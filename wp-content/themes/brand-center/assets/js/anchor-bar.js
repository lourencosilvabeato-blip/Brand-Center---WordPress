/**
 * Anchor bar scrollspy — Brand Center
 *
 * Uses IntersectionObserver to highlight the current in-view section
 * in the right anchor bar. Click → smooth scroll.
 *
 * Expects the page template to output:
 *   <nav class="anchor-bar">
 *     <ul class="anchor-bar-list">
 *       <li><a class="anchor-bar-link" href="#section-id">Label</a></li>
 *       ...
 *     </ul>
 *   </nav>
 *
 * And each anchored section:
 *   <section id="section-id" ...>...</section>
 */
( function () {
    'use strict';

    const anchorBar   = document.querySelector( '.anchor-bar' );
    if ( ! anchorBar ) return;

    const anchorLinks = anchorBar.querySelectorAll( '.anchor-bar-link[href^="#"]' );
    if ( ! anchorLinks.length ) return;

    // Collect target sections.
    const sections = [];
    anchorLinks.forEach( function ( link ) {
        const id = link.getAttribute( 'href' ).slice( 1 );
        const el = document.getElementById( id );
        if ( el ) sections.push( { el: el, link: link } );
    } );

    if ( ! sections.length ) return;

    // Smooth scroll on click.
    anchorLinks.forEach( function ( link ) {
        link.addEventListener( 'click', function ( e ) {
            e.preventDefault();
            const id = link.getAttribute( 'href' ).slice( 1 );
            const el = document.getElementById( id );
            if ( el ) {
                el.scrollIntoView( { behavior: 'smooth', block: 'start' } );
            }
        } );
    } );

    // Scrollspy with IntersectionObserver.
    let activeLink = null;

    const observer = new IntersectionObserver(
        function ( entries ) {
            entries.forEach( function ( entry ) {
                if ( entry.isIntersecting ) {
                    const match = sections.find( function ( s ) {
                        return s.el === entry.target;
                    } );
                    if ( match ) {
                        if ( activeLink ) activeLink.classList.remove( 'is-active' );
                        match.link.classList.add( 'is-active' );
                        activeLink = match.link;
                    }
                }
            } );
        },
        {
            root: null,
            // Trigger when section reaches top 30% of viewport.
            rootMargin: '-30% 0px -60% 0px',
            threshold: 0,
        }
    );

    sections.forEach( function ( s ) {
        observer.observe( s.el );
    } );

} )();
