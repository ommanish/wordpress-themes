(function () {
	'use strict';

	var config = window.nexaProTheme || {};
	var navigation = document.querySelector('.primary-navigation');
	var transparentHeader = document.querySelector('.nexa-pro-header--transparent');
	var reducedMotionQuery = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;

	if (transparentHeader) {
		function updateTransparentHeader() {
			transparentHeader.classList.toggle('is-scrolled', window.scrollY > 10);
		}

		updateTransparentHeader();
		window.addEventListener('scroll', updateTransparentHeader, { passive: true });
	}

	function initModals() {
		var modals = Array.prototype.slice.call(document.querySelectorAll('[data-nexa-pro-modal]'));
		var lastTrigger = null;
		var activeModal = null;
		var focusableSelector = [
			'a[href]',
			'button:not([disabled])',
			'input:not([disabled])',
			'select:not([disabled])',
			'textarea:not([disabled])',
			'[tabindex]:not([tabindex="-1"])'
		].join(',');

		if (!modals.length) {
			return;
		}

		function supportsDialog(modal) {
			return typeof modal.showModal === 'function';
		}

		function isOpen(modal) {
			return modal && modal.hasAttribute('open') && modal.getAttribute('aria-hidden') !== 'true';
		}

		function getModalFromTrigger(trigger) {
			var target = trigger.getAttribute('data-nexa-pro-modal-target') || '';
			var controls = trigger.getAttribute('aria-controls') || '';

			if (!target && controls) {
				target = '#' + controls;
			}

			if (!target || target.charAt(0) !== '#') {
				return null;
			}

			return document.getElementById(target.slice(1));
		}

		function getFocusableElements(modal) {
			return Array.prototype.slice.call(modal.querySelectorAll(focusableSelector)).filter(function (element) {
				return element.offsetWidth > 0 || element.offsetHeight > 0 || element === document.activeElement;
			});
		}

		function focusInitialElement(modal) {
			var closeButton = modal.querySelector('[data-nexa-pro-modal-close]');
			var focusable = getFocusableElements(modal);

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

		function closeModal(modal) {
			if (!isOpen(modal)) {
				return;
			}

			if (supportsDialog(modal) && modal.open) {
				modal.close();
			} else {
				modal.removeAttribute('open');
			}

			modal.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('nexa-pro-modal-open');
			activeModal = null;

			if (lastTrigger && typeof lastTrigger.focus === 'function') {
				lastTrigger.focus();
			}
		}

		function openModal(modal, trigger) {
			lastTrigger = trigger;
			activeModal = modal;
			modal.setAttribute('aria-hidden', 'false');
			document.body.classList.add('nexa-pro-modal-open');

			if (supportsDialog(modal)) {
				try {
					modal.showModal();
				} catch (error) {
					modal.setAttribute('open', '');
				}
			} else {
				modal.setAttribute('open', '');
			}

			window.setTimeout(function () {
				focusInitialElement(modal);
			}, 0);
		}

		modals.forEach(function (modal) {
			if (!supportsDialog(modal)) {
				modal.classList.add('is-fallback');
			}

			modal.addEventListener('click', function (event) {
				if (event.target === modal) {
					closeModal(modal);
				}
			});

			modal.addEventListener('cancel', function (event) {
				event.preventDefault();
				closeModal(modal);
			});
		});

		document.addEventListener('click', function (event) {
			var target = event.target;
			var trigger = target && typeof target.closest === 'function' ? target.closest('[data-nexa-pro-modal-trigger]') : null;
			var closeButton = target && typeof target.closest === 'function' ? target.closest('[data-nexa-pro-modal-close]') : null;
			var modal = null;

			if (trigger) {
				modal = getModalFromTrigger(trigger);

				if (modal && modals.indexOf(modal) !== -1) {
					event.preventDefault();
					openModal(modal, trigger);
				}

				return;
			}

			if (closeButton) {
				modal = closeButton.closest('[data-nexa-pro-modal]');

				if (modal) {
					event.preventDefault();
					closeModal(modal);
				}
			}
		});

		document.addEventListener('keydown', function (event) {
			var focusable = null;
			var first = null;
			var last = null;

			if (!activeModal || !isOpen(activeModal)) {
				return;
			}

			if (event.key === 'Escape') {
				event.preventDefault();
				closeModal(activeModal);
				return;
			}

			if (event.key !== 'Tab') {
				return;
			}

			focusable = getFocusableElements(activeModal);

			if (!focusable.length) {
				event.preventDefault();
				activeModal.focus();
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

	function initMobileNavigation() {
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
	}

	function getSamePageTarget(link) {
		var linkUrl = null;
		var currentUrl = null;
		var hash = '';

		if (!link || link.hasAttribute('download') || link.getAttribute('data-nexa-pro-modal-trigger')) {
			return null;
		}

		try {
			linkUrl = new URL(link.href, window.location.href);
			currentUrl = new URL(window.location.href);
		} catch (error) {
			return null;
		}

		hash = linkUrl.hash ? linkUrl.hash.slice(1) : '';

		if (!hash || linkUrl.origin !== currentUrl.origin || linkUrl.pathname !== currentUrl.pathname || linkUrl.search !== currentUrl.search) {
			return null;
		}

		return document.getElementById(decodeURIComponent(hash));
	}

	function getScrollOffset() {
		var header = document.querySelector('.nexa-pro-header--sticky');
		var adminBar = document.getElementById('wpadminbar');
		var offset = parseInt(config.scrollOffset || 0, 10);

		if (header) {
			offset += header.getBoundingClientRect().height;
		}

		if (adminBar && window.getComputedStyle(adminBar).position === 'fixed') {
			offset += adminBar.getBoundingClientRect().height;
		}

		return offset;
	}

	function focusTarget(target) {
		var hadTabindex = target.hasAttribute('tabindex');
		var originalTabindex = target.getAttribute('tabindex');

		if (!hadTabindex) {
			target.setAttribute('tabindex', '-1');
		}

		target.focus({ preventScroll: true });

		if (!hadTabindex) {
			target.addEventListener('blur', function restoreTabindex() {
				target.removeEventListener('blur', restoreTabindex);
				target.removeAttribute('tabindex');
			});
		} else {
			target.setAttribute('tabindex', originalTabindex);
		}
	}

	function initSinglePageNavigation() {
		var links = [];
		var targets = [];
		var prefersReducedMotion = reducedMotionQuery && reducedMotionQuery.matches;

		if (config.navigationMode !== 'single-page' || !config.isFrontPage) {
			return;
		}

		links = Array.prototype.slice.call(document.querySelectorAll('a[href*="#"]')).filter(function (link) {
			return !!getSamePageTarget(link);
		});

		if (!links.length) {
			return;
		}

		links.forEach(function (link) {
			var target = getSamePageTarget(link);

			if (target && targets.indexOf(target) === -1) {
				targets.push(target);
			}

			link.addEventListener('click', function (event) {
				var top = 0;

				target = getSamePageTarget(link);

				if (!target) {
					return;
				}

				event.preventDefault();
				top = target.getBoundingClientRect().top + window.pageYOffset - getScrollOffset();

				window.scrollTo({
					top: Math.max(0, top),
					behavior: config.smoothScroll && !prefersReducedMotion ? 'smooth' : 'auto'
				});

				window.setTimeout(function () {
					focusTarget(target);
				}, config.smoothScroll && !prefersReducedMotion ? 280 : 0);
			});
		});

		if (!config.activeState || !('IntersectionObserver' in window)) {
			return;
		}

		function setActiveTarget(target) {
			links.forEach(function (link) {
				if (getSamePageTarget(link) === target) {
					link.setAttribute('aria-current', 'location');
				} else if (link.getAttribute('aria-current') === 'location') {
					link.removeAttribute('aria-current');
				}
			});
		}

		var sectionObserver = new IntersectionObserver(function (entries) {
			var visible = entries.filter(function (entry) {
				return entry.isIntersecting;
			}).sort(function (a, b) {
				return b.intersectionRatio - a.intersectionRatio;
			});

			if (visible.length) {
				setActiveTarget(visible[0].target);
			}
		}, {
			rootMargin: '-' + getScrollOffset() + 'px 0px -55% 0px',
			threshold: [0.15, 0.35, 0.55]
		});

		targets.forEach(function (target) {
			sectionObserver.observe(target);
		});
	}

	initModals();
	initMobileNavigation();
	initSinglePageNavigation();
})();
