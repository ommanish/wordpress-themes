(function () {
	'use strict';

	var admin = document.querySelector('.nexa-pro-admin');

	if (!admin) {
		return;
	}

	function initMediaFields() {
		if (!window.wp || !window.wp.media) {
			return;
		}

		var fields = admin.querySelectorAll('[data-nexa-pro-media-field]');

		fields.forEach(function (field) {
			var input = field.querySelector('[data-nexa-pro-media-input]');
			var selectButton = field.querySelector('[data-nexa-pro-media-select]');
			var removeButton = field.querySelector('[data-nexa-pro-media-remove]');
			var preview = field.querySelector('[data-nexa-pro-media-preview]');

			if (!input || !selectButton || !removeButton || !preview) {
				return;
			}

			var frame = null;

			function setRemoveState() {
				removeButton.disabled = !input.value;
			}

			selectButton.addEventListener('click', function () {
				if (!frame) {
					frame = window.wp.media({
						title: selectButton.textContent,
						button: {
							text: selectButton.textContent
						},
						library: {
							type: 'image'
						},
						multiple: false
					});

					frame.on('select', function () {
						var attachment = frame.state().get('selection').first().toJSON();
						var previewUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
						var alt = attachment.alt || attachment.title || '';

						input.value = attachment.id || '';
						preview.textContent = '';

						if (previewUrl) {
							var image = document.createElement('img');
							image.className = 'nexa-pro-admin-media__image';
							image.src = previewUrl;
							image.alt = alt;
							preview.appendChild(image);
						}

						setRemoveState();
					});
				}

				frame.open();
			});

			removeButton.addEventListener('click', function () {
				input.value = '';
				preview.textContent = '';
				setRemoveState();
				input.focus();
			});

			input.addEventListener('input', setRemoveState);
			setRemoveState();
		});
	}

	function updateTemplateToken(element, token, replacement) {
		Array.prototype.forEach.call(element.querySelectorAll('*'), function (node) {
			Array.prototype.forEach.call(node.attributes, function (attribute) {
				if (attribute.value.indexOf(token) !== -1) {
					node.setAttribute(attribute.name, attribute.value.split(token).join(replacement));
				}
			});
		});
	}

	function initRepeater(repeater) {
		var items = repeater.querySelector('[data-nexa-pro-repeater-items]');
		var template = repeater.querySelector('[data-nexa-pro-repeater-template]');
		var addButton = repeater.querySelector('[data-nexa-pro-repeater-add]');
		var emptyMessage = repeater.querySelector('[data-nexa-pro-repeater-empty]');
		var status = repeater.querySelector('[data-nexa-pro-repeater-status]');
		var itemLabel = repeater.getAttribute('data-nexa-pro-item-label') || 'Item';
		var counter = 0;

		if (!items || !template || !addButton) {
			return;
		}

		addButton.hidden = false;

		Array.prototype.forEach.call(items.querySelectorAll('[data-nexa-pro-repeater-row]'), function (row) {
			var rowKey = row.getAttribute('data-nexa-pro-row-key') || '';
			var match = rowKey.match(/^new-(\d+)$/);

			if (match) {
				counter = Math.max(counter, parseInt(match[1], 10));
			}
		});

		function announce(message) {
			if (status) {
				status.textContent = message;
			}
		}

		function getRows() {
			return Array.prototype.slice.call(items.querySelectorAll('[data-nexa-pro-repeater-row]'));
		}

		function isRemoved(row) {
			var checkbox = row.querySelector('[data-nexa-pro-repeater-remove-checkbox]');

			return !!(checkbox && checkbox.checked);
		}

		function getTitleInput(row) {
			return row.querySelector('[data-nexa-pro-repeater-title]');
		}

		function updateRows() {
			var activeRows = getRows().filter(function (row) {
				return !isRemoved(row);
			});

			getRows().forEach(function (row) {
				var removed = isRemoved(row);
				var title = row.querySelector('[data-nexa-pro-repeater-row-title]');
				var moveUp = row.querySelector('[data-nexa-pro-repeater-move="up"]');
				var moveDown = row.querySelector('[data-nexa-pro-repeater-move="down"]');
				var undo = row.querySelector('[data-nexa-pro-repeater-undo]');
				var removeButton = row.querySelector('[data-nexa-pro-repeater-remove]');
				var activeIndex = activeRows.indexOf(row);
				var number = activeIndex + 1;
				var labelNumber = activeIndex >= 0 ? number : '';

				row.classList.toggle('is-removed', removed);

				if (title) {
					title.textContent = removed ? itemLabel + ' marked for removal' : itemLabel + ' ' + number;
				}

				if (moveUp) {
					moveUp.hidden = false;
					moveUp.disabled = removed || activeIndex <= 0;
					moveUp.setAttribute('aria-label', removed ? 'Move removed ' + itemLabel + ' up' : 'Move ' + itemLabel + ' ' + labelNumber + ' up');
				}

				if (moveDown) {
					moveDown.hidden = false;
					moveDown.disabled = removed || activeIndex === -1 || activeIndex >= activeRows.length - 1;
					moveDown.setAttribute('aria-label', removed ? 'Move removed ' + itemLabel + ' down' : 'Move ' + itemLabel + ' ' + labelNumber + ' down');
				}

				if (removeButton) {
					removeButton.hidden = false;
					removeButton.disabled = removed;
					removeButton.setAttribute('aria-label', removed ? itemLabel + ' already marked for removal' : 'Remove ' + itemLabel + ' ' + labelNumber);
				}

				if (undo) {
					undo.classList.toggle('hidden', !removed);
					undo.hidden = !removed;
					undo.setAttribute('aria-label', 'Undo removal for ' + itemLabel);
				}
			});

			if (emptyMessage) {
				emptyMessage.hidden = activeRows.length > 0;
			}
		}

		function createRow() {
			counter += 1;

			var rowKey = 'new-' + counter;
			var fragment = template.content.cloneNode(true);
			var row = fragment.querySelector('[data-nexa-pro-repeater-row]');

			if (!row) {
				return null;
			}

			updateTemplateToken(row, '__index__', rowKey);
			row.classList.remove('is-template');
			row.setAttribute('data-nexa-pro-row-key', rowKey);

			return row;
		}

		function markRemoved(row) {
			var checkbox = row.querySelector('[data-nexa-pro-repeater-remove-checkbox]');

			if (checkbox) {
				checkbox.checked = true;
			}

			updateRows();
			announce(itemLabel + ' marked for removal.');

			var nextRow = getRows().filter(function (candidate) {
				return candidate !== row && !isRemoved(candidate);
			})[0];
			var nextInput = nextRow ? getTitleInput(nextRow) : addButton;

			if (nextInput) {
				nextInput.focus();
			}
		}

		function undoRemoved(row) {
			var checkbox = row.querySelector('[data-nexa-pro-repeater-remove-checkbox]');
			var titleInput = getTitleInput(row);

			if (checkbox) {
				checkbox.checked = false;
			}

			updateRows();
			announce(itemLabel + ' removal undone.');

			if (titleInput) {
				titleInput.focus();
			}
		}

		function moveRow(row, direction) {
			var activeRows = getRows().filter(function (candidate) {
				return !isRemoved(candidate);
			});
			var index = activeRows.indexOf(row);
			var target = direction === 'up' ? activeRows[index - 1] : activeRows[index + 1];
			var titleInput = getTitleInput(row);

			if (!target) {
				return;
			}

			if (direction === 'up') {
				items.insertBefore(row, target);
			} else {
				items.insertBefore(target, row);
			}

			updateRows();
			announce(itemLabel + ' moved ' + direction + '.');

			if (titleInput) {
				titleInput.focus();
			}
		}

		addButton.addEventListener('click', function () {
			var row = createRow();
			var titleInput = null;

			if (!row) {
				return;
			}

			items.appendChild(row);
			updateRows();
			announce(itemLabel + ' added.');

			titleInput = getTitleInput(row);

			if (titleInput) {
				titleInput.focus();
			}
		});

		repeater.addEventListener('click', function (event) {
			var removeButton = event.target.closest('[data-nexa-pro-repeater-remove]');
			var undoButton = event.target.closest('[data-nexa-pro-repeater-undo]');
			var moveButton = event.target.closest('[data-nexa-pro-repeater-move]');
			var row = event.target.closest('[data-nexa-pro-repeater-row]');

			if (!row || !repeater.contains(row)) {
				return;
			}

			if (removeButton) {
				markRemoved(row);
				return;
			}

			if (undoButton) {
				undoRemoved(row);
				return;
			}

			if (moveButton) {
				moveRow(row, moveButton.getAttribute('data-nexa-pro-repeater-move'));
			}
		});

		repeater.addEventListener('change', function (event) {
			if (event.target.matches('[data-nexa-pro-repeater-remove-checkbox]')) {
				updateRows();
				announce(event.target.checked ? itemLabel + ' marked for removal.' : itemLabel + ' removal undone.');
			}
		});

		updateRows();
	}

	initMediaFields();

	admin.querySelectorAll('[data-nexa-pro-repeater]').forEach(initRepeater);
})();
