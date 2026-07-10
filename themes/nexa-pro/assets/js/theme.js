(function () {
	'use strict';

	var navigation = document.querySelector('.primary-navigation');
	var transparentHeader = document.querySelector('.nexa-pro-header--transparent');
	var scheduleModal = document.querySelector('#nexa-pro-schedule');

	if (transparentHeader) {
		function updateTransparentHeader() {
			transparentHeader.classList.toggle('is-scrolled', window.scrollY > 10);
		}

		updateTransparentHeader();
		window.addEventListener('scroll', updateTransparentHeader, { passive: true });
	}

	function initScheduleModal(modal) {
		var lastTrigger = null;
		var supportsDialog = typeof modal.showModal === 'function';
		var focusableSelector = [
			'a[href]',
			'button:not([disabled])',
			'input:not([disabled])',
			'select:not([disabled])',
			'textarea:not([disabled])',
			'[tabindex]:not([tabindex="-1"])'
		].join(',');

		if (!supportsDialog) {
			modal.classList.add('is-fallback');
		}

		function isOpen() {
			return modal.hasAttribute('open') && modal.getAttribute('aria-hidden') !== 'true';
		}

		function getFocusableElements() {
			return Array.prototype.slice.call(modal.querySelectorAll(focusableSelector)).filter(function (element) {
				return element.offsetWidth > 0 || element.offsetHeight > 0 || element === document.activeElement;
			});
		}

		function focusInitialElement() {
			var focusable = getFocusableElements();
			var closeButton = modal.querySelector('[data-nexa-pro-modal-close]');

			if (closeButton) {
				closeButton.focus();
				return;
			}

			if (focusable.length) {
				focusable[0].focus();
				return;
			}

			modal.focus();
		}

		function closeModal() {
			if (!isOpen()) {
				return;
			}

			if (supportsDialog && modal.open) {
				modal.close();
			} else {
				modal.removeAttribute('open');
			}

			modal.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('nexa-pro-modal-open');

			if (lastTrigger && typeof lastTrigger.focus === 'function') {
				lastTrigger.focus();
			}
		}

		function openModal(trigger) {
			lastTrigger = trigger;
			modal.setAttribute('aria-hidden', 'false');
			document.body.classList.add('nexa-pro-modal-open');

			if (supportsDialog) {
				try {
					modal.showModal();
				} catch (error) {
					modal.setAttribute('open', '');
				}
			} else {
				modal.setAttribute('open', '');
			}

			window.setTimeout(focusInitialElement, 0);
		}

		document.addEventListener('click', function (event) {
			var target = event.target;
			var trigger = target && typeof target.closest === 'function' ? target.closest('[data-nexa-pro-modal-trigger="schedule"]') : null;
			var closeButton = target && typeof target.closest === 'function' ? target.closest('[data-nexa-pro-modal-close]') : null;

			if (trigger) {
				event.preventDefault();
				openModal(trigger);
				return;
			}

			if (closeButton && modal.contains(closeButton)) {
				event.preventDefault();
				closeModal();
			}
		});

		modal.addEventListener('click', function (event) {
			if (event.target === modal) {
				closeModal();
			}
		});

		modal.addEventListener('cancel', function (event) {
			event.preventDefault();
			closeModal();
		});

		document.addEventListener('keydown', function (event) {
			var focusable = null;
			var first = null;
			var last = null;

			if (!isOpen()) {
				return;
			}

			if (event.key === 'Escape') {
				event.preventDefault();
				closeModal();
				return;
			}

			if (event.key !== 'Tab') {
				return;
			}

			focusable = getFocusableElements();

			if (!focusable.length) {
				event.preventDefault();
				modal.focus();
				return;
			}

			first = focusable[0];
			last = focusable[focusable.length - 1];

			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		});
	}

	if (scheduleModal) {
		initScheduleModal(scheduleModal);
	}

	if (!navigation) {
		return;
	}

	var toggle = navigation.querySelector('.menu-toggle');
	var panel = navigation.querySelector('#primary-menu-panel');
	var toggleText = toggle ? toggle.querySelector('.menu-toggle__text') : null;
	var openLabel = toggle ? toggle.getAttribute('data-aria-open') : '';
	var closeLabel = toggle ? toggle.getAttribute('data-aria-close') : '';

	if (!toggle || !panel || !toggleText) {
		return;
	}

	navigation.classList.add('is-js');

	function closeMenu() {
		navigation.classList.remove('is-open');
		toggle.setAttribute('aria-expanded', 'false');
		toggle.setAttribute('aria-label', openLabel);
	}

	function openMenu() {
		navigation.classList.add('is-open');
		toggle.setAttribute('aria-expanded', 'true');
		toggle.setAttribute('aria-label', closeLabel);
	}

	toggle.addEventListener('click', function () {
		if (navigation.classList.contains('is-open')) {
			closeMenu();
			return;
		}

		openMenu();
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && navigation.classList.contains('is-open')) {
			closeMenu();
			toggle.focus();
		}
	});

	document.addEventListener('click', function (event) {
		if (!navigation.contains(event.target)) {
			closeMenu();
		}
	});

	panel.addEventListener('click', function (event) {
		if (event.target.closest('a')) {
			closeMenu();
		}
	});
})();
