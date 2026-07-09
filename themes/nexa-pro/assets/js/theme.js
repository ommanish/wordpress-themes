(function () {
	'use strict';

	var navigation = document.querySelector('.primary-navigation');

	if (!navigation) {
		return;
	}

	var toggle = navigation.querySelector('.menu-toggle');
	var menu = navigation.querySelector('#primary-menu');

	if (!toggle || !menu) {
		return;
	}

	navigation.classList.add('is-js');

	function closeMenu() {
		navigation.classList.remove('is-open');
		toggle.setAttribute('aria-expanded', 'false');
	}

	function openMenu() {
		navigation.classList.add('is-open');
		toggle.setAttribute('aria-expanded', 'true');
	}

	toggle.addEventListener('click', function () {
		if (navigation.classList.contains('is-open')) {
			closeMenu();
			return;
		}

		openMenu();
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			closeMenu();
			toggle.focus();
		}
	});

	document.addEventListener('click', function (event) {
		if (!navigation.contains(event.target)) {
			closeMenu();
		}
	});
})();
