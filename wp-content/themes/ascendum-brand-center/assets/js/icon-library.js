/**
 * Icon Library — real-time client-side search filter.
 *
 * Filters `.icon-library-item` elements by matching the search term
 * against the icon name (data-icon-name attribute, case-insensitive).
 * Shows a "no results" message when nothing matches.
 *
 * @package ascendum-brand-center
 */

( function () {
    'use strict';

    function initIconLibrary( wrap ) {
        const input    = wrap.querySelector( '.icon-library-search-input' );
        const items    = wrap.querySelectorAll( '.icon-library-item' );
        const noResult = wrap.querySelector( '.icon-library-no-results' );

        if ( ! input || ! items.length ) {
            return;
        }

        function filter() {
            const term    = input.value.trim().toLowerCase();
            let   visible = 0;

            items.forEach( function ( item ) {
                const name = ( item.dataset.iconName || '' ).toLowerCase();
                const show = ! term || name.includes( term );
                item.hidden = ! show;
                if ( show ) {
                    visible++;
                }
            } );

            if ( noResult ) {
                noResult.hidden = visible > 0;
            }
        }

        input.addEventListener( 'input', filter );
        input.addEventListener( 'search', filter ); // handles ✕ clear button
    }

    document.querySelectorAll( '.block-icon-library' ).forEach( initIconLibrary );
}() );
