(function () {
	'use strict';

	if (!window.wp || !window.wp.media) {
		return;
	}

	var fields = document.querySelectorAll('[data-nexa-pro-media-field]');

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
					preview.innerHTML = '';

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
			preview.innerHTML = '';
			setRemoveState();
			input.focus();
		});

		input.addEventListener('input', setRemoveState);
		setRemoveState();
	});
})();
