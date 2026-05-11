/**
 * D01 Search Results — sidebar search input clear button.
 *
 * Toggles visibility of the clear (×) button and search icon
 * based on whether the sidebar input has a value.
 *
 * @package ascendum-brand-center
 */

( function () {
	'use strict';

	const form = document.querySelector( '.search-sidebar-form' );
	if ( ! form ) {
		return;
	}

	const input = form.querySelector( '.search-sidebar-input' );
	const clear = form.querySelector( '.search-sidebar-clear' );
	const icon  = form.querySelector( '.search-sidebar-icon' );

	if ( ! input ) {
		return;
	}

	function syncClear() {
		const hasValue = !! input.value.trim();
		if ( clear ) { clear.hidden = ! hasValue; }
		if ( icon )  { icon.hidden  = hasValue; }
	}

	input.addEventListener( 'input', syncClear );

	if ( clear ) {
		clear.addEventListener( 'click', function () {
			input.value = '';
			syncClear();
			input.focus();
		} );
	}

	syncClear();
}() );
