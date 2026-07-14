( function () {
	'use strict';

	var root = document.querySelector( '.nexa-pro-core-builder' );

	if ( ! root ) {
		return;
	}

	var stack = root.querySelector( '[data-component-stack]' );
	var orderInputs = root.querySelector( '[data-order-inputs]' );
	var liveRegion = root.querySelector( '[data-builder-live-region]' );
	var editorForm = root.querySelector( '[data-builder-editor-form]' );
	var dirty = false;
	var dragging = null;

	function announce( message ) {
		if ( liveRegion ) {
			liveRegion.textContent = message;
		}
	}

	function updateOrderInputs() {
		if ( ! stack || ! orderInputs ) {
			return;
		}

		orderInputs.textContent = '';

		Array.prototype.forEach.call( stack.querySelectorAll( '[data-component-card]' ), function ( card ) {
			var id = card.getAttribute( 'data-component-id' );
			var input;

			if ( ! id ) {
				return;
			}

			input = document.createElement( 'input' );
			input.type = 'hidden';
			input.name = 'ordered_ids[]';
			input.value = id;
			orderInputs.appendChild( input );
		} );
	}

	function closestCard( target ) {
		if ( ! target || ! target.closest ) {
			return null;
		}

		return target.closest( '[data-component-card]' );
	}

	if ( stack ) {
		stack.addEventListener( 'dragstart', function ( event ) {
			var card = closestCard( event.target );

			if ( ! card ) {
				return;
			}

			dragging = card;
			card.classList.add( 'is-dragging' );

			if ( event.dataTransfer ) {
				event.dataTransfer.effectAllowed = 'move';
				event.dataTransfer.setData( 'text/plain', card.getAttribute( 'data-component-id' ) || '' );
			}
		} );

		stack.addEventListener( 'dragover', function ( event ) {
			var card = closestCard( event.target );
			var rect;
			var before;

			if ( ! dragging || ! card || card === dragging ) {
				return;
			}

			event.preventDefault();
			rect = card.getBoundingClientRect();
			before = event.clientY < rect.top + rect.height / 2;
			stack.insertBefore( dragging, before ? card : card.nextSibling );
			updateOrderInputs();
		} );

		stack.addEventListener( 'drop', function ( event ) {
			if ( dragging ) {
				event.preventDefault();
				updateOrderInputs();
				announce( 'Component order changed. Use Save order to persist the new order.' );
			}
		} );

		stack.addEventListener( 'dragend', function () {
			if ( dragging ) {
				dragging.classList.remove( 'is-dragging' );
				dragging = null;
			}
		} );
	}

	if ( editorForm ) {
		editorForm.addEventListener( 'input', function () {
			dirty = true;
		} );

		editorForm.addEventListener( 'change', function () {
			dirty = true;
		} );

		editorForm.addEventListener( 'submit', function () {
			dirty = false;
		} );
	}

	root.addEventListener( 'submit', function () {
		dirty = false;
	}, true );

	window.addEventListener( 'beforeunload', function ( event ) {
		if ( ! dirty ) {
			return;
		}

		event.preventDefault();
		event.returnValue = '';
	} );
}() );
