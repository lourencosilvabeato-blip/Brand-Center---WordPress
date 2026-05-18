/* global document */
( function () {
    'use strict';

    function initCollectionDetail( wrap ) {
        var dataEl = document.getElementById( 'cd-data' );
        if ( ! dataEl ) { return; }

        var assets = JSON.parse( dataEl.textContent || '[]' );
        var total  = assets.length;
        if ( total === 0 ) { return; }

        var currentIndex = 0;

        var previewImg    = wrap.querySelector( '#cd-preview-img' );
        var assetText     = wrap.querySelector( '#cd-asset-text' );
        var assetDlBtn    = wrap.querySelector( '#cd-asset-dl' );
        var currentEl     = wrap.querySelector( '#cd-current' );
        var prevBtn       = wrap.querySelector( '#cd-prev' );
        var nextBtn       = wrap.querySelector( '#cd-next' );
        var filmstrip     = wrap.querySelector( '#cd-filmstrip' );
        var filmArrowPrev = wrap.querySelector( '.cd-film-arrow--prev' );
        var filmArrowNext = wrap.querySelector( '.cd-film-arrow--next' );
        var thumbBtns     = filmstrip
            ? Array.prototype.slice.call( filmstrip.querySelectorAll( '.cd-film-thumb' ) )
            : [];

        function scrollFilmstripToThumb( index ) {
            if ( ! filmstrip || ! thumbBtns[ index ] ) { return; }
            var thumb      = thumbBtns[ index ];
            var filmLeft   = filmstrip.scrollLeft;
            var filmWidth  = filmstrip.clientWidth;
            var thumbLeft  = thumb.offsetLeft;
            var thumbWidth = thumb.offsetWidth;

            if ( thumbLeft < filmLeft ) {
                filmstrip.scrollTo( { left: thumbLeft, behavior: 'smooth' } );
            } else if ( thumbLeft + thumbWidth > filmLeft + filmWidth ) {
                filmstrip.scrollTo( { left: thumbLeft + thumbWidth - filmWidth, behavior: 'smooth' } );
            }
        }

        function showAsset( index ) {
            if ( index < 0 )      { index = total - 1; }
            if ( index >= total ) { index = 0; }
            currentIndex = index;

            var asset = assets[ index ];

            // Update preview image.
            if ( previewImg ) {
                previewImg.src = asset.src;
                previewImg.alt = asset.alt;
            }

            // Update asset description (full HTML from WYSIWYG).
            if ( assetText ) {
                assetText.innerHTML = asset.desc || '';
            }

            // Update per-asset overlay download button.
            if ( assetDlBtn ) {
                if ( asset.file ) {
                    assetDlBtn.href   = asset.file;
                    assetDlBtn.hidden = false;
                } else {
                    assetDlBtn.hidden = true;
                }
            }

            // Update pagination counter.
            if ( currentEl ) {
                currentEl.textContent = index + 1;
            }

            // Update filmstrip: toggle is-active (controls dark overlay via CSS ::after).
            thumbBtns.forEach( function ( btn, i ) {
                btn.classList.toggle( 'is-active', i === index );
            } );

            scrollFilmstripToThumb( index );
        }

        // Thumbnail clicks.
        thumbBtns.forEach( function ( btn ) {
            btn.addEventListener( 'click', function () {
                showAsset( parseInt( btn.dataset.index, 10 ) );
            } );
        } );

        // Navigation pill prev/next.
        if ( prevBtn ) {
            prevBtn.addEventListener( 'click', function () { showAsset( currentIndex - 1 ); } );
        }
        if ( nextBtn ) {
            nextBtn.addEventListener( 'click', function () { showAsset( currentIndex + 1 ); } );
        }

        // Filmstrip scroll arrows.
        if ( filmArrowPrev ) {
            filmArrowPrev.addEventListener( 'click', function () {
                if ( filmstrip ) { filmstrip.scrollBy( { left: -250, behavior: 'smooth' } ); }
            } );
        }
        if ( filmArrowNext ) {
            filmArrowNext.addEventListener( 'click', function () {
                if ( filmstrip ) { filmstrip.scrollBy( { left: 250, behavior: 'smooth' } ); }
            } );
        }

        // Keyboard navigation — skip when focus is in a form field.
        document.addEventListener( 'keydown', function ( e ) {
            var tag = document.activeElement ? document.activeElement.tagName : '';
            if ( tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' ) { return; }
            if ( e.key === 'ArrowLeft' )  { showAsset( currentIndex - 1 ); }
            if ( e.key === 'ArrowRight' ) { showAsset( currentIndex + 1 ); }
        } );

        showAsset( 0 );
    }

    document.addEventListener( 'DOMContentLoaded', function () {
        var detail = document.querySelector( '.collection-detail' );
        if ( detail ) { initCollectionDetail( detail ); }
    } );
} )();
