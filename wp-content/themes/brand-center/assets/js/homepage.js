/**
 * Homepage JS — B01
 *
 * Handles search form validation:
 * - Prevents submission when the text input is empty
 * - Shows an inline error message
 * - Clears the error on subsequent input
 *
 * No dependencies — vanilla ES6+.
 */
( function () {
    'use strict';

    const form  = document.getElementById( 'homepage-search-form' );
    if ( ! form ) return;

    const input = form.querySelector( '.hp-search-input' );
    const error = document.getElementById( 'hp-search-error' );

    if ( ! input || ! error ) return;

    form.addEventListener( 'submit', function ( e ) {
        if ( input.value.trim() === '' ) {
            e.preventDefault();
            error.hidden = false;
            input.setAttribute( 'aria-invalid', 'true' );
            input.focus();
        }
    } );

    input.addEventListener( 'input', function () {
        if ( ! error.hidden ) {
            error.hidden = true;
            input.removeAttribute( 'aria-invalid' );
        }
    } );

} )();
