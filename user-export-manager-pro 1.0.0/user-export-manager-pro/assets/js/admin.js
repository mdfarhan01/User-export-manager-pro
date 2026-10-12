/**
 * User Export Manager Pro — admin script.
 * Everything lives inside this closure; the only global it reads is the
 * localized UEMPRO object.
 */
(function ($, cfg) {
	'use strict';

	if (!cfg || !$) {
		return;
	}

	var t = cfg.i18n || {};
	var $wrap = $('.uempro-wrap').first();

	/* =====================================================================
	 * Utilities
	 * ================================================================== */

	function sprintf(str) {
		var args = Array.prototype.slice.call(arguments, 1);
		var i = 0;
		return String(str).replace(/%(\d+)\$s|%s/g, function (m, n) {
			var v = n ? args[parseInt(n, 10) - 1] : args[i++];
			return v === undefined ? '' : v;
		});
	}

	function esc(str) {
		return String(str == null ? '' : str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function num(n) {
		var v = parseInt(n, 10) || 0;
		try {
			return v.toLocaleString();
		} catch (e) {
			return String(v);
		}
	}

	function debounce(fn, wait) {
		var timer;
		return function () {
			var ctx = this, args = arguments;
			clearTimeout(timer);
			timer = setTimeout(function () {
				fn.apply(ctx, args);
			}, wait);
		};
	}

	function ajax(action, data) {
		var payload = $.extend({}, data || {}, { action: action, nonce: cfg.nonce });
		var jqxhr = $.ajax({
			url: cfg.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: payload
		});
		var promise = jqxhr.then(function (res) {
			if (res && res.success) {
				return res.data;
			}
			return $.Deferred().reject((res && res.data && res.data.message) || t.genericError).promise();
		}, function (xhr) {
			if (xhr && xhr.statusText === 'abort') {
				return $.Deferred().reject('abort').promise();
			}
			var msg = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message;
			return $.Deferred().reject(msg || t.requestFailed).promise();
		});
		promise.abort = function () {
			jqxhr.abort();
		};
		return promise;
	}

	/**
	 * Local initials avatar (no Gravatar request, so no data leaves the site).
	 */
	function avatar(u) {
		var name = $.trim(String(u.name || u.login || '?'));
		var parts = name.split(/\s+/);
		var initials = (parts[0].charAt(0) + (parts.length > 1 ? parts[parts.length - 1].charAt(0) : '')).toUpperCase();
		return '<span class="uempro-avatar uempro-avatar--' + (parseInt(u.id, 10) % 6) + '" aria-hidden="true">' + esc(initials) + '</span>';
	}

	function columnLetter(index) {
		var s = '';
		index += 1;
		while (index > 0) {
			var mod = (index - 1) % 26;
			s = String.fromCharCode(65 + mod) + s;
			index = Math.floor((index - mod) / 26);
		}
		return s;
	}

	/* ---------- Toasts ---------- */
	var $toasts = null;

	function toast(message, type) {
		if (!$toasts) {
			$toasts = $('<div class="uempro-toasts" role="status" aria-live="polite"></div>').appendTo($wrap.length ? $wrap : 'body');
		}
		var $t = $('<div class="uempro-toast"></div>').text(message);
		if (type === 'error') {
			$t.addClass('uempro-toast--error');
		}
		$toasts.append($t);
		setTimeout(function () {
			$t.fadeOut(200, function () {
				$t.remove();
			});
		}, type === 'error' ? 6000 : 3500);
	}

	/* ---------- Modals ---------- */
	var lastFocus = null;

	function openModal($modal) {
		lastFocus = document.activeElement;
		$modal.prop('hidden', false);
		$('body').addClass('uempro-modal-open');
		setTimeout(function () {
			var $focus = $modal.find('[data-autofocus]').first();
			if (!$focus.length) {
				$focus = $modal.find('input:visible, button:visible').not('[data-modal-close]').first();
			}
			$focus.trigger('focus');
		}, 30);
	}

	function closeModal($modal) {
		$modal.prop('hidden', true).trigger('uempro:closed');
		if (!$('.uempro-modal:not([hidden])').length) {
			$('body').removeClass('uempro-modal-open');
		}
		if ($modal.data('uempro-temp')) {
			$modal.remove();
		}
		if (lastFocus && lastFocus.focus) {
			lastFocus.focus();
		}
	}

	$(document).on('click', '.uempro-modal [data-modal-close]', function () {
		var $m = $(this).closest('.uempro-modal');
		if ($m.data('uempro-locked')) {
			return;
		}
		closeModal($m);
	});

	$(document).on('keydown', function (e) {
		if (e.key !== 'Escape') {
			return;
		}
		var $open = $('.uempro-modal:not([hidden])').last();
		if ($open.length && !$open.data('uempro-locked')) {
			closeModal($open);
		}
	});

	/**
	 * Build a temporary dialog.
	 */
	function dialog(opts) {
		var html =
			'<div class="uempro-modal">' +
			'<div class="uempro-modal__backdrop" data-modal-close></div>' +
			'<div class="uempro-modal__dialog" role="dialog" aria-modal="true">' +
			'<header class="uempro-modal__head"><h2></h2>' +
			'<button type="button" class="uempro-iconbtn" data-modal-close aria-label="' + esc(t.close) + '"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button></header>' +
			'<div class="uempro-modal__body"></div>' +
			'<footer class="uempro-modal__foot"></footer>' +
			'</div></div>';
		var $m = $(html);
		$m.data('uempro-temp', true);
		$m.find('h2').text(opts.title || '');
		$m.find('.uempro-modal__body').append(opts.body || '');
		$m.find('.uempro-modal__foot').append(opts.footer || '');
		($wrap.length ? $wrap : $('body')).append($m);
		openModal($m);
		return $m;
	}

	function confirmDialog(title, message, confirmLabel) {
		var d = $.Deferred();
		var $yes = $('<button type="button" class="uempro-btn uempro-btn--danger" data-autofocus></button>').text(confirmLabel || t.delete);
		var $no = $('<button type="button" class="uempro-btn uempro-btn--ghost" data-modal-close></button>').text(t.cancel);
		var $m = dialog({
			title: title,
			body: $('<p></p>').text(message),
			footer: [$no, $yes]
		});
		var resolved = false;
		$yes.on('click', function () {
			resolved = true;
			closeModal($m);
			d.resolve();
		});
		$m.on('uempro:closed', function () {
			if (!resolved) {
				d.reject();
			}
		});
		return d.promise();
	}

	/* =====================================================================
	 * Field picker
	 * ================================================================== */

	function FieldPicker($root) {
		this.$root = $root;
		this.order = [];
		this.scanning = false;
		this.dragKey = null;

		var self = this;
		$root.find('.uempro-fp__item input:checked').each(function () {
			self.order.push(this.value);
		});

		this.bind();
		this.syncSystemGroup();
		this.render();
	}

	FieldPicker.prototype = {
		inputs: function () {
			return this.$root.find('.uempro-fp__item input');
		},

		inputFor: function (key) {
			var found = null;
			this.inputs().each(function () {
				if (this.value === key) {
					found = $(this);
					return false;
				}
			});
			return found;
		},

		labelFor: function (key) {
			var $i = this.inputFor(key);
			return $i ? $i.data('label') : key;
		},

		get: function () {
			return this.order.slice();
		},

		set: function (keys) {
			var self = this;
			keys = Array.isArray(keys) ? keys : [];
			this.inputs().prop('checked', false);
			this.order = [];
			$.each(keys, function (i, key) {
				key = String(key);
				if (self.order.indexOf(key) !== -1) {
					return;
				}
				var $input = self.inputFor(key);
				if (!$input) {
					$input = self.addMissing(key);
				}
				$input.prop('checked', true);
				self.order.push(key);
			});
			this.syncSystemGroup();
			this.render();
		},

		itemHtml: function (key, label) {
			return '<label class="uempro-fp__item" data-key="' + esc(key) + '" data-search="' + esc((label + ' ' + key).toLowerCase()) + '">' +
				'<input type="checkbox" value="' + esc(key) + '" data-label="' + esc(label) + '">' +
				'<span class="uempro-fp__text"><span class="uempro-fp__label">' + esc(label) + '</span>' +
				'<code class="uempro-fp__key">' + esc(key) + '</code></span></label>';
		},

		addMissing: function (key) {
			var $item = $(this.itemHtml(key, key));
			$item.addClass('is-missing').find('.uempro-fp__key').attr('data-missing', t.notOnSite);
			this.$root.find('[data-fp-grid="profile"]').append($item);
			this.$root.find('[data-fp-empty]').prop('hidden', true);
			return $item.find('input');
		},

		/** Rebuild custom groups from a scan result, keeping the selection. */
		rebuild: function (data) {
			var self = this;
			var seen = {};
			this.inputs().filter(function () {
				return $(this).closest('[data-group="core"]').length > 0;
			}).each(function () {
				seen[this.value] = true;
			});

			$.each(['profile', 'system'], function (i, group) {
				var html = '';
				$.each(data[group] || {}, function (key, label) {
					if (seen[key]) {
						return; // Never render a key twice.
					}
					seen[key] = true;
					html += self.itemHtml(key, label);
				});
				self.$root.find('[data-fp-grid="' + group + '"]').html(html);
			});

			this.$root.find('[data-fp-empty]').prop('hidden', !$.isEmptyObject(data.profile || {}));

			var keep = this.order.slice();
			this.set(keep);
			this.filter(this.$root.find('.uempro-fp__search').val());
		},

		scan: function ($btn) {
			if (this.scanning) {
				return;
			}
			var self = this;
			this.scanning = true;
			$btn.addClass('is-busy').find('[data-fp-scan-label]').text(t.scanning);

			ajax('uempro_scan_fields').then(function (data) {
				$('[data-uempro-picker]').each(function () {
					var picker = $(this).data('uempro-picker-instance');
					if (picker) {
						picker.rebuild(data);
					}
				});
				var found = Object.keys(data.profile || {}).length + Object.keys(data.system || {}).length;
				toast(sprintf(t.scanDone, num(found)));
				if (data.scanned_at) {
					$('[data-fp-scanned]').text(sprintf(t.scannedAt, data.scanned_at));
				}
			}, function (msg) {
				toast(msg, 'error');
			}).always(function () {
				self.scanning = false;
				$btn.removeClass('is-busy').find('[data-fp-scan-label]').text(t.rescan);
			});
		},

		syncSystemGroup: function () {
			var $group = this.$root.find('[data-group="system"]');
			if ($group.find('input:checked').length) {
				this.toggleSystem(true);
			}
		},

		toggleSystem: function (open) {
			var $group = this.$root.find('[data-group="system"]');
			var $btn = $group.find('[data-fp-toggle]');
			$group.toggleClass('is-collapsed', !open);
			$btn.attr('aria-expanded', open ? 'true' : 'false').text(open ? t.hide : t.show);
		},

		filter: function (term) {
			term = $.trim(String(term || '')).toLowerCase();
			this.$root.toggleClass('is-searching', term !== '');
			var any = false;

			this.$root.find('.uempro-fp__group').each(function () {
				var $g = $(this);
				var visible = 0;
				$g.find('.uempro-fp__item').each(function () {
					var match = term === '' || String($(this).data('search')).indexOf(term) !== -1;
					$(this).prop('hidden', !match);
					if (match) {
						visible++;
					}
				});
				$g.prop('hidden', term !== '' && visible === 0);
				if (visible) {
					any = true;
				}
			});

			this.$root.find('[data-fp-nomatch]').prop('hidden', term === '' || any);
		},

		toggle: function (key, checked) {
			var idx = this.order.indexOf(key);
			if (checked && idx === -1) {
				this.order.push(key);
			} else if (!checked && idx !== -1) {
				this.order.splice(idx, 1);
			}
			this.render();
		},

		move: function (key, newIndex) {
			var idx = this.order.indexOf(key);
			if (idx === -1) {
				return;
			}
			newIndex = Math.max(0, Math.min(this.order.length - 1, newIndex));
			this.order.splice(idx, 1);
			this.order.splice(newIndex, 0, key);
			this.render();
		},

		remove: function (key) {
			var $i = this.inputFor(key);
			if ($i) {
				$i.prop('checked', false);
			}
			this.toggle(key, false);
		},

		render: function () {
			var self = this;
			var total = this.inputs().length;

			this.$root.find('[data-fp-count]').text(sprintf(t.fieldsSelected, num(this.order.length), num(total)));

			this.$root.find('.uempro-fp__group').each(function () {
				var $g = $(this);
				var all = $g.find('.uempro-fp__item').length;
				var sel = $g.find('input:checked').length;
				$g.find('[data-fp-gcount]').text(sel ? sel + ' / ' + all : all);
			});

			this.$root.trigger('uempro:fieldschange', [this.order.length]);

			var $list = this.$root.find('[data-fp-columns]').empty();
			if (!this.order.length) {
				$list.append($('<li class="uempro-cols__empty"></li>').text(t.noColumns));
				return;
			}
			$.each(this.order, function (i, key) {
				var label = self.labelFor(key);
				var $li = $('<li class="uempro-col" draggable="true" tabindex="0"></li>')
					.attr('data-key', key)
					.attr('aria-label', columnLetter(i) + ': ' + label);
				$li.append($('<span class="uempro-col__letter"></span>').text(columnLetter(i)));
				$li.append($('<span class="uempro-col__name"></span>').text(label).attr('title', label + ' (' + key + ')'));
				$li.append($('<button type="button" class="uempro-col__remove" tabindex="-1">×</button>').attr('aria-label', t.removeColumn));
				$list.append($li);
			});
		},

		bind: function () {
			var self = this;
			var $r = this.$root;

			$r.on('change', '.uempro-fp__item input', function () {
				self.toggle(this.value, this.checked);
			});

			$r.on('input', '.uempro-fp__search', debounce(function () {
				self.filter(this.value);
			}, 120));

			$r.on('click', '[data-fp-all], [data-fp-none]', function () {
				var on = $(this).is('[data-fp-all]');
				$(this).closest('.uempro-fp__group').find('.uempro-fp__item:not([hidden]) input').each(function () {
					if (this.checked !== on) {
						this.checked = on;
						self.toggle(this.value, on);
					}
				});
			});

			$r.on('click', '[data-fp-toggle]', function () {
				var $g = $(this).closest('.uempro-fp__group');
				self.toggleSystem($g.hasClass('is-collapsed'));
			});

			$r.on('click', '[data-fp-scan]', function () {
				self.scan($(this));
			});

			/* Column strip: remove, keyboard, drag & drop */
			$r.on('click', '.uempro-col__remove', function (e) {
				e.preventDefault();
				self.remove($(this).closest('.uempro-col').data('key'));
			});

			$r.on('keydown', '.uempro-col', function (e) {
				var key = $(this).data('key');
				var idx = self.order.indexOf(key);
				var next = null;
				if (e.key === 'ArrowLeft') {
					next = idx - 1;
				} else if (e.key === 'ArrowRight') {
					next = idx + 1;
				} else if (e.key === 'Delete' || e.key === 'Backspace') {
					e.preventDefault();
					var focusKey = self.order[idx + 1] || self.order[idx - 1];
					self.remove(key);
					if (focusKey) {
						$r.find('.uempro-col[data-key="' + cssEscape(focusKey) + '"]').trigger('focus');
					}
					return;
				}
				if (next !== null) {
					e.preventDefault();
					self.move(key, next);
					$r.find('.uempro-col[data-key="' + cssEscape(key) + '"]').trigger('focus');
				}
			});

			$r.on('dragstart', '.uempro-col', function (e) {
				self.dragKey = $(this).data('key');
				$(this).addClass('is-dragging');
				var dt = e.originalEvent.dataTransfer;
				if (dt) {
					dt.effectAllowed = 'move';
					try {
						dt.setData('text/plain', String(self.dragKey));
					} catch (err) { /* IE */ }
				}
			});

			$r.on('dragover', '.uempro-col', function (e) {
				if (self.dragKey === null) {
					return;
				}
				e.preventDefault();
				$r.find('.uempro-col').removeClass('is-over');
				$(this).addClass('is-over');
			});

			$r.on('drop', '.uempro-col', function (e) {
				e.preventDefault();
				var target = $(this).data('key');
				if (self.dragKey !== null && target !== self.dragKey) {
					self.move(self.dragKey, self.order.indexOf(target));
				}
			});

			$r.on('dragend', '.uempro-col', function () {
				self.dragKey = null;
				$r.find('.uempro-col').removeClass('is-dragging is-over');
			});
		}
	};

	function cssEscape(value) {
		if (window.CSS && window.CSS.escape) {
			return window.CSS.escape(String(value));
		}
		return String(value).replace(/["\\]/g, '\\$&');
	}

	function initPickers() {
		$('[data-uempro-picker]').each(function () {
			var $el = $(this);
			if (!$el.data('uempro-picker-instance')) {
				$el.data('uempro-picker-instance', new FieldPicker($el));
			}
		});
	}

	/* =====================================================================
	 * Scope ("which users") picker
	 * ================================================================== */

	function ScopePicker($root, onChange) {
		this.$root = $root;
		this.id = $root.data('scope-id');
		this.selected = {};
		this.page = 1;
		this.perPage = 10;
		this.loaded = false;
		this.onChange = onChange || function () {};
		this.bind();
		this.sync();
	}

	ScopePicker.prototype = {
		scope: function () {
			return this.$root.find('input[name="' + this.id + '-scope"]:checked').val() || 'all';
		},

		range: function () {
			return this.$root.find('input[name="' + this.id + '-range"]:checked').val() || '30days';
		},

		get: function () {
			return {
				scope: this.scope(),
				user_ids: Object.keys(this.selected).map(function (k) { return parseInt(k, 10); }),
				roles: this.$root.find('[data-scope-role]:checked').map(function () { return this.value; }).get(),
				date_range: this.range(),
				date_from: this.$root.find('[data-scope-from]').val() || '',
				date_to: this.$root.find('[data-scope-to]').val() || '',
				search: $.trim(this.$root.find('[data-scope-search]').val() || ''),
				orderby: this.$root.find('[data-scope-orderby]').val() || 'ID',
				order: this.$root.find('[data-scope-order]').val() || 'ASC'
			};
		},

		set: function (f) {
			var self = this;
			f = f || {};
			var scope = f.scope || 'all';

			this.selected = {};
			$.each(f.user_ids || [], function (i, id) {
				self.selected[id] = true;
			});

			// Template editor has no user table; show the option only when a template uses it.
			var $selOnly = this.$root.find('[data-scope-selected-only]');
			if ($selOnly.length) {
				$selOnly.prop('hidden', scope !== 'selected');
				$selOnly.find('[data-choice-desc]').text(sprintf(t.selectedUsers, num(Object.keys(this.selected).length)));
			}

			this.$root.find('input[name="' + this.id + '-scope"][value="' + scope + '"]').prop('checked', true);
			this.$root.find('[data-scope-role]').each(function () {
				this.checked = $.inArray(this.value, f.roles || []) !== -1;
			});
			this.$root.find('input[name="' + this.id + '-range"][value="' + (f.date_range || '30days') + '"]').prop('checked', true);
			this.$root.find('[data-scope-from]').val(f.date_from || '');
			this.$root.find('[data-scope-to]').val(f.date_to || '');
			this.$root.find('[data-scope-search]').val(f.search || '');
			this.$root.find('[data-scope-orderby]').val(f.orderby || 'ID');
			this.$root.find('[data-scope-order]').val(f.order || 'ASC');
			this.sync();
			this.updateSelectedCount();
			if (this.loaded) {
				this.renderChecks();
			}
		},

		validate: function () {
			var f = this.get();
			if (f.scope === 'selected' && !f.user_ids.length) {
				return t.pickUsers;
			}
			if (f.scope === 'role' && !f.roles.length) {
				return t.pickRoles;
			}
			if (f.scope === 'date' && f.date_range === 'custom' && !f.date_from && !f.date_to) {
				return t.pickDates;
			}
			return '';
		},

		summary: function () {
			var f = this.get();
			var text;
			switch (f.scope) {
				case 'selected':
					text = sprintf(t.selectedUsers, num(f.user_ids.length));
					break;
				case 'role':
					text = sprintf(t.roleSummary, f.roles.map(function (r) {
						return (cfg.roles && cfg.roles[r]) || r;
					}).join(', '));
					break;
				case 'date':
					text = { today: t.today, '7days': t.last7, '30days': t.last30 }[f.date_range] ||
						sprintf(t.customRange, f.date_from || '…', f.date_to || '…');
					break;
				default:
					text = t.allUsers;
			}
			if (f.search) {
				text += ' ' + sprintf(t.matching, f.search);
			}
			return text;
		},

		sync: function () {
			var scope = this.scope();
			this.$root.find('[data-scope-panel]').each(function () {
				$(this).prop('hidden', $(this).data('scope-panel') !== scope);
			});
			this.$root.find('[data-scope-custom]').prop('hidden', this.range() !== 'custom');
			if (scope === 'selected' && this.$root.find('[data-users-body]').length && !this.loaded) {
				this.loadUsers(1);
			}
		},

		/* ---- user table ---- */
		loadUsers: function (page) {
			var self = this;
			var $body = this.$root.find('[data-users-body]');
			this.page = page || 1;
			this.loaded = true;
			$body.html('<tr><td colspan="4" class="uempro-muted">' + esc(t.loadingUsers) + '</td></tr>');

			if (this.xhr && this.xhr.abort) {
				this.xhr.abort();
			}
			this.xhr = ajax('uempro_list_users', {
				paged: this.page,
				per_page: this.perPage,
				search: this.$root.find('[data-users-search]').val(),
				role: this.$root.find('[data-users-role]').val()
			});
			this.xhr.then(function (data) {
				self.renderUsers(data);
			}, function (msg) {
				if (msg !== 'abort') {
					$body.html('<tr><td colspan="4">' + esc(msg) + '</td></tr>');
				}
			});
		},

		renderUsers: function (data) {
			var self = this;
			var $body = this.$root.find('[data-users-body]').empty();
			if (!data.users.length) {
				$body.html('<tr><td colspan="4" class="uempro-muted">' + esc(t.noUsers) + '</td></tr>');
			}
			$.each(data.users, function (i, u) {
				var checked = self.selected[u.id] ? ' checked' : '';
				$body.append(
					'<tr class="' + (checked ? 'is-selected' : '') + '">' +
					'<td><input type="checkbox" data-user-id="' + esc(u.id) + '"' + checked + ' aria-label="' + esc(u.name) + '"></td>' +
					'<td><span class="uempro-usercell">' + avatar(u) + '<span><strong>' + esc(u.name) + '</strong><small>' + esc(u.login) + '</small></span></span></td>' +
					'<td>' + esc(u.email) + '</td>' +
					'<td>' + esc(u.role) + '</td>' +
					'</tr>'
				);
			});
			var pages = Math.max(1, Math.ceil(data.total / data.per_page));
			this.pages = pages;
			this.$root.find('[data-users-pageinfo]').text(sprintf(t.pageOf, num(data.paged), num(pages), num(data.total)));
			this.$root.find('[data-users-prev]').prop('disabled', data.paged <= 1);
			this.$root.find('[data-users-next]').prop('disabled', data.paged >= pages);
			this.syncAllBox();
		},

		renderChecks: function () {
			var self = this;
			this.$root.find('[data-user-id]').each(function () {
				var on = !!self.selected[$(this).data('user-id')];
				this.checked = on;
				$(this).closest('tr').toggleClass('is-selected', on);
			});
			this.syncAllBox();
		},

		syncAllBox: function () {
			var $boxes = this.$root.find('[data-user-id]');
			var on = $boxes.length && $boxes.filter(':checked').length === $boxes.length;
			this.$root.find('[data-users-all]').prop('checked', !!on);
		},

		updateSelectedCount: function () {
			this.$root.find('[data-users-selected]').text(num(Object.keys(this.selected).length));
		},

		bind: function () {
			var self = this;
			var $r = this.$root;
			var changed = debounce(function () {
				self.onChange();
			}, 350);

			$r.on('change', 'input[name="' + this.id + '-scope"], input[name="' + this.id + '-range"]', function () {
				self.sync();
				changed();
			});
			$r.on('change', '[data-scope-role], [data-scope-from], [data-scope-to], [data-scope-orderby], [data-scope-order]', changed);
			$r.on('input', '[data-scope-search]', changed);

			$r.on('input', '[data-users-search]', debounce(function () {
				self.loadUsers(1);
			}, 350));
			$r.on('change', '[data-users-role]', function () {
				self.loadUsers(1);
			});
			$r.on('click', '[data-users-prev]', function () {
				self.loadUsers(Math.max(1, self.page - 1));
			});
			$r.on('click', '[data-users-next]', function () {
				self.loadUsers(Math.min(self.pages || 1, self.page + 1));
			});
			$r.on('change', '[data-user-id]', function () {
				var id = $(this).data('user-id');
				if (this.checked) {
					self.selected[id] = true;
				} else {
					delete self.selected[id];
				}
				$(this).closest('tr').toggleClass('is-selected', this.checked);
				self.syncAllBox();
				self.updateSelectedCount();
				changed();
			});
			$r.on('change', '[data-users-all]', function () {
				var on = this.checked;
				$r.find('[data-user-id]').each(function () {
					var id = $(this).data('user-id');
					this.checked = on;
					$(this).closest('tr').toggleClass('is-selected', on);
					if (on) {
						self.selected[id] = true;
					} else {
						delete self.selected[id];
					}
				});
				self.updateSelectedCount();
				changed();
			});
			$r.on('click', '[data-users-clear]', function () {
				self.selected = {};
				self.renderChecks();
				self.updateSelectedCount();
				changed();
			});
		}
	};

	/* =====================================================================
	 * Format picker helpers
	 * ================================================================== */

	function FormatPicker($root) {
		this.$root = $root;
		this.id = $root.data('format-id');
		var self = this;
		$root.on('change', 'input[type="radio"]', function () {
			self.updateSuffix();
		});
		this.updateSuffix();
	}

	FormatPicker.prototype = {
		get: function () {
			return this.$root.find('input[name="' + this.id + '-format"]:checked').val() || 'csv';
		},
		filename: function () {
			return $.trim(this.$root.find('[data-format-filename]').val()) || cfg.defaultFilename || 'users-export';
		},
		set: function (format, filename) {
			var $radio = this.$root.find('input[name="' + this.id + '-format"][value="' + format + '"]:not(:disabled)');
			if ($radio.length) {
				$radio.prop('checked', true);
			}
			this.$root.find('[data-format-filename]').val(filename || cfg.defaultFilename || 'users-export');
			this.updateSuffix();
		},
		updateSuffix: function () {
			var $s = this.$root.find('[data-format-suffix]');
			$s.text($s.text().replace(/\.[a-z]+$/, '.' + this.get()));
		}
	};

	/* =====================================================================
	 * Export runner (start → batches → finish)
	 * ================================================================== */

	function progressView($box) {
		$box.prop('hidden', false).removeClass('is-done is-error').html(
			'<div class="uempro-progress__label"><span data-p-label></span><span data-p-pct>0%</span></div>' +
			'<div class="uempro-progress__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span></span></div>'
		);
		$box.find('[data-p-label]').text(t.preparing);

		return {
			update: function (done, total) {
				var pct = total ? Math.round((done / total) * 100) : 0;
				$box.find('[data-p-label]').text(sprintf(t.progress, num(done), num(total)));
				$box.find('[data-p-pct]').text(pct + '%');
				$box.find('.uempro-progress__bar').attr('aria-valuenow', pct).find('span').css('width', pct + '%');
			},
			writing: function () {
				$box.find('[data-p-label]').text(t.writing);
				$box.find('[data-p-pct]').text('100%');
				$box.find('.uempro-progress__bar span').css('width', '100%');
			},
			done: function (res) {
				$box.addClass('is-done').html(
					'<div class="uempro-result">' +
					'<span class="uempro-result__icon"><span class="dashicons dashicons-yes" aria-hidden="true"></span></span>' +
					'<div class="uempro-result__text"><strong></strong><span></span></div>' +
					'<a class="uempro-btn uempro-btn--primary" href=""><span class="dashicons dashicons-download" aria-hidden="true"></span></a>' +
					'</div>'
				);
				$box.find('strong').text(t.exportDone);
				$box.find('.uempro-result__text span').text(res.file_name + ' — ' + sprintf(t.exportDoneMsg, num(res.user_count), res.file_size));
				$box.find('a').attr('href', res.download_url).append(document.createTextNode(t.download));
			},
			error: function (msg) {
				$box.addClass('is-error').html(
					'<div class="uempro-result">' +
					'<span class="uempro-result__icon"><span class="dashicons dashicons-warning" aria-hidden="true"></span></span>' +
					'<div class="uempro-result__text"><strong></strong><span></span></div></div>'
				);
				$box.find('strong').text(t.exportFailed);
				$box.find('.uempro-result__text span').text(msg);
			}
		};
	}

	function triggerDownload(url) {
		var $f = $('<iframe hidden aria-hidden="true" tabindex="-1"></iframe>').attr('src', url);
		$('body').append($f);
		setTimeout(function () {
			$f.remove();
		}, 60000);
	}

	/**
	 * @param {Object} start Data for uempro_export_start.
	 * @param {Object} view  progressView() instance.
	 */
	function runExport(start, view) {
		var d = $.Deferred();

		function step(job) {
			ajax('uempro_export_step', { job: job.id }).then(function (r) {
				view.update(r.processed, r.total);
				if (r.done) {
					view.writing();
					ajax('uempro_export_finish', { job: job.id }).then(function (res) {
						view.done(res);
						triggerDownload(res.download_url);
						d.resolve(res);
					}, fail);
				} else {
					step(job);
				}
			}, fail);
		}

		function fail(msg) {
			view.error(msg);
			d.reject(msg);
		}

		ajax('uempro_export_start', start).then(function (job) {
			view.update(0, job.total);
			step(job);
		}, fail);

		return d.promise();
	}

	/* Quick export (dashboard + template cards) */
	$(document).on('click', '[data-uempro-quick]', function () {
		var id = $(this).data('uempro-quick');
		var tpl = cfg.templates && cfg.templates[id];
		var $box = $('<div class="uempro-progress"></div>');
		var $close = $('<button type="button" class="uempro-btn uempro-btn--ghost" data-modal-close></button>').text(t.close);
		var $m = dialog({
			title: sprintf(t.exportingTemplate, tpl ? tpl.name : ''),
			body: $box,
			footer: [$close]
		});
		$m.data('uempro-locked', true);
		$close.prop('disabled', true);

		runExport({ run_template: id }, progressView($box)).always(function () {
			$m.data('uempro-locked', false);
			$close.prop('disabled', false).trigger('focus');
		});
	});

	/* =====================================================================
	 * Export wizard
	 * ================================================================== */

	function initWizard() {
		var $w = $('#uempro-wizard');
		if (!$w.length) {
			return;
		}

		var picker = $w.find('[data-uempro-picker]').data('uempro-picker-instance');
		var format = new FormatPicker($w.find('[data-uempro-format]'));
		var current = 1;
		var furthest = 1;
		var loaded = null; // Loaded template.
		var running = false;

		var scope = new ScopePicker($w.find('[data-uempro-scope]'), countUsers);

		function countUsers() {
			var $c = $w.find('[data-match-count]');
			var invalid = scope.validate();
			if (invalid) {
				$c.text('').removeClass('is-zero');
				lastCount = null;
				updateMeta();
				return;
			}
			$c.text(t.counting).removeClass('is-zero');
			ajax('uempro_count_users', { filters: JSON.stringify(scope.get()) }).then(function (r) {
				lastCount = r.count;
				$c.text(r.count === 1 ? t.oneUserMatch : sprintf(t.usersMatch, num(r.count))).toggleClass('is-zero', r.count === 0);
				updateMeta();
			}, function () {
				$c.text('');
			});
		}

		/* Live summary of each step's choice, shown in the step rail. */
		var lastCount = null;
		function updateMeta() {
			if (!picker || !format) {
				return;
			}
			var users = scope.summary();
			if (lastCount !== null && !scope.validate()) {
				users += ' · ' + (lastCount === 1 ? t.oneUserMatch : sprintf(t.usersMatch, num(lastCount)));
			}
			$w.find('[data-step-meta="1"]').text(users);
			$w.find('[data-step-meta="2"]').text(sprintf(t.nFields, num(picker.get().length)));
			$w.find('[data-step-meta="3"]').text(format.get().toUpperCase() + ' · ' + format.filename());
		}
		var updateMetaSoon = debounce(updateMeta, 150);
		$w.on('change input', updateMetaSoon);
		$w.on('uempro:fieldschange', updateMetaSoon);

		function validate(step) {
			if (step === 1) {
				return scope.validate();
			}
			if (step === 2 && !picker.get().length) {
				return t.pickFields;
			}
			return '';
		}

		function go(step) {
			step = parseInt(step, 10);
			// Validate every step between here and the target.
			for (var s = 1; s < step; s++) {
				var err = validate(s);
				if (err) {
					showStep(s);
					$w.find('.uempro-step[data-step="' + s + '"] [data-step-error]').text(err);
					return;
				}
			}
			showStep(step);
		}

		function showStep(step) {
			current = step;
			furthest = Math.max(furthest, step);
			$w.find('[data-step-error]').text('');
			$w.find('.uempro-step').each(function () {
				$(this).prop('hidden', parseInt($(this).data('step'), 10) !== step);
			});
			$w.find('.uempro-stepper__btn').each(function () {
				var n = parseInt($(this).data('goto'), 10);
				$(this)
					.toggleClass('is-current', n === step)
					.toggleClass('is-done', n < step || (n <= furthest && n !== step))
					.prop('disabled', n > furthest)
					.attr('aria-current', n === step ? 'step' : null);
			});
			if (step === 4) {
				buildReview();
			}
			var top = $w.offset().top - 50;
			if ($(window).scrollTop() > top) {
				$('html, body').scrollTop(top);
			}
		}

		$w.on('click', '[data-next]', function () {
			go(current + 1);
		});
		$w.on('click', '[data-prev]', function () {
			showStep(current - 1);
		});
		$w.on('click', '.uempro-stepper__btn', function () {
			go($(this).data('goto'));
		});

		function buildReview() {
			var fields = picker.get();
			var f = scope.get();
			var $wrap = $w.find('[data-review]').empty();

			function card(step, icon, label, value, sub) {
				var $c = $('<div class="uempro-reviewcard"></div>');
				$c.append('<span class="uempro-reviewcard__icon" aria-hidden="true"><span class="dashicons ' + icon + '"></span></span>');
				var $body = $('<div class="uempro-reviewcard__body"></div>');
				$body.append($('<span class="uempro-reviewcard__label"></span>').text(label));
				$body.append($('<strong class="uempro-reviewcard__value"></strong>').text(value));
				$body.append($('<span class="uempro-reviewcard__sub"></span>').text(sub || ''));
				$c.append($body);
				$c.append($('<button type="button" class="uempro-link" data-review-goto></button>').attr('data-review-goto', step).text(t.edit));
				return $c;
			}

			var label = function (n) {
				return $w.find('.uempro-stepper__btn[data-goto="' + n + '"] .uempro-stepper__label').text();
			};
			var order = sprintf(t.sortedBy, $w.find('[data-scope-orderby] option:selected').text() + ', ' + (f.order === 'DESC' ? t.descending : t.ascending));

			var $grid = $('<div class="uempro-reviewgrid"></div>');
			var $users = card(1, 'dashicons-groups', label(1), scope.summary(), t.counting);
			$grid.append($users);
			$grid.append(card(2, 'dashicons-columns', label(2), sprintf(t.nFields, num(fields.length)), order));
			$grid.append(card(3, 'dashicons-media-spreadsheet', label(3), format.get().toUpperCase(), format.filename() + '-….' + format.get()));
			$wrap.append($grid);

			var $cols = $('<div class="uempro-review__cols"></div>');
			$.each(fields, function (i, key) {
				$cols.append($('<span></span>').append($('<b></b>').text(columnLetter(i))).append(document.createTextNode(picker.labelFor(key))));
			});
			$wrap.append($('<div class="uempro-reviewcols"></div>').append($('<p class="uempro-reviewcols__title"></p>').text(t.columnsInFile)).append($cols));

			ajax('uempro_count_users', { filters: JSON.stringify(f) }).then(function (r) {
				$users.find('.uempro-reviewcard__sub').text(r.count === 1 ? t.oneUserMatch : sprintf(t.usersMatch, num(r.count)));
			});

			syncSavebox();
		}

		$w.on('click', '[data-review-goto]', function () {
			go($(this).attr('data-review-goto'));
		});

		/* ---- templates in the wizard ---- */
		function syncSavebox() {
			var $up = $w.find('[data-tpl-update]');
			if (loaded) {
				$up.prop('hidden', false).text(sprintf(t.updateTemplate, loaded.name));
			} else {
				$up.prop('hidden', true);
			}
		}

		function applyTemplate(tpl, jump) {
			loaded = tpl;
			scope.set(tpl.filters || {});
			picker.set(tpl.fields || []);
			format.set(tpl.format, tpl.filename);
			$('#uempro-template-select').val(String(tpl.id));
			var $b = $('#uempro-template-banner');
			$b.find('[data-banner-text]').text(sprintf(t.templateLoaded, tpl.name));
			$b.prop('hidden', false);
			furthest = 4;
			showStep(jump || 2);
			countUsers();
		}

		function clearTemplate() {
			loaded = null;
			scope.set({});
			picker.set(cfg.defaultFields || []);
			format.set(cfg.defaultFormat, cfg.defaultFilename);
			$('#uempro-template-select').val('');
			$('#uempro-template-banner').prop('hidden', true);
			furthest = 1;
			showStep(1);
			countUsers();
		}

		$('#uempro-template-select').on('change', function () {
			var id = $(this).val();
			if (!id) {
				clearTemplate();
				return;
			}
			if (cfg.templates[id]) {
				applyTemplate(cfg.templates[id]);
			} else {
				ajax('uempro_template_get', { id: id }).then(function (tpl) {
					cfg.templates[tpl.id] = tpl;
					applyTemplate(tpl);
				}, function (msg) {
					toast(msg, 'error');
				});
			}
		});

		$('#uempro-template-banner').on('click', '[data-banner-go]', function () {
			go(4);
		}).on('click', '[data-banner-clear]', clearTemplate);

		function currentTemplateData(extra) {
			return $.extend({
				fields: JSON.stringify(picker.get()),
				filters: JSON.stringify(scope.get()),
				format: format.get(),
				filename: format.filename()
			}, extra);
		}

		function rememberTemplate(tpl, isNew) {
			cfg.templates[tpl.id] = tpl;
			loaded = tpl;
			var $sel = $('#uempro-template-select');
			if ($sel.length) {
				var $opt = $sel.find('option[value="' + tpl.id + '"]');
				if (!$opt.length) {
					$opt = $('<option></option>').val(tpl.id).appendTo($sel);
				}
				$opt.text(tpl.name);
				$sel.val(String(tpl.id));
			}
			toast(isNew ? t.templateSaved : t.templateUpdated);
			syncSavebox();
		}

		$w.on('click', '[data-tpl-new]', function () {
			$w.find('[data-savebox-actions]').prop('hidden', true);
			$w.find('[data-savebox-form]').prop('hidden', false).find('input').val('').trigger('focus');
		});
		$w.on('click', '[data-tpl-cancel]', function () {
			$w.find('[data-savebox-form]').prop('hidden', true);
			$w.find('[data-savebox-actions]').prop('hidden', false);
		});
		$w.on('keydown', '#uempro-wiz-tplname', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				$w.find('[data-tpl-save]').trigger('click');
			}
		});
		$w.on('click', '[data-tpl-save]', function () {
			var $btn = $(this);
			var name = $.trim($('#uempro-wiz-tplname').val());
			if (!name) {
				toast(t.nameRequired, 'error');
				$('#uempro-wiz-tplname').trigger('focus');
				return;
			}
			$btn.addClass('is-busy');
			ajax('uempro_template_save', currentTemplateData({ name: name })).then(function (tpl) {
				rememberTemplate(tpl, true);
				$w.find('[data-tpl-cancel]').trigger('click');
			}, function (msg) {
				toast(msg, 'error');
			}).always(function () {
				$btn.removeClass('is-busy');
			});
		});
		$w.on('click', '[data-tpl-update]', function () {
			if (!loaded) {
				return;
			}
			var $btn = $(this);
			$btn.addClass('is-busy');
			ajax('uempro_template_save', currentTemplateData({
				id: loaded.id,
				name: loaded.name,
				description: loaded.description || ''
			})).then(function (tpl) {
				rememberTemplate(tpl, false);
			}, function (msg) {
				toast(msg, 'error');
			}).always(function () {
				$btn.removeClass('is-busy');
			});
		});

		/* ---- run ---- */
		$w.on('click', '[data-run]', function () {
			if (running) {
				return;
			}
			for (var s = 1; s <= 3; s++) {
				var err = validate(s);
				if (err) {
					go(s + 1);
					return;
				}
			}
			var $btn = $(this);
			running = true;
			$btn.addClass('is-busy').find('[data-run-label]').text(t.exporting);
			$w.find('.uempro-stepper__btn, [data-prev]').prop('disabled', true);

			var start = currentTemplateData({ template_id: loaded ? loaded.id : 0 });
			runExport(start, progressView($w.find('[data-progress]'))).always(function () {
				running = false;
				$btn.removeClass('is-busy').find('[data-run-label]').text(t.runExport);
				$w.find('[data-prev]').prop('disabled', false);
				showStep(4);
			});
		});

		/* ---- initial state ---- */
		if (cfg.preloadTemplate && cfg.templates[cfg.preloadTemplate]) {
			applyTemplate(cfg.templates[cfg.preloadTemplate]);
		} else {
			countUsers();
		}
		updateMeta();
	}

	/* =====================================================================
	 * Templates screen
	 * ================================================================== */

	function initTemplates() {
		var $editor = $('#uempro-tpl-editor');
		if (!$editor.length) {
			return;
		}

		var picker = $editor.find('[data-uempro-picker]').data('uempro-picker-instance');
		var scope = new ScopePicker($editor.find('[data-uempro-scope]'));
		var format = new FormatPicker($editor.find('[data-uempro-format]'));
		var editing = 0;

		function open(tpl) {
			editing = tpl ? tpl.id : 0;
			$editor.find('[data-editor-title]').text(tpl ? t.editTemplate : t.newTemplate);
			$editor.find('[data-editor-save]').text(tpl ? t.saveChanges : t.saveTemplate);
			$editor.find('[data-editor-error]').text('');
			$editor.find('[data-editor-name]').val(tpl ? tpl.name : '').attr('data-autofocus', '');
			$editor.find('[data-editor-desc]').val(tpl ? tpl.description : '');
			$editor.find('.uempro-fp__search').val('');
			picker.filter('');
			scope.set(tpl ? tpl.filters : {});
			picker.set(tpl ? tpl.fields : (cfg.defaultFields || []));
			format.set(tpl ? tpl.format : cfg.defaultFormat, tpl ? tpl.filename : cfg.defaultFilename);
			openModal($editor);
			$editor.find('.uempro-modal__body').scrollTop(0);
		}

		$(document).on('click', '[data-tpl-create]', function () {
			open(null);
		});

		$(document).on('click', '[data-tpl-edit]', function () {
			var id = $(this).data('tpl-edit');
			if (cfg.templates[id]) {
				open(cfg.templates[id]);
			}
		});

		$editor.on('click', '[data-editor-save]', function () {
			var $btn = $(this);
			var $err = $editor.find('[data-editor-error]').text('');
			var name = $.trim($editor.find('[data-editor-name]').val());

			var problem = !name ? t.nameRequired : (scope.validate() || (!picker.get().length ? t.pickFields : ''));
			if (problem) {
				$err.text(problem);
				return;
			}

			$btn.addClass('is-busy').text(t.saving);
			ajax('uempro_template_save', {
				id: editing,
				name: name,
				description: $editor.find('[data-editor-desc]').val(),
				fields: JSON.stringify(picker.get()),
				filters: JSON.stringify(scope.get()),
				format: format.get(),
				filename: format.filename()
			}).then(function () {
				window.location.href = cfg.urls.templates + '&uempro_notice=template-saved';
			}, function (msg) {
				$err.text(msg);
				$btn.removeClass('is-busy').text(editing ? t.saveChanges : t.saveTemplate);
			});
		});

		$(document).on('click', '[data-tpl-duplicate]', function () {
			var $btn = $(this).addClass('is-busy');
			ajax('uempro_template_duplicate', { id: $btn.data('tpl-duplicate') }).then(function () {
				window.location.href = cfg.urls.templates + '&uempro_notice=template-saved';
			}, function (msg) {
				$btn.removeClass('is-busy');
				toast(msg, 'error');
			});
		});

		$(document).on('click', '[data-tpl-delete]', function () {
			var id = $(this).data('tpl-delete');
			var tpl = cfg.templates[id] || { name: '' };
			confirmDialog(t.deleteTemplateQ, sprintf(t.deleteTemplateMsg, tpl.name), t.delete).then(function () {
				ajax('uempro_template_delete', { id: id }).then(function (r) {
					delete cfg.templates[id];
					$('[data-tpl-id="' + id + '"]').fadeOut(150, function () {
						$(this).remove();
						if (!r.count) {
							$('[data-tpl-empty]').prop('hidden', false);
						}
					});
					toast(t.templateDeleted);
				}, function (msg) {
					toast(msg, 'error');
				});
			});
		});

		if (/[?&]new=1/.test(window.location.search)) {
			open(null);
		}
	}

	/* =====================================================================
	 * History screen
	 * ================================================================== */

	$(document).on('click', '[data-history-delete]', function () {
		var id = $(this).data('history-delete');
		confirmDialog(t.deleteExportQ, t.deleteExportMsg, t.delete).then(function () {
			ajax('uempro_history_delete', { id: id }).then(function () {
				$('[data-history-id="' + id + '"]').fadeOut(150, function () {
					$(this).remove();
				});
				toast(t.exportDeleted);
			}, function (msg) {
				toast(msg, 'error');
			});
		});
	});

	$(document).on('click', '[data-history-clear]', function () {
		confirmDialog(t.clearHistoryQ, t.clearHistoryMsg, t.deleteAll).then(function () {
			ajax('uempro_history_clear').then(function () {
				window.location.href = cfg.urls.history;
			}, function (msg) {
				toast(msg, 'error');
			});
		});
	});

	/* =====================================================================
	 * Docs: highlight the current section in the table of contents
	 * ================================================================== */

	function initDocs() {
		var $links = $('.uempro-docs__toc a');
		if (!$links.length || !('IntersectionObserver' in window)) {
			return;
		}
		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					$links.removeClass('is-active').filter('[href="#' + entry.target.id + '"]').addClass('is-active');
				}
			});
		}, { rootMargin: '-40% 0px -55% 0px' });
		$('.uempro-doc').each(function () {
			observer.observe(this);
		});
	}


	/* =====================================================================
	 * Import wizard
	 * ================================================================== */

	function initImport() {
		var $w = $('#uempro-import');
		if (!$w.length) {
			return;
		}

		var job = null;      // Upload response.
		var current = 1;
		var furthest = 1;
		var running = false;
		var targets = cfg.importTargets || [];
		var targetLabel = {};
		$.each(targets, function (i, tg) {
			targetLabel[tg.key] = tg.label;
		});

		function showStep(step) {
			current = step;
			furthest = Math.max(furthest, step);
			$w.find('[data-step-error]').text('');
			$w.find('.uempro-step').each(function () {
				$(this).prop('hidden', parseInt($(this).data('step'), 10) !== step);
			});
			$w.find('.uempro-stepper__btn').each(function () {
				var n = parseInt($(this).data('goto'), 10);
				$(this)
					.toggleClass('is-current', n === step)
					.toggleClass('is-done', n < step || (n <= furthest && n !== step))
					.prop('disabled', n > furthest || running)
					.attr('aria-current', n === step ? 'step' : null);
			});
			if (step === 4) {
				buildReview();
			}
		}

		function error(step, msg) {
			$w.find('.uempro-step[data-step="' + step + '"] [data-step-error]').text(msg || '');
		}

		/* ---- step 1: upload ---- */
		var $drop = $w.find('[data-drop]');
		var $file = $w.find('[data-file]');

		function upload(file) {
			if (!file) {
				return;
			}
			error(1, '');
			var ext = (file.name.split('.').pop() || '').toLowerCase();
			if ($.inArray(ext, ['csv', 'tsv', 'txt', 'xlsx', 'json', 'xml']) === -1) {
				error(1, t.badType);
				return;
			}
			if (cfg.maxUpload && file.size > cfg.maxUpload) {
				error(1, sprintf(t.tooBig, cfg.maxUploadLabel));
				return;
			}

			var fd = new FormData();
			fd.append('action', 'uempro_import_upload');
			fd.append('nonce', cfg.nonce);
			fd.append('file', file);

			$drop.addClass('is-busy');
			$w.find('[data-upload-status]').text(t.uploading);

			$.ajax({
				url: cfg.ajaxUrl,
				method: 'POST',
				data: fd,
				processData: false,
				contentType: false,
				dataType: 'json'
			}).done(function (res) {
				if (!res || !res.success) {
					error(1, (res && res.data && res.data.message) || t.genericError);
					return;
				}
				job = res.data;
				buildMapping();
				furthest = 2;
				showStep(2);
			}).fail(function (xhr) {
				var msg = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message;
				error(1, msg || t.requestFailed);
			}).always(function () {
				$drop.removeClass('is-busy');
				$w.find('[data-upload-status]').text('');
				$file.val('');
			});
		}

		$file.on('change', function () {
			upload(this.files && this.files[0]);
		});
		$drop.on('dragenter dragover', function (e) {
			e.preventDefault();
			$drop.addClass('is-dragover');
		}).on('dragleave dragend drop', function (e) {
			e.preventDefault();
			$drop.removeClass('is-dragover');
		}).on('drop', function (e) {
			var dt = e.originalEvent.dataTransfer;
			upload(dt && dt.files && dt.files[0]);
		});

		/* ---- step 2: mapping ---- */
		function targetSelect(selected) {
			var groups = [
				['identify', t.groupIdentify],
				['profile', t.groupProfile],
				['password', t.groupPassword],
				['custom', t.groupCustom],
				['hidden', t.groupHidden]
			];
			var html = '<select class="uempro-select" data-map-select>';
			html += '<option value="">' + esc(t.skipColumn) + '</option>';
			$.each(groups, function (i, g) {
				var opts = '';
				$.each(targets, function (j, tg) {
					if (tg.group === g[0]) {
						opts += '<option value="' + esc(tg.key) + '"' + (tg.key === selected ? ' selected' : '') + '>' + esc(tg.label) + '</option>';
					}
				});
				if (opts) {
					html += '<optgroup label="' + esc(g[1]) + '">' + opts + '</optgroup>';
				}
			});
			html += '<option value="__new">' + esc(t.newMeta) + '</option>';
			html += '</select>';
			return html;
		}

		function buildMapping() {
			var $body = $w.find('[data-map-body]').empty();
			$w.find('[data-file-chip]').html(
				'<span class="uempro-badge uempro-badge--' + esc(job.format.toLowerCase()) + '">' + esc(job.format) + '</span>' +
				esc(job.file_name) + ' · ' + esc(sprintf(t.rowsCount, num(job.total)))
			);

			$.each(job.headers, function (i, header) {
				var samples = [];
				$.each(job.sample || [], function (k, row) {
					var v = row[header];
					if (v !== undefined && v !== null && String(v) !== '') {
						samples.push(String(v));
					}
				});
				var suggested = job.suggestion && job.suggestion[header] ? job.suggestion[header] : '';
				var $tr = $('<tr></tr>').attr('data-column', header);
				$tr.append($('<td></td>').text(header).append(suggested ? '<span class="uempro-map__auto">' + esc(t.matched) + '</span>' : ''));
				var $samples = $('<td></td>');
				$.each(samples.slice(0, 3), function (k, v) {
					$samples.append($('<span class="uempro-map__sample"></span>').text(v).attr('title', v));
				});
				$tr.append($samples);
				var $target = $('<div class="uempro-map__target"></div>').append(targetSelect(suggested))
					.append('<input type="text" class="uempro-input" data-map-meta hidden placeholder="' + esc(t.metaKeyPh) + '" aria-label="' + esc(t.newMeta) + '">');
				$tr.append($('<td></td>').append($target));
				$body.append($tr);
			});
			syncMapping();
		}

		function syncMapping() {
			var mapped = 0;
			$w.find('[data-map-body] tr').each(function () {
				var $tr = $(this);
				var val = $tr.find('[data-map-select]').val();
				$tr.find('[data-map-meta]').prop('hidden', val !== '__new');
				$tr.toggleClass('is-skipped', !val);
				if (val) {
					mapped++;
				}
			});
			$w.find('[data-mapped-count]').text(sprintf(t.mappedCount, num(mapped), num(job ? job.headers.length : 0)));
		}

		$w.on('change', '[data-map-select]', function () {
			syncMapping();
			if ($(this).val() === '__new') {
				var $meta = $(this).closest('tr').find('[data-map-meta]');
				if (!$meta.val()) {
					$meta.val(String($(this).closest('tr').data('column')).toLowerCase().replace(/[^a-z0-9_\-]+/g, '_').replace(/^_+|_+$/g, ''));
				}
				$meta.trigger('focus');
			}
		});

		function getMapping() {
			var mapping = {};
			$w.find('[data-map-body] tr').each(function () {
				var $tr = $(this);
				var val = $tr.find('[data-map-select]').val();
				if (val === '__new') {
					var key = $.trim($tr.find('[data-map-meta]').val());
					val = key ? 'meta:' + key : '__invalid';
				}
				if (val) {
					mapping[$tr.data('column')] = val;
				}
			});
			return mapping;
		}

		function validateMapping() {
			var mapping = getMapping();
			var seen = {};
			var hasId = false;
			var problem = '';
			$.each(mapping, function (col, target) {
				if (target === '__invalid' || (target.indexOf('meta:') === 0 && !/^meta:[A-Za-z0-9_\-]+$/.test(target))) {
					problem = t.badMetaKey;
					return false;
				}
				if (seen[target]) {
					problem = t.dupTarget;
					return false;
				}
				seen[target] = true;
				if (target === 'user_email' || target === 'user_login' || target === 'ID') {
					hasId = true;
				}
			});
			if (!problem && !hasId) {
				problem = t.needIdentifier;
			}
			return problem;
		}

		/* ---- step 3: options ---- */
		function getOptions() {
			return {
				match_by: $w.find('input[name="uempro-imp-match"]:checked').val() || 'email',
				existing: $w.find('input[name="uempro-imp-existing"]:checked').val() || 'update',
				create: $w.find('[data-opt-create]').is(':checked') ? 1 : 0,
				default_role: $w.find('[data-opt-role]').val(),
				skip_empty: $w.find('[data-opt-empty]').is(':checked') ? 1 : 0,
				send_email: $w.find('[data-opt-email]').is(':checked') ? 1 : 0
			};
		}

		/* ---- step 4: review + run ---- */
		function buildReview() {
			var mapping = getMapping();
			var o = getOptions();
			var $dl = $w.find('[data-review]').empty();
			var cols = $('<div class="uempro-review__cols"></div>');
			$.each(mapping, function (col, target) {
				var label = target.indexOf('meta:') === 0 && !targetLabel[target] ? target.substr(5) : (targetLabel[target] || target);
				cols.append($('<span></span>').text(col + ' → ' + label));
			});
			var row = function (k, v) {
				$dl.append($('<dt></dt>').text(k)).append($('<dd></dd>').append(v));
			};
			row($w.find('.uempro-stepper__btn[data-goto="1"] .uempro-stepper__label').text(), $('<span></span>').text(job.file_name + ' — ' + sprintf(t.fileSummary, num(job.total), job.format)));
			row($w.find('.uempro-stepper__btn[data-goto="2"] .uempro-stepper__label').text(), $('<div></div>').text(sprintf(t.mappedCount, num(Object.keys(mapping).length), num(job.headers.length))).append(cols));
			var optText = [
				$w.find('input[name="uempro-imp-match"]:checked').next().text(),
				$w.find('input[name="uempro-imp-existing"]:checked').next().text(),
				o.create ? $w.find('label[for="uempro-imp-create"]').text() : '',
				$w.find('[data-opt-role] option:selected').text()
			].filter(Boolean).join(' · ');
			row($w.find('.uempro-stepper__btn[data-goto="3"] .uempro-stepper__label').text(), $('<span></span>').text(optText));
		}

		function go(step) {
			step = parseInt(step, 10);
			if (step >= 2 && !job) {
				showStep(1);
				return;
			}
			if (step >= 3) {
				var problem = validateMapping();
				if (problem) {
					showStep(2);
					error(2, problem);
					return;
				}
			}
			showStep(step);
		}

		$w.on('click', '[data-next]', function () {
			go(current + 1);
		});
		$w.on('click', '[data-prev]', function () {
			showStep(current - 1);
		});
		$w.on('click', '.uempro-stepper__btn', function () {
			go($(this).data('goto'));
		});
		$w.on('click', '[data-restart]', function () {
			job = null;
			furthest = 1;
			showStep(1);
		});

		function renderResult(r) {
			var $res = $w.find('[data-result]').prop('hidden', false).empty();
			var stats = $('<div class="uempro-importresult__stats"></div>');
			$.each([['created', t.created], ['updated', t.updated], ['skipped', t.skipped], ['failed', t.failed]], function (i, s) {
				stats.append('<div class="uempro-importresult__stat uempro-importresult__stat--' + s[0] + '"><strong>' + esc(num(r.stats[s[0]])) + '</strong><span>' + esc(s[1]) + '</span></div>');
			});
			$res.append(stats);

			if (r.errors && r.errors.length) {
				var $tbl = $('<table class="uempro-table"><thead><tr><th>' + esc(t.row) + '</th><th>' + esc(t.problem) + '</th></tr></thead><tbody></tbody></table>');
				$.each(r.errors, function (i, e) {
					$tbl.find('tbody').append($('<tr></tr>').append($('<td></td>').text(e.row)).append($('<td></td>').text(e.message)));
				});
				$res.append($('<div class="uempro-importresult__errors"></div>').append($tbl));
				if (r.more) {
					$res.append($('<p class="uempro-muted"></p>').text(sprintf(t.moreProblems, num(r.more))));
				}
			}

			$res.append(
				'<div class="uempro-importresult__actions">' +
				'<a class="uempro-btn uempro-btn--primary" href="' + esc(cfg.urls.users) + '">' + esc(t.viewUsers) + '</a>' +
				'<a class="uempro-btn uempro-btn--ghost" href="' + esc(cfg.urls.import) + '">' + esc(t.importAnother) + '</a>' +
				'</div>'
			);
		}

		$w.on('click', '[data-run]', function () {
			if (running || !job) {
				return;
			}
			var problem = validateMapping();
			if (problem) {
				go(2);
				return;
			}

			var $btn = $(this);
			var $box = $w.find('[data-progress]');
			running = true;
			$btn.addClass('is-busy').find('[data-run-label]').text(t.importing);
			$w.find('[data-prev], .uempro-stepper__btn').prop('disabled', true);
			$box.prop('hidden', false).removeClass('is-done is-error').html(
				'<div class="uempro-progress__label"><span data-p-label></span><span data-p-pct>0%</span></div>' +
				'<div class="uempro-progress__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span></span></div>'
			);
			$box.find('[data-p-label]').text(t.preparing);

			function update(done, total) {
				var pct = total ? Math.round((done / total) * 100) : 0;
				$box.find('[data-p-label]').text(sprintf(t.importProgress, num(done), num(total)));
				$box.find('[data-p-pct]').text(pct + '%');
				$box.find('.uempro-progress__bar').attr('aria-valuenow', pct).find('span').css('width', pct + '%');
			}

			function finish(r, failedMsg) {
				running = false;
				$btn.removeClass('is-busy').find('[data-run-label]').text(t.runImport);
				if (failedMsg) {
					$box.addClass('is-error').html('<div class="uempro-result"><span class="uempro-result__icon"><span class="dashicons dashicons-warning" aria-hidden="true"></span></span><div class="uempro-result__text"><strong></strong><span></span></div></div>');
					$box.find('strong').text(t.importFailed);
					$box.find('.uempro-result__text span').text(failedMsg);
					$w.find('[data-prev]').prop('disabled', false);
					return;
				}
				update(r.processed, r.total);
				$box.addClass('is-done').find('[data-p-label]').text(t.importDone);
				$w.find('.uempro-step[data-step="4"] .uempro-step__foot, .uempro-step[data-step="4"] .uempro-callout--warn').prop('hidden', true);
				renderResult(r);
			}

			function step() {
				ajax('uempro_import_step', { job: job.id }).then(function (r) {
					if (r.done) {
						finish(r);
					} else {
						update(r.processed, r.total);
						step();
					}
				}, function (msg) {
					finish(null, msg);
				});
			}

			ajax('uempro_import_configure', {
				job: job.id,
				mapping: JSON.stringify(getMapping()),
				options: JSON.stringify(getOptions())
			}).then(function (r) {
				update(0, r.total);
				step();
			}, function (msg) {
				finish(null, msg);
			});
		});
	}

	/* =====================================================================
	 * Boot
	 * ================================================================== */

	$(function () {
		initPickers();
		initWizard();
		initTemplates();
		initImport();
		initDocs();
	});

})(window.jQuery, window.UEMPRO);
