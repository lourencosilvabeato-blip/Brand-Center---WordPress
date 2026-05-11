/**
 * C03 Collection Detail — asset gallery cycling.
 *
 * Clicking a thumbnail in the strip:
 *  1. Updates the main image src/alt.
 *  2. Marks the clicked thumbnail as active.
 *  3. Shows the corresponding asset description in the left panel.
 *
 * @package ascendum-brand-center
 */

( function () {
	'use strict';

	const viewer = document.querySelector( '[data-collection-viewer]' );
	if ( ! viewer ) {
		return;
	}

	const mainImg   = viewer.querySelector( '[data-main-img]' );
	const thumbBtns = viewer.querySelectorAll( '.collection-thumb-btn' );
	const descPanels = viewer.querySelectorAll( '[data-asset-desc]' );

	if ( ! thumbBtns.length ) {
		return;
	}

	thumbBtns.forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			const idx = this.dataset.assetIdx;

			if ( mainImg ) {
				mainImg.src = this.dataset.src || '';
				mainImg.alt = this.dataset.alt || '';
			}

			thumbBtns.forEach( function ( b ) { b.classList.remove( 'is-active' ); } );
			this.classList.add( 'is-active' );

			descPanels.forEach( function ( p ) { p.classList.remove( 'is-active' ); } );
			const activeDesc = viewer.querySelector( '[data-asset-desc="' + idx + '"]' );
			if ( activeDesc ) {
				activeDesc.classList.add( 'is-active' );
			}
		} );
	} );
}() );
