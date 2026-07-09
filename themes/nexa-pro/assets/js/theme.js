(function () {
	'use strict';

	var navigation = document.querySelector('.primary-navigation');
	var transparentHeader = document.querySelector('.nexa-pro-header--transparent');

	if (transparentHeader) {
		function updateTransparentHeader() {
			transparentHeader.classList.toggle('is-scrolled', window.scrollY > 10);
		}

		updateTransparentHeader();
		window.addEventListener('scroll', updateTransparentHeader, { passive: true });
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
