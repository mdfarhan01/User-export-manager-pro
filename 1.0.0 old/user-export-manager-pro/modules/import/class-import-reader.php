<?php
/**
 * Reads an uploaded CSV, XLSX, JSON or XML file and normalises it into a
 * temporary JSON-lines file (one object per row, keyed by column name), so
 * the import job can process any format the same way, in batches.
 *
 * @package User_Export_Manager_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.WP.AlternativeFunctions -- Streaming large files row by row needs native file handles; all paths are the upload's temp file or the plugin's own protected folder.

if ( ! class_exists( 'UEMPro_Import_Reader' ) ) {

	/**
	 * Class UEMPro_Import_Reader
	 */
	class UEMPro_Import_Reader {

		/**
		 * Number of sample rows returned for the mapping screen.
		 */
		const SAMPLE_ROWS = 3;

		/**
		 * Supported extensions.
		 *
		 * @return string[]
		 */
		public static function extensions() {
			return array( 'csv', 'xlsx', 'json', 'xml' );
		}

		/**
		 * Parse $source into $part.
		 *
		 * @param string $source Source file path.
		 * @param string $ext    Extension (csv|xlsx|json|xml).
		 * @param string $part   Target JSON-lines file.
		 * @return array|WP_Error { headers: string[], total: int, sample: array[] }
		 */
		public static function parse( $source, $ext, $part ) {
			$out = fopen( $part, 'wb' );
			if ( ! $out ) {
				return new WP_Error( 'uempro_import_write', __( 'Could not write to the plugin’s folder inside wp-content/uploads.', 'user-export-manager-pro' ) );
			}

			$state = array(
				'out'     => $out,
				'headers' => array(),
				'total'   => 0,
				'sample'  => array(),
			);

			switch ( $ext ) {
				case 'xlsx':
					$result = self::read_xlsx( $source, $state );
					break;
				case 'json':
					$result = self::read_json( $source, $state );
					break;
				case 'xml':
					$result = self::read_xml( $source, $state );
					break;
				default:
					$result = self::read_csv( $source, $state );
			}

			fclose( $out );

			if ( is_wp_error( $result ) ) {
				return $result;
			}
			if ( empty( $state['headers'] ) || $state['total'] < 1 ) {
				return new WP_Error( 'uempro_import_empty', __( 'No rows were found in this file. Make sure the first row contains column names and the file has at least one user.', 'user-export-manager-pro' ) );
			}

			return array(
				'headers' => $state['headers'],
				'total'   => $state['total'],
				'sample'  => $state['sample'],
			);
		}

		/**
		 * Store one row.
		 *
		 * @param array $row   Associative row (column => value).
		 * @param array $state Reader state (by reference).
		 */
		private static function emit( array $row, array &$state ) {
			$clean = array();
			$empty = true;
			foreach ( $row as $column => $value ) {
				$column = (string) $column;
				if ( '' === $column ) {
					continue;
				}
				$value = self::to_utf8( is_scalar( $value ) || null === $value ? (string) $value : (string) wp_json_encode( $value ) );
				if ( '' !== trim( $value ) ) {
					$empty = false;
				}
				if ( ! in_array( $column, $state['headers'], true ) ) {
					$state['headers'][] = $column;
				}
				$clean[ $column ] = $value;
			}
			if ( $empty ) {
				return;
			}

			fwrite( $state['out'], wp_json_encode( $clean ) . "\n" );
			++$state['total'];
			if ( count( $state['sample'] ) < self::SAMPLE_ROWS ) {
				$state['sample'][] = $clean;
			}
		}

		/**
		 * Make a string valid UTF-8 (files saved by older Excel versions are often Windows-1252).
		 *
		 * @param string $value Value.
		 * @return string
		 */
		private static function to_utf8( $value ) {
			if ( '' === $value || ( function_exists( 'mb_check_encoding' ) ? mb_check_encoding( $value, 'UTF-8' ) : '' !== wp_check_invalid_utf8( $value ) ) ) {
				return $value;
			}
			if ( function_exists( 'mb_convert_encoding' ) ) {
				return (string) mb_convert_encoding( $value, 'UTF-8', 'Windows-1252' );
			}
			return wp_check_invalid_utf8( $value, true );
		}

		/**
		 * Make column names unique and non-empty.
		 *
		 * @param array $headers Raw headers.
		 * @return string[]
		 */
		private static function normalise_headers( array $headers ) {
			$out  = array();
			$seen = array();
			foreach ( array_values( $headers ) as $i => $header ) {
				$header = trim( self::to_utf8( (string) $header ) );
				$header = preg_replace( '/^\xEF\xBB\xBF/', '', $header );
				if ( '' === $header ) {
					/* translators: %d: column number */
					$header = sprintf( __( 'Column %d', 'user-export-manager-pro' ), $i + 1 );
				}
				$base = $header;
				$n    = 2;
				while ( isset( $seen[ $header ] ) ) {
					$header = $base . ' (' . $n++ . ')';
				}
				$seen[ $header ] = true;
				$out[]           = $header;
			}
			return $out;
		}

		/**
		 * Undo the formula-protection apostrophe added by this plugin's CSV export.
		 *
		 * @param string $value Value.
		 * @return string
		 */
		private static function unprotect( $value ) {
			if ( strlen( $value ) > 1 && "'" === $value[0] && false !== strpos( "=+-@\t\r", $value[1] ) ) {
				return substr( $value, 1 );
			}
			return $value;
		}

		/**
		 * CSV reader with delimiter detection (comma, semicolon, tab).
		 *
		 * @param string $source File.
		 * @param array  $state  State.
		 * @return true|WP_Error
		 */
		private static function read_csv( $source, array &$state ) {
			$in = fopen( $source, 'rb' );
			if ( ! $in ) {
				return new WP_Error( 'uempro_import_read', __( 'The uploaded file could not be read.', 'user-export-manager-pro' ) );
			}

			$first = (string) fgets( $in );
			$first = preg_replace( '/^\xEF\xBB\xBF/', '', $first );
			$best  = ',';
			$max   = -1;
			foreach ( array( ',', ';', "\t" ) as $candidate ) {
				$count = count( str_getcsv( $first, $candidate, '"', '\\' ) );
				if ( $count > $max ) {
					$max  = $count;
					$best = $candidate;
				}
			}

			rewind( $in );
			if ( "\xEF\xBB\xBF" !== fread( $in, 3 ) ) {
				rewind( $in );
			}

			$headers = fgetcsv( $in, 0, $best, '"', '\\' );
			if ( ! is_array( $headers ) ) {
				fclose( $in );
				return true;
			}
			$headers = self::normalise_headers( $headers );

			while ( false !== ( $line = fgetcsv( $in, 0, $best, '"', '\\' ) ) ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
				if ( array( null ) === $line ) {
					continue;
				}
				$row = array();
				foreach ( $headers as $i => $header ) {
					$row[ $header ] = isset( $line[ $i ] ) ? self::unprotect( (string) $line[ $i ] ) : '';
				}
				self::emit( $row, $state );
			}

			fclose( $in );
			return true;
		}

		/**
		 * JSON reader: an array of objects (or { "users": [...] }).
		 *
		 * @param string $source File.
		 * @param array  $state  State.
		 * @return true|WP_Error
		 */
		private static function read_json( $source, array &$state ) {
			$data = json_decode( (string) file_get_contents( $source ), true );
			if ( ! is_array( $data ) ) {
				return new WP_Error( 'uempro_import_json', __( 'This is not valid JSON. The file should contain an array of user objects.', 'user-export-manager-pro' ) );
			}
			if ( isset( $data['users'] ) && is_array( $data['users'] ) ) {
				$data = $data['users'];
			}
			foreach ( $data as $item ) {
				if ( is_array( $item ) ) {
					self::emit( $item, $state );
				}
			}
			return true;
		}

		/**
		 * XML reader: every child of the root element is one user; its child
		 * elements are the columns.
		 *
		 * @param string $source File.
		 * @param array  $state  State.
		 * @return true|WP_Error
		 */
		private static function read_xml( $source, array &$state ) {
			if ( ! class_exists( 'XMLReader' ) || ! class_exists( 'DOMDocument' ) ) {
				return new WP_Error( 'uempro_import_xml', __( 'XML import needs the PHP XMLReader and DOM extensions.', 'user-export-manager-pro' ) );
			}

			$reader = new XMLReader();
			if ( ! $reader->open( $source, null, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING ) ) {
				return new WP_Error( 'uempro_import_xml', __( 'This XML file could not be opened.', 'user-export-manager-pro' ) );
			}

			$doc   = new DOMDocument();
			$found = false;
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- XMLReader properties.
			while ( @$reader->read() ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Malformed XML is reported below instead of raising warnings.
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				if ( XMLReader::ELEMENT !== $reader->nodeType || 1 !== $reader->depth ) {
					continue;
				}
				$node = $reader->expand( $doc );
				if ( ! $node ) {
					continue;
				}
				$found = true;
				$row   = array();
				foreach ( $node->childNodes as $child ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					if ( XML_ELEMENT_NODE === $child->nodeType ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						$row[ $child->nodeName ] = $child->textContent; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					}
				}
				self::emit( $row, $state );
			}
			$reader->close();

			if ( ! $found ) {
				return new WP_Error( 'uempro_import_xml', __( 'No user records were found in this XML file. Expected a root element with one child element per user.', 'user-export-manager-pro' ) );
			}
			return true;
		}

		/**
		 * Column letters ("AB") to a zero-based index.
		 *
		 * @param string $ref Cell reference such as "AB12".
		 * @return int
		 */
		private static function column_index( $ref ) {
			$letters = preg_replace( '/[^A-Z]/', '', strtoupper( $ref ) );
			$index   = 0;
			$len     = strlen( $letters );
			for ( $i = 0; $i < $len; $i++ ) {
				$index = $index * 26 + ( ord( $letters[ $i ] ) - 64 );
			}
			return $index - 1;
		}

		/**
		 * Concatenated text of all <t> elements below a node (rich text runs).
		 *
		 * @param DOMNode $node Node.
		 * @return string
		 */
		private static function text_of( $node ) {
			$text = '';
			foreach ( $node->getElementsByTagName( 't' ) as $t ) {
				$text .= $t->textContent; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			}
			return $text;
		}

		/**
		 * XLSX reader (first worksheet). No external libraries.
		 *
		 * @param string $source File.
		 * @param array  $state  State.
		 * @return true|WP_Error
		 */
		private static function read_xlsx( $source, array &$state ) {
			if ( ! class_exists( 'ZipArchive' ) || ! class_exists( 'XMLReader' ) ) {
				return new WP_Error( 'uempro_import_xlsx', __( 'Excel import needs the PHP ZipArchive and XMLReader extensions. Save the file as CSV and import that instead.', 'user-export-manager-pro' ) );
			}

			$zip = new ZipArchive();
			if ( true !== $zip->open( $source ) ) {
				return new WP_Error( 'uempro_import_xlsx', __( 'This Excel file could not be opened. Save it again as .xlsx (or CSV) and retry.', 'user-export-manager-pro' ) );
			}

			// Locate the first worksheet through the workbook relationships.
			$sheet_path = 'xl/worksheets/sheet1.xml';
			$workbook   = $zip->getFromName( 'xl/workbook.xml' );
			$rels       = $zip->getFromName( 'xl/_rels/workbook.xml.rels' );
			if ( $workbook && $rels && preg_match( '/<sheet\b[^>]*\br:id="([^"]+)"/', $workbook, $m ) ) {
				if ( preg_match( '/<Relationship\b[^>]*Id="' . preg_quote( $m[1], '/' ) . '"[^>]*Target="([^"]+)"/', $rels, $t )
					|| preg_match( '/<Relationship\b[^>]*Target="([^"]+)"[^>]*Id="' . preg_quote( $m[1], '/' ) . '"/', $rels, $t ) ) {
					$target     = ltrim( $t[1], '/' );
					$sheet_path = 0 === strpos( $target, 'xl/' ) ? $target : 'xl/' . $target;
				}
			}

			// Shared strings.
			$shared = array();
			$sst    = $zip->getFromName( 'xl/sharedStrings.xml' );
			if ( $sst ) {
				$doc = new DOMDocument();
				if ( @$doc->loadXML( $sst, LIBXML_NONET | LIBXML_COMPACT | LIBXML_PARSEHUGE ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
					foreach ( $doc->getElementsByTagName( 'si' ) as $si ) {
						$shared[] = self::text_of( $si );
					}
				}
			}

			$xml = $zip->getFromName( $sheet_path );
			$zip->close();
			if ( false === $xml ) {
				return new WP_Error( 'uempro_import_xlsx', __( 'No worksheet was found in this Excel file.', 'user-export-manager-pro' ) );
			}

			$reader = new XMLReader();
			if ( ! $reader->XML( $xml, null, LIBXML_NONET | LIBXML_PARSEHUGE ) ) {
				return new WP_Error( 'uempro_import_xlsx', __( 'The worksheet in this Excel file could not be read.', 'user-export-manager-pro' ) );
			}

			$doc     = new DOMDocument();
			$headers = null;
			while ( @$reader->read() ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				if ( XMLReader::ELEMENT !== $reader->nodeType || 'row' !== $reader->localName ) {
					continue;
				}
				$row_node = $reader->expand( $doc );
				if ( ! $row_node ) {
					continue;
				}

				$cells = array();
				$next  = 0;
				foreach ( $row_node->getElementsByTagName( 'c' ) as $cell ) {
					$ref   = $cell->getAttribute( 'r' );
					$index = '' !== $ref ? self::column_index( $ref ) : $next;
					$next  = $index + 1;
					$type  = $cell->getAttribute( 't' );

					if ( 'inlineStr' === $type ) {
						$value = self::text_of( $cell );
					} else {
						$v     = $cell->getElementsByTagName( 'v' )->item( 0 );
						$value = $v ? $v->textContent : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						if ( 's' === $type ) {
							$value = isset( $shared[ (int) $value ] ) ? $shared[ (int) $value ] : '';
						} elseif ( 'b' === $type ) {
							$value = '1' === $value ? '1' : '0';
						}
					}
					$cells[ $index ] = (string) $value;
				}

				if ( null === $headers ) {
					if ( empty( array_filter( $cells, 'strlen' ) ) ) {
						continue; // Skip leading blank rows.
					}
					$width   = max( array_keys( $cells ) ) + 1;
					$raw     = array();
					for ( $i = 0; $i < $width; $i++ ) {
						$raw[] = isset( $cells[ $i ] ) ? $cells[ $i ] : '';
					}
					$headers = self::normalise_headers( $raw );
					continue;
				}

				$row = array();
				foreach ( $headers as $i => $header ) {
					$row[ $header ] = isset( $cells[ $i ] ) ? $cells[ $i ] : '';
				}
				self::emit( $row, $state );
			}
			$reader->close();

			if ( null !== $headers && empty( $state['headers'] ) ) {
				$state['headers'] = $headers;
			}
			return true;
		}
	}
}
