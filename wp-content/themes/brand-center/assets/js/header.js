/**
 * Header JS — Brand Center
 *
 * Handles:
 *  - Profile dropdown: toggle open/close on trigger click; close on outside click
 *  - Mega-menu: open on hover; close on mouse-leave; keyboard accessible via focus
 *  - Mobile menu: open/close overlay; L1 → L2 drill-down; back button; close
 *
 * No dependencies — vanilla ES6+.
 */
( function () {
    'use strict';

    document.addEventListener( 'DOMContentLoaded', function () {

    // -------------------------------------------------------------------------
    // Profile dropdown
    // -------------------------------------------------------------------------

    const profileTrigger  = document.querySelector( '.profile-trigger' );
    const profileDropdown = document.getElementById( 'profile-dropdown' );

    if ( profileTrigger && profileDropdown ) {

        profileTrigger.addEventListener( 'click', function () {
            const isOpen = profileTrigger.getAttribute( 'aria-expanded' ) === 'true';
            profileTrigger.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
            profileDropdown.hidden = isOpen;
        } );

        // Close on outside click.
        document.addEventListener( 'click', function ( e ) {
            if ( ! profileTrigger.contains( e.target ) && ! profileDropdown.contains( e.target ) ) {
                profileTrigger.setAttribute( 'aria-expanded', 'false' );
                profileDropdown.hidden = true;
            }
        } );

        // Close on Escape.
        document.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Escape' && ! profileDropdown.hidden ) {
                profileTrigger.setAttribute( 'aria-expanded', 'false' );
                profileDropdown.hidden = true;
                profileTrigger.focus();
            }
        } );
    }

    // -------------------------------------------------------------------------
    // Mega-menu — hover triggered, click navigates
    // -------------------------------------------------------------------------

    const navItems = document.querySelectorAll( '.site-nav-item.has-dropdown' );
    let hoverCloseTimer = null;

    function closeSingleMegaMenu( item ) {
        const link = item.querySelector( '.site-nav-link' );
        item.classList.remove( 'is-open' );
        if ( link ) link.setAttribute( 'aria-expanded', 'false' );
    }

    function closeAllMegaMenus( except ) {
        navItems.forEach( function ( item ) {
            if ( item === except ) return;
            closeSingleMegaMenu( item );
        } );
    }

    function openMegaMenu( item ) {
        clearTimeout( hoverCloseTimer );
        closeAllMegaMenus( item );
        const link = item.querySelector( '.site-nav-link' );
        item.classList.add( 'is-open' );
        if ( link ) link.setAttribute( 'aria-expanded', 'true' );
    }

    navItems.forEach( function ( item ) {
        const link    = item.querySelector( '.site-nav-link' );
        const megaMenu = item.querySelector( '.mega-menu' );
        if ( ! link || ! megaMenu ) return;

        // Open on hover — clicking the link navigates normally (no preventDefault).
        item.addEventListener( 'mouseenter', function () {
            openMegaMenu( item );
        } );

        // Close on mouse-leave with a brief delay to prevent accidental dismissal
        // when the cursor briefly leaves while moving into the dropdown panel.
        item.addEventListener( 'mouseleave', function () {
            hoverCloseTimer = setTimeout( function () {
                closeSingleMegaMenu( item );
            }, 150 );
        } );

        // Keyboard: open when the L1 link receives focus.
        link.addEventListener( 'focus', function () {
            openMegaMenu( item );
        } );

        // Keyboard: close when focus leaves the entire nav item (link + dropdown).
        item.addEventListener( 'focusout', function ( e ) {
            if ( ! item.contains( e.relatedTarget ) ) {
                closeSingleMegaMenu( item );
            }
        } );
    } );

    // Close all on outside click.
    document.addEventListener( 'click', function ( e ) {
        const siteNav = document.querySelector( '.site-nav' );
        if ( siteNav && ! siteNav.contains( e.target ) ) {
            closeAllMegaMenus( null );
        }
    } );

    // Close all on Escape.
    document.addEventListener( 'keydown', function ( e ) {
        if ( e.key === 'Escape' ) {
            closeAllMegaMenus( null );
        }
    } );

    // -------------------------------------------------------------------------
    // Mobile menu
    // -------------------------------------------------------------------------

    const mobileMenuTrigger = document.querySelector( '.mobile-menu-trigger' );
    const mobileMenu        = document.getElementById( 'mobile-menu' );

    if ( ! mobileMenuTrigger || ! mobileMenu ) return;

    const l1Screen    = mobileMenu.querySelector( '[data-mobile-screen="l1"]' );
    const l2Screens   = mobileMenu.querySelectorAll( '[data-mobile-screen="l2"]' );
    const closeButtons = mobileMenu.querySelectorAll( '.mobile-menu-close' );

    // Open mobile menu.
    mobileMenuTrigger.addEventListener( 'click', function () {
        mobileMenu.hidden = false;
        mobileMenuTrigger.setAttribute( 'aria-expanded', 'true' );
        document.body.style.overflow = 'hidden';
        // Focus first focusable element.
        const firstFocusable = mobileMenu.querySelector( 'button, a' );
        if ( firstFocusable ) firstFocusable.focus();
    } );

    // Close mobile menu.
    function closeMobileMenu() {
        mobileMenu.hidden = true;
        mobileMenuTrigger.setAttribute( 'aria-expanded', 'false' );
        document.body.style.overflow = '';
        // Reset to L1 screen.
        showL1Screen();
        mobileMenuTrigger.focus();
    }

    closeButtons.forEach( function ( btn ) {
        btn.addEventListener( 'click', closeMobileMenu );
    } );

    // Close on Escape.
    document.addEventListener( 'keydown', function ( e ) {
        if ( e.key === 'Escape' && ! mobileMenu.hidden ) {
            closeMobileMenu();
        }
    } );

    // Drill-down: L1 item button → show corresponding L2 screen.
    const l1Buttons = mobileMenu.querySelectorAll( '.mobile-menu-l1-link[data-l1-id]' );
    l1Buttons.forEach( function ( btn ) {
        btn.addEventListener( 'click', function () {
            const l1Id   = btn.getAttribute( 'data-l1-id' );
            const target = mobileMenu.querySelector( '[data-for-l1="' + l1Id + '"]' );
            if ( target ) {
                if ( l1Screen ) l1Screen.hidden = true;
                target.hidden = false;
                const firstFocusable = target.querySelector( 'button, a' );
                if ( firstFocusable ) firstFocusable.focus();
            }
        } );
    } );

    // Back button: L2 → L1.
    const backButtons = mobileMenu.querySelectorAll( '.mobile-menu-back' );
    backButtons.forEach( function ( btn ) {
        btn.addEventListener( 'click', function () {
            const parentL2 = btn.closest( '[data-mobile-screen="l2"]' );
            if ( parentL2 ) parentL2.hidden = true;
            showL1Screen();
            if ( l1Screen ) {
                const firstFocusable = l1Screen.querySelector( 'button, a' );
                if ( firstFocusable ) firstFocusable.focus();
            }
        } );
    } );

    function showL1Screen() {
        if ( l1Screen ) l1Screen.hidden = false;
        l2Screens.forEach( function ( s ) {
            s.hidden = true;
        } );
    }

    } ); // end DOMContentLoaded

} )();
