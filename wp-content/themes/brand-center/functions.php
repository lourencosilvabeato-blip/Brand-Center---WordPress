<?php
/**
 * Brand Center — Theme Functions
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Includes
// ---------------------------------------------------------------------------

require_once get_template_directory() . '/inc/nav.php';
require_once get_template_directory() . '/inc/sso.php';
require_once get_template_directory() . '/inc/email.php';
require_once get_template_directory() . '/inc/blocks.php';
require_once get_template_directory() . '/inc/channel.php';

// ---------------------------------------------------------------------------
// Remove admin bar from the frontend for all users
// ---------------------------------------------------------------------------

add_filter( 'show_admin_bar', '__return_false' );

// ---------------------------------------------------------------------------
// Theme setup
// ---------------------------------------------------------------------------

add_action( 'after_setup_theme', 'abc_theme_setup' );
function abc_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'editor-styles' );
    add_theme_support(
        'html5',
        array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
    );

    register_nav_menus(
        array(
            'primary' => __( 'Primary Navigation', 'brand-center' ),
        )
    );
}

// ---------------------------------------------------------------------------
// Enqueue styles and scripts
// ---------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', 'abc_enqueue_assets' );
function abc_enqueue_assets() {

    // Google Fonts — Montserrat + IBM Plex Sans
    wp_enqueue_style(
        'abc-google-fonts',
        'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;600&family=Montserrat:wght@600;700&display=swap',
        array(),
        null
    );

    // Main theme stylesheet
    wp_enqueue_style(
        'abc-style',
        get_stylesheet_uri(),
        array( 'abc-google-fonts' ),
        wp_get_theme()->get( 'Version' )
    );
}

// ---------------------------------------------------------------------------
// Block registration
// ---------------------------------------------------------------------------

add_action( 'init', 'abc_register_blocks' );
function abc_register_blocks() {
    // Register each block by pointing to its block.json.
    // Add a new register_block_type() call here for every block.
    $blocks = array(
        // 'abc/block-name' => get_template_directory() . '/blocks/block-name/',
    );

    foreach ( $blocks as $block_name => $block_path ) {
        if ( file_exists( $block_path . 'block.json' ) ) {
            register_block_type( $block_path );
        }
    }
}

// ---------------------------------------------------------------------------
// ACF block registration (placeholder — add blocks below when ready)
// ---------------------------------------------------------------------------

add_action( 'acf/init', 'abc_register_acf_blocks' );
function abc_register_acf_blocks() {
    // acf_register_block_type( array(
    //     'name'            => 'block-name',
    //     'title'           => __( 'Block Title', 'brand-center' ),
    //     'description'     => __( 'Block description.', 'brand-center' ),
    //     'render_template' => get_template_directory() . '/blocks/block-name/block-name.php',
    //     'category'        => 'ascendum',
    //     'icon'            => 'admin-generic',
    //     'keywords'        => array(),
    //     'supports'        => array( 'align' => false ),
    // ) );
}

// ---------------------------------------------------------------------------
// A01 — Authentication: redirect unauthenticated users to login page
// ---------------------------------------------------------------------------

add_action( 'template_redirect', 'abc_auth_redirect' );
function abc_auth_redirect() {
    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();

        // Restrict the invite page to local_admin and administrator only.
        if ( is_page_template( 'page-invite-user.php' ) ) {
            if ( ! in_array( 'local_admin', (array) $user->roles, true )
                && ! in_array( 'administrator', (array) $user->roles, true ) ) {
                wp_safe_redirect( abc_homepage_url() );
                exit;
            }
        }

        // Restrict the change-password page to external_user only.
        if ( is_page_template( 'page-change-password.php' ) ) {
            if ( ! in_array( 'external_user', (array) $user->roles, true ) ) {
                wp_safe_redirect( abc_homepage_url() );
                exit;
            }
        }

        return;
    }

    // Login page: identify by ID so the check is reliable regardless of slug,
    // template assignment, or whether the page is currently the WP front page.
    // is_page($id) is unconditionally safe; slug/template checks are not when
    // the page is the static front page and redirect_canonical is active.
    $login_page = get_page_by_path( 'login' );
    if ( ! $login_page ) {
        $pages      = get_pages( array(
            'meta_key'   => '_wp_page_template',
            'meta_value' => 'page-login.php',
            'number'     => 1,
        ) );
        $login_page = ! empty( $pages ) ? $pages[0] : null;
    }
    if ( $login_page && is_page( $login_page->ID ) ) {
        return;
    }

    // Remaining public pages — slug + template dual check is sufficient here
    // because none of these are ever set as the WP front page.
    if ( is_page_template( 'page-set-password.php' )      || is_page( 'set-password' ) )      { return; }
    if ( is_page_template( 'page-expired-link.php' )      || is_page( 'expired-link' ) )      { return; }
    if ( is_page_template( 'page-password-recovery.php' ) || is_page( 'password-recovery' ) ) { return; }
    if ( is_page_template( 'page-reset-password.php' )    || is_page( 'reset-password' ) )    { return; }

    wp_safe_redirect( abc_login_url() );
    exit;
}

// ---------------------------------------------------------------------------
// A01 — Authentication: handle external login form submission
// ---------------------------------------------------------------------------

add_action( 'admin_post_nopriv_abc_login', 'abc_handle_login' );
function abc_handle_login() {
    $login_url = abc_login_url();

    // Verify nonce.
    $nonce = isset( $_POST['abc_login_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['abc_login_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'abc_login' ) ) {
        wp_safe_redirect( add_query_arg( 'login_error', 'system', $login_url ) );
        exit;
    }

    $email    = isset( $_POST['log'] ) ? sanitize_email( wp_unslash( $_POST['log'] ) ) : '';
    $password = isset( $_POST['pwd'] ) ? wp_unslash( $_POST['pwd'] ) : '';
    $remember = ! empty( $_POST['rememberme'] );

    // Required field check.
    if ( '' === $email || '' === $password ) {
        wp_safe_redirect( add_query_arg( 'login_error', 'required', $login_url ) );
        exit;
    }

    // Email format check.
    if ( ! is_email( $email ) ) {
        wp_safe_redirect( add_query_arg( 'login_error', 'invalid_email', $login_url ) );
        exit;
    }

    // Resolve username from email address.
    $user = get_user_by( 'email', $email );
    if ( ! $user ) {
        // Do not distinguish "email not found" from "wrong password" to prevent enumeration.
        wp_safe_redirect( add_query_arg( 'login_error', 'invalid_credentials', $login_url ) );
        exit;
    }

    $result = wp_signon(
        array(
            'user_login'    => $user->user_login,
            'user_password' => $password,
            'remember'      => $remember,
        ),
        is_ssl()
    );

    if ( is_wp_error( $result ) ) {
        wp_safe_redirect( add_query_arg( 'login_error', 'invalid_credentials', $login_url ) );
        exit;
    }

    wp_safe_redirect( abc_homepage_url() );
    exit;
}

// ---------------------------------------------------------------------------
// Helper: resolve the Homepage permalink regardless of WP front-page setting
// ---------------------------------------------------------------------------

function abc_homepage_url() {
    static $url = null;
    if ( null === $url ) {
        $page = get_page_by_path( 'homepage' );
        $url  = $page ? get_permalink( $page->ID ) : site_url( '/homepage/' );
    }
    return $url;
}

// ---------------------------------------------------------------------------
// Helper: resolve the Login page canonical permalink.
// Uses get_permalink() so the URL is correct whether or not the Login page
// is set as the WordPress front page (avoids redirect_canonical loops).
// ---------------------------------------------------------------------------

function abc_login_url() {
    static $url = null;
    if ( null === $url ) {
        $page = get_page_by_path( 'login' );
        if ( ! $page ) {
            // Fallback: find by template assignment in case the slug was changed.
            $pages = get_pages( array(
                'meta_key'   => '_wp_page_template',
                'meta_value' => 'page-login.php',
                'number'     => 1,
            ) );
            $page = ! empty( $pages ) ? $pages[0] : null;
        }
        $url = $page ? get_permalink( $page->ID ) : home_url( '/login/' );
    }
    return $url;
}

// ---------------------------------------------------------------------------
// A01 — Authentication: redirect to homepage after any successful WP login
// ---------------------------------------------------------------------------

add_filter( 'login_redirect', 'abc_login_redirect', 10, 3 );
function abc_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
    if ( $user && ! is_wp_error( $user ) ) {
        return abc_homepage_url();
    }
    return $redirect_to;
}

// ---------------------------------------------------------------------------
// A06 — Logout: redirect all users to the custom login page after logout
// ---------------------------------------------------------------------------

add_filter( 'logout_redirect', 'abc_logout_redirect', 10, 3 );
function abc_logout_redirect( $redirect_to, $requested_redirect_to, $user ) {
    // WordPress's built-in logout (wp_logout()) handles both session destruction
    // and auth-cookie clearing before this filter runs.
    //
    // For SSO users (internal_user, local_admin, administrator): only the
    // WordPress session is ended. The O365/Microsoft session is NOT touched —
    // the user remains signed into Microsoft 365 apps.
    //
    // For external users: immediate session end and redirect to login.
    return abc_login_url();
}

/**
 * Returns the nonce'd logout URL pointing back to the brand-center login page.
 * Use this in all header/dropdown/mobile-menu templates instead of wp_logout_url().
 *
 * @return string Logout URL with nonce.
 */
function abc_get_logout_url() {
    return wp_logout_url( abc_login_url() );
}

// ---------------------------------------------------------------------------
// A02 — Invite: send invitation to a new external user
// ---------------------------------------------------------------------------

add_action( 'admin_post_abc_invite_user', 'abc_handle_invite_user' );
function abc_handle_invite_user() {
    $invite_url = home_url( '/invite-user/' );

    // Nonce verification.
    $nonce = isset( $_POST['abc_invite_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['abc_invite_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'abc_invite_user' ) ) {
        wp_safe_redirect( add_query_arg( 'invite_error', 'system', $invite_url ) );
        exit;
    }

    // Role gate — local_admin or administrator only.
    $current_user = wp_get_current_user();
    if ( ! in_array( 'local_admin', (array) $current_user->roles, true )
        && ! in_array( 'administrator', (array) $current_user->roles, true ) ) {
        wp_safe_redirect( abc_homepage_url() );
        exit;
    }

    $email = isset( $_POST['invite_email'] ) ? sanitize_email( wp_unslash( $_POST['invite_email'] ) ) : '';

    if ( '' === $email ) {
        wp_safe_redirect( add_query_arg( 'invite_error', 'required', $invite_url ) );
        exit;
    }

    if ( ! is_email( $email ) ) {
        wp_safe_redirect( add_query_arg( 'invite_error', 'invalid_email', $invite_url ) );
        exit;
    }

    // Duplicate check: pending invitations (run before active-user check because
    // pending users are real WP users and would otherwise surface as 'user_exists').
    $pending_users = get_users( array(
        'meta_key'   => 'abc_invite_pending',
        'meta_value' => '1',
        'fields'     => 'ID',
        'number'     => -1,
    ) );
    foreach ( $pending_users as $uid ) {
        $pending_email = get_user_meta( (int) $uid, 'abc_invite_email', true );
        if ( strtolower( $pending_email ) === strtolower( $email ) ) {
            wp_safe_redirect( add_query_arg( 'invite_error', 'already_invited', $invite_url ) );
            exit;
        }
    }

    // Duplicate check: active (non-pending) users.
    $existing = get_user_by( 'email', $email );
    if ( $existing && '1' !== get_user_meta( $existing->ID, 'abc_invite_pending', true ) ) {
        wp_safe_redirect( add_query_arg( 'invite_error', 'user_exists', $invite_url ) );
        exit;
    }

    // Build a unique username from the email local-part.
    $base_username = sanitize_user( strstr( $email, '@', true ), true );
    $base_username = $base_username ?: 'user';
    $username      = $base_username;
    $suffix        = 1;
    while ( username_exists( $username ) ) {
        $username = $base_username . $suffix;
        $suffix++;
    }

    $user_id = wp_create_user( $username, wp_generate_password( 32, true, true ), $email );
    if ( is_wp_error( $user_id ) ) {
        wp_safe_redirect( add_query_arg( 'invite_error', 'system', $invite_url ) );
        exit;
    }

    $new_user = new WP_User( $user_id );
    $new_user->set_role( 'external_user' );

    $token   = wp_generate_password( 64, false );
    $expires = time() + DAY_IN_SECONDS;

    update_user_meta( $user_id, 'abc_invite_pending', '1' );
    update_user_meta( $user_id, 'abc_invite_email',   $email );
    update_user_meta( $user_id, 'abc_invite_token',   wp_hash( $token ) );
    update_user_meta( $user_id, 'abc_invite_expires', $expires );
    update_user_meta( $user_id, 'abc_invite_by',      get_current_user_id() );

    abc_send_invite_email( $email, $token );

    wp_safe_redirect( add_query_arg( 'invite_success', '1', $invite_url ) );
    exit;
}

// ---------------------------------------------------------------------------
// A02 — Invite: cancel a pending invitation
// ---------------------------------------------------------------------------

add_action( 'admin_post_abc_cancel_invite', 'abc_handle_cancel_invite' );
function abc_handle_cancel_invite() {
    $invite_url = home_url( '/invite-user/' );

    $nonce = isset( $_POST['abc_cancel_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['abc_cancel_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'abc_cancel_invite' ) ) {
        wp_safe_redirect( $invite_url );
        exit;
    }

    $current_user = wp_get_current_user();
    if ( ! in_array( 'local_admin', (array) $current_user->roles, true )
        && ! in_array( 'administrator', (array) $current_user->roles, true ) ) {
        wp_safe_redirect( abc_homepage_url() );
        exit;
    }

    $invite_user_id = isset( $_POST['invite_user_id'] ) ? (int) wp_unslash( $_POST['invite_user_id'] ) : 0;
    if ( ! $invite_user_id ) {
        wp_safe_redirect( $invite_url );
        exit;
    }

    if ( '1' !== get_user_meta( $invite_user_id, 'abc_invite_pending', true ) ) {
        wp_safe_redirect( $invite_url );
        exit;
    }

    require_once ABSPATH . 'wp-admin/includes/user.php';
    wp_delete_user( $invite_user_id );

    wp_safe_redirect( add_query_arg( 'cancelled', '1', $invite_url ) );
    exit;
}

// ---------------------------------------------------------------------------
// A02 — Set password: invitee activates their account
// ---------------------------------------------------------------------------

add_action( 'admin_post_nopriv_abc_set_password', 'abc_handle_set_password' );
function abc_handle_set_password() {
    $expired_url = home_url( '/expired-link/' );
    $set_pw_url  = home_url( '/set-password/' );

    $token = isset( $_POST['abc_token'] ) ? sanitize_text_field( wp_unslash( $_POST['abc_token'] ) ) : '';
    $nonce = isset( $_POST['abc_set_password_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['abc_set_password_nonce'] ) ) : '';

    if ( '' === $token || ! wp_verify_nonce( $nonce, 'abc_set_password_' . $token ) ) {
        wp_safe_redirect( $expired_url );
        exit;
    }

    // Resolve user by token hash.
    $token_hash = wp_hash( $token );
    $users      = get_users( array(
        'meta_key'   => 'abc_invite_token',
        'meta_value' => $token_hash,
        'number'     => 1,
        'fields'     => 'ID',
    ) );

    if ( empty( $users ) ) {
        wp_safe_redirect( $expired_url );
        exit;
    }

    $user_id = (int) $users[0];
    $expires = (int) get_user_meta( $user_id, 'abc_invite_expires', true );

    if ( time() > $expires ) {
        wp_safe_redirect( $expired_url );
        exit;
    }

    $password  = isset( $_POST['password'] )  ? wp_unslash( $_POST['password'] )  : '';
    $password2 = isset( $_POST['password2'] ) ? wp_unslash( $_POST['password2'] ) : '';

    $redirect_base = add_query_arg( 'token', rawurlencode( $token ), $set_pw_url );

    if ( '' === $password || '' === $password2 ) {
        wp_safe_redirect( add_query_arg( 'pw_error', 'required', $redirect_base ) );
        exit;
    }

    if ( $password !== $password2 ) {
        wp_safe_redirect( add_query_arg( 'pw_error', 'mismatch', $redirect_base ) );
        exit;
    }

    if ( ! abc_password_is_strong( $password ) ) {
        wp_safe_redirect( add_query_arg( 'pw_error', 'weak', $redirect_base ) );
        exit;
    }

    // Activate the account.
    wp_set_password( $password, $user_id );
    delete_user_meta( $user_id, 'abc_invite_pending' );
    delete_user_meta( $user_id, 'abc_invite_token' );
    delete_user_meta( $user_id, 'abc_invite_expires' );
    delete_user_meta( $user_id, 'abc_invite_email' );
    delete_user_meta( $user_id, 'abc_invite_by' );

    wp_set_auth_cookie( $user_id, false );
    wp_safe_redirect( abc_homepage_url() );
    exit;
}

/**
 * Returns true if the password meets strength requirements:
 * min 8 chars, one uppercase letter, one digit, one special character.
 *
 * @param  string $password
 * @return bool
 */
function abc_password_is_strong( $password ) {
    if ( strlen( $password ) < 8 ) {
        return false;
    }
    if ( ! preg_match( '/[A-Z]/', $password ) ) {
        return false;
    }
    if ( ! preg_match( '/[0-9]/', $password ) ) {
        return false;
    }
    if ( ! preg_match( '/[^a-zA-Z0-9]/', $password ) ) {
        return false;
    }
    return true;
}

// ---------------------------------------------------------------------------
// NAV — Enqueue sidebar, breadcrumb, anchor-bar, and header assets
// Loaded on all authenticated pages (auth-shell pages use standalone HTML
// and don't render these elements, so the CSS is harmless there).
// ---------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', 'abc_enqueue_nav_assets' );
function abc_enqueue_nav_assets() {
    $ver = wp_get_theme()->get( 'Version' );

    wp_enqueue_style(
        'abc-header',
        get_template_directory_uri() . '/assets/css/header.css',
        array( 'abc-style' ),
        $ver
    );

    wp_enqueue_style(
        'abc-sidebar',
        get_template_directory_uri() . '/assets/css/sidebar.css',
        array( 'abc-style' ),
        $ver
    );

    wp_enqueue_style(
        'abc-breadcrumb',
        get_template_directory_uri() . '/assets/css/breadcrumb.css',
        array( 'abc-style' ),
        $ver
    );

    wp_enqueue_script(
        'abc-header',
        get_template_directory_uri() . '/assets/js/header.js',
        array(),
        $ver,
        true
    );

    wp_enqueue_script(
        'abc-anchor-bar',
        get_template_directory_uri() . '/assets/js/anchor-bar.js',
        array(),
        $ver,
        true
    );
}

// ---------------------------------------------------------------------------
// A01 — Login page: enqueue page-specific stylesheet
// ---------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', 'abc_enqueue_login_assets' );
function abc_enqueue_login_assets() {
    if ( ! is_page_template( 'page-login.php' ) ) {
        return;
    }
    wp_enqueue_style(
        'abc-login',
        get_template_directory_uri() . '/assets/css/login.css',
        array( 'abc-style' ),
        wp_get_theme()->get( 'Version' )
    );
}

// ---------------------------------------------------------------------------
// A02 — Invite page stylesheet
// ---------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', 'abc_enqueue_invite_assets' );
function abc_enqueue_invite_assets() {
    if ( ! is_page_template( 'page-invite-user.php' ) ) {
        return;
    }
    wp_enqueue_style(
        'abc-invite',
        get_template_directory_uri() . '/assets/css/invite.css',
        array( 'abc-style' ),
        wp_get_theme()->get( 'Version' )
    );
}

// ---------------------------------------------------------------------------
// A02 — Set password / expired link stylesheet
// A04 — Reset password reuses the same stylesheet
// ---------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', 'abc_enqueue_set_password_assets' );
function abc_enqueue_set_password_assets() {
    if ( ! is_page_template( 'page-set-password.php' )
        && ! is_page_template( 'page-expired-link.php' )
        && ! is_page_template( 'page-reset-password.php' )
        && ! is_page_template( 'page-password-recovery.php' ) ) {
        return;
    }
    wp_enqueue_style(
        'abc-set-password',
        get_template_directory_uri() . '/assets/css/set-password.css',
        array( 'abc-style' ),
        wp_get_theme()->get( 'Version' )
    );
}

// ---------------------------------------------------------------------------
// A01 — Login page: ACF field group
// ---------------------------------------------------------------------------

add_action( 'acf/init', 'abc_register_login_acf_fields' );
function abc_register_login_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( array(
        'key'    => 'group_login_page',
        'title'  => 'Login Page Settings',
        'fields' => array(

            // Background image (required)
            array(
                'key'           => 'field_login_image',
                'label'         => 'Background Image',
                'name'          => 'login_image',
                'type'          => 'image',
                'required'      => 1,
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ),

            // Headline area
            array(
                'key'   => 'field_login_title',
                'label' => 'Title',
                'name'  => 'login_title',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_login_introduction',
                'label' => 'Introduction',
                'name'  => 'login_introduction',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_login_subtitle',
                'label' => 'Subtitle',
                'name'  => 'login_subtitle',
                'type'  => 'text',
            ),

            // Institutional link (footer, required)
            array(
                'key'      => 'field_login_institutional_label',
                'label'    => 'Institutional Link — Label',
                'name'     => 'login_institutional_label',
                'type'     => 'text',
                'required' => 1,
            ),
            array(
                'key'      => 'field_login_institutional_url',
                'label'    => 'Institutional Link — URL',
                'name'     => 'login_institutional_url',
                'type'     => 'url',
                'required' => 1,
            ),

            // Social links — 4 fixed slots (ACF Free compatible, no repeater)
            array(
                'key'           => 'field_social_icon_1',
                'label'         => 'Social 1 — Icon',
                'name'          => 'social_icon_1',
                'type'          => 'image',
                'return_format' => 'array',
                'instructions'  => 'e.g. Instagram icon (24×24 px PNG or SVG)',
            ),
            array(
                'key'  => 'field_social_url_1',
                'label' => 'Social 1 — URL',
                'name'  => 'social_url_1',
                'type'  => 'url',
            ),
            array(
                'key'           => 'field_social_icon_2',
                'label'         => 'Social 2 — Icon',
                'name'          => 'social_icon_2',
                'type'          => 'image',
                'return_format' => 'array',
                'instructions'  => 'e.g. Facebook icon',
            ),
            array(
                'key'  => 'field_social_url_2',
                'label' => 'Social 2 — URL',
                'name'  => 'social_url_2',
                'type'  => 'url',
            ),
            array(
                'key'           => 'field_social_icon_3',
                'label'         => 'Social 3 — Icon',
                'name'          => 'social_icon_3',
                'type'          => 'image',
                'return_format' => 'array',
                'instructions'  => 'e.g. LinkedIn icon',
            ),
            array(
                'key'  => 'field_social_url_3',
                'label' => 'Social 3 — URL',
                'name'  => 'social_url_3',
                'type'  => 'url',
            ),
            array(
                'key'           => 'field_social_icon_4',
                'label'         => 'Social 4 — Icon',
                'name'          => 'social_icon_4',
                'type'          => 'image',
                'return_format' => 'array',
                'instructions'  => 'e.g. YouTube icon',
            ),
            array(
                'key'  => 'field_social_url_4',
                'label' => 'Social 4 — URL',
                'name'  => 'social_url_4',
                'type'  => 'url',
            ),

            // Legal links — 3 fixed slots (ACF Free compatible, no repeater)
            array(
                'key'         => 'field_legal_label_1',
                'label'       => 'Legal 1 — Label',
                'name'        => 'legal_label_1',
                'type'        => 'text',
                'placeholder' => 'Privacy Policy',
            ),
            array(
                'key'  => 'field_legal_url_1',
                'label' => 'Legal 1 — URL',
                'name'  => 'legal_url_1',
                'type'  => 'url',
            ),
            array(
                'key'         => 'field_legal_label_2',
                'label'       => 'Legal 2 — Label',
                'name'        => 'legal_label_2',
                'type'        => 'text',
                'placeholder' => 'Terms of Use',
            ),
            array(
                'key'  => 'field_legal_url_2',
                'label' => 'Legal 2 — URL',
                'name'  => 'legal_url_2',
                'type'  => 'url',
            ),
            array(
                'key'         => 'field_legal_label_3',
                'label'       => 'Legal 3 — Label',
                'name'        => 'legal_label_3',
                'type'        => 'text',
                'placeholder' => 'Leave blank if unused',
            ),
            array(
                'key'  => 'field_legal_url_3',
                'label' => 'Legal 3 — URL',
                'name'  => 'legal_url_3',
                'type'  => 'url',
            ),

        ),
        'location' => array(
            array(
                array(
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'page-login.php',
                ),
            ),
            array(
                array(
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'page-set-password.php',
                ),
            ),
            array(
                array(
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'page-expired-link.php',
                ),
            ),
            array(
                array(
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'page-reset-password.php',
                ),
            ),
            array(
                array(
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'page-password-recovery.php',
                ),
            ),
        ),
    ) );
}

// ---------------------------------------------------------------------------
// A05 — Change password: handle form submission
// ---------------------------------------------------------------------------

add_action( 'admin_post_abc_change_password', 'abc_handle_change_password' );
function abc_handle_change_password() {
    $change_url = home_url( '/change-password/' );

    // Role gate — external_user only.
    $current_user = wp_get_current_user();
    if ( ! in_array( 'external_user', (array) $current_user->roles, true ) ) {
        wp_safe_redirect( abc_homepage_url() );
        exit;
    }

    // Nonce verification.
    $nonce = isset( $_POST['abc_change_password_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['abc_change_password_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'abc_change_password' ) ) {
        wp_safe_redirect( add_query_arg( 'cp_error', 'system', $change_url ) );
        exit;
    }

    $current_password = isset( $_POST['current_password'] ) ? wp_unslash( $_POST['current_password'] ) : '';
    $password         = isset( $_POST['password'] )         ? wp_unslash( $_POST['password'] )         : '';
    $password2        = isset( $_POST['password2'] )        ? wp_unslash( $_POST['password2'] )        : '';

    // Required fields check.
    if ( '' === $current_password || '' === $password || '' === $password2 ) {
        wp_safe_redirect( add_query_arg( 'cp_error', 'required', $change_url ) );
        exit;
    }

    // Verify current password.
    $user = get_userdata( $current_user->ID );
    if ( ! $user || ! wp_check_password( $current_password, $user->user_pass, $user->ID ) ) {
        wp_safe_redirect( add_query_arg( 'cp_error', 'wrong_current', $change_url ) );
        exit;
    }

    // New password strength.
    if ( ! abc_password_is_strong( $password ) ) {
        wp_safe_redirect( add_query_arg( 'cp_error', 'weak', $change_url ) );
        exit;
    }

    // Passwords must match.
    if ( $password !== $password2 ) {
        wp_safe_redirect( add_query_arg( 'cp_error', 'mismatch', $change_url ) );
        exit;
    }

    // Update password and re-establish session (wp_set_password invalidates all sessions).
    wp_set_password( $password, $current_user->ID );
    wp_set_auth_cookie( $current_user->ID, false );

    wp_safe_redirect( abc_homepage_url() );
    exit;
}

// ---------------------------------------------------------------------------
// A05 — Change password page stylesheet
// ---------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', 'abc_enqueue_change_password_assets' );
function abc_enqueue_change_password_assets() {
    if ( ! is_page_template( 'page-change-password.php' ) ) {
        return;
    }
    wp_enqueue_style(
        'abc-change-password',
        get_template_directory_uri() . '/assets/css/change-password.css',
        array( 'abc-style' ),
        wp_get_theme()->get( 'Version' )
    );
}

// ---------------------------------------------------------------------------
// A03 — Password recovery: handle email submission
// ---------------------------------------------------------------------------

add_action( 'admin_post_nopriv_abc_password_recovery', 'abc_handle_password_recovery' );
function abc_handle_password_recovery() {
    $recovery_url = home_url( '/password-recovery/' );

    // Nonce verification.
    $nonce = isset( $_POST['abc_recovery_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['abc_recovery_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'abc_password_recovery' ) ) {
        wp_safe_redirect( add_query_arg( 'pr_error', 'system', $recovery_url ) );
        exit;
    }

    $email = isset( $_POST['recovery_email'] ) ? sanitize_email( wp_unslash( $_POST['recovery_email'] ) ) : '';

    if ( '' === $email ) {
        wp_safe_redirect( add_query_arg( 'pr_error', 'required', $recovery_url ) );
        exit;
    }

    if ( ! is_email( $email ) ) {
        wp_safe_redirect( add_query_arg( 'pr_error', 'invalid_email', $recovery_url ) );
        exit;
    }

    // Only send a reset link if the email belongs to an active external_user.
    // Always redirect to ?sent=1 regardless — prevents email enumeration.
    $user = get_user_by( 'email', $email );
    if ( $user && in_array( 'external_user', (array) $user->roles, true ) ) {
        $token   = wp_generate_password( 64, false );
        $expires = time() + DAY_IN_SECONDS;

        update_user_meta( $user->ID, 'abc_recovery_token',   wp_hash( $token ) );
        update_user_meta( $user->ID, 'abc_recovery_expires', $expires );

        abc_send_recovery_email( $email, $token );
    }

    wp_safe_redirect( add_query_arg( 'sent', '1', $recovery_url ) );
    exit;
}

// ---------------------------------------------------------------------------
// A04 — Reset password: handle form submission (shared with A03 flow)
// ---------------------------------------------------------------------------

add_action( 'admin_post_nopriv_abc_reset_password', 'abc_handle_reset_password' );
function abc_handle_reset_password() {
    $recovery_url = home_url( '/password-recovery/' );

    $token = isset( $_POST['abc_token'] ) ? sanitize_text_field( wp_unslash( $_POST['abc_token'] ) ) : '';
    $nonce = isset( $_POST['abc_reset_password_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['abc_reset_password_nonce'] ) ) : '';

    if ( '' === $token || ! wp_verify_nonce( $nonce, 'abc_reset_password_' . $token ) ) {
        wp_safe_redirect( add_query_arg( 'expired', '1', $recovery_url ) );
        exit;
    }

    // Resolve user by recovery token hash.
    $token_hash = wp_hash( $token );
    $users      = get_users( array(
        'meta_key'   => 'abc_recovery_token',
        'meta_value' => $token_hash,
        'number'     => 1,
        'fields'     => 'ID',
    ) );

    if ( empty( $users ) ) {
        wp_safe_redirect( add_query_arg( 'expired', '1', $recovery_url ) );
        exit;
    }

    $user_id = (int) $users[0];
    $expires = (int) get_user_meta( $user_id, 'abc_recovery_expires', true );

    if ( time() > $expires ) {
        wp_safe_redirect( add_query_arg( 'expired', '1', $recovery_url ) );
        exit;
    }

    $password  = isset( $_POST['password'] )  ? wp_unslash( $_POST['password'] )  : '';
    $password2 = isset( $_POST['password2'] ) ? wp_unslash( $_POST['password2'] ) : '';

    $redirect_base = add_query_arg( 'token', rawurlencode( $token ), home_url( '/reset-password/' ) );

    if ( '' === $password || '' === $password2 ) {
        wp_safe_redirect( add_query_arg( 'pw_error', 'required', $redirect_base ) );
        exit;
    }

    if ( $password !== $password2 ) {
        wp_safe_redirect( add_query_arg( 'pw_error', 'mismatch', $redirect_base ) );
        exit;
    }

    if ( ! abc_password_is_strong( $password ) ) {
        wp_safe_redirect( add_query_arg( 'pw_error', 'weak', $redirect_base ) );
        exit;
    }

    // Set the new password and clear recovery meta.
    wp_set_password( $password, $user_id );
    delete_user_meta( $user_id, 'abc_recovery_token' );
    delete_user_meta( $user_id, 'abc_recovery_expires' );

    // Log the user in immediately.
    wp_set_auth_cookie( $user_id, false );
    wp_safe_redirect( abc_homepage_url() );
    exit;
}

// ---------------------------------------------------------------------------
// A04 — Admin-initiated password reset: row action in Users list
// ---------------------------------------------------------------------------

add_filter( 'user_row_actions', 'abc_add_user_reset_password_action', 10, 2 );
function abc_add_user_reset_password_action( $actions, $user ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        return $actions;
    }
    // Only available for external users.
    if ( ! in_array( 'external_user', (array) $user->roles, true ) ) {
        return $actions;
    }
    $url = wp_nonce_url(
        add_query_arg(
            array(
                'action'  => 'abc_admin_reset_password',
                'user_id' => $user->ID,
            ),
            admin_url( 'admin-post.php' )
        ),
        'abc_admin_reset_password_' . $user->ID,
        'abc_reset_nonce'
    );
    $actions['abc_reset_password'] = '<a href="' . esc_url( $url ) . '">'
        . esc_html__( 'Reset Password', 'brand-center' )
        . '</a>';
    return $actions;
}

// ---------------------------------------------------------------------------
// A04 — Admin-initiated password reset: form handler
// ---------------------------------------------------------------------------

add_action( 'admin_post_abc_admin_reset_password', 'abc_handle_admin_password_reset' );
function abc_handle_admin_password_reset() {
    // Must be an administrator.
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Unauthorized.', 'brand-center' ), '', array( 'response' => 403 ) );
    }

    $user_id = isset( $_REQUEST['user_id'] ) ? (int) $_REQUEST['user_id'] : 0;

    check_admin_referer( 'abc_admin_reset_password_' . $user_id, 'abc_reset_nonce' );

    if ( ! $user_id ) {
        wp_die( esc_html__( 'Invalid user ID.', 'brand-center' ), '', array( 'response' => 400 ) );
    }

    $user = get_userdata( $user_id );
    if ( ! $user || ! in_array( 'external_user', (array) $user->roles, true ) ) {
        wp_die(
            esc_html__( 'Password reset is only available for external users.', 'brand-center' ),
            '',
            array( 'response' => 400 )
        );
    }

    // Generate a 64-character token, store the hash.
    $token   = wp_generate_password( 64, false );
    $expires = time() + DAY_IN_SECONDS;

    update_user_meta( $user_id, 'abc_recovery_token',   wp_hash( $token ) );
    update_user_meta( $user_id, 'abc_recovery_expires', $expires );

    // Send Email #3.
    abc_send_admin_reset_email( $user->user_email, $token );

    // Redirect back to user-edit with a success flag.
    wp_safe_redirect(
        add_query_arg(
            array(
                'user_id'        => $user_id,
                'abc_reset_sent' => '1',
            ),
            admin_url( 'user-edit.php' )
        )
    );
    exit;
}

// ---------------------------------------------------------------------------
// A04 — Admin-initiated password reset: success notice in user-edit screen
// ---------------------------------------------------------------------------

add_action( 'admin_notices', 'abc_admin_reset_password_notice' );
function abc_admin_reset_password_notice() {
    if ( empty( $_GET['abc_reset_sent'] ) || '1' !== $_GET['abc_reset_sent'] ) {
        return;
    }
    $screen = get_current_screen();
    if ( ! $screen || 'user-edit' !== $screen->id ) {
        return;
    }
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e( 'Password reset email sent successfully.', 'brand-center' ); ?></p>
    </div>
    <?php
}

// ---------------------------------------------------------------------------
// NAV — Page anchors ACF field group (right anchor bar)
// ACF Free compatible: single textarea, one anchor per line (Label|section-id)
// ---------------------------------------------------------------------------

add_action( 'acf/init', 'abc_register_page_anchors_acf_fields' );
function abc_register_page_anchors_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( array(
        'key'      => 'group_page_anchors',
        'title'    => 'Page Anchors (Right sidebar)',
        'fields'   => array(
            array(
                'key'          => 'field_page_anchors',
                'label'        => 'Anchor sections',
                'name'         => 'page_anchors',
                'type'         => 'textarea',
                'instructions' => "One anchor per line.\nFormat: Section Label|section-id\nExample: Introduction|section-intro\nLeave blank to hide the anchor bar.",
                'placeholder'  => "Introduction|section-intro\nHow to use|section-usage",
                'rows'         => 6,
                'new_lines'    => '',   // preserve raw line breaks
                'required'     => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'page',
                ),
                array(
                    'param'    => 'page_template',
                    'operator' => '!=',
                    'value'    => 'page-generic-content.php',
                ),
            ),
        ),
        'position'      => 'side',
        'menu_order'    => 10,
        'label_placement' => 'top',
    ) );
}

// ---------------------------------------------------------------------------
// A05 — Change password page: ACF field group
// ---------------------------------------------------------------------------

add_action( 'acf/init', 'abc_register_change_password_acf_fields' );
function abc_register_change_password_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( array(
        'key'    => 'group_change_password_page',
        'title'  => 'Change Password Page Settings',
        'fields' => array(
            array(
                'key'         => 'field_cp_title',
                'label'       => 'Title',
                'name'        => 'cp_title',
                'type'        => 'text',
                'placeholder' => 'Change password',
            ),
            array(
                'key'         => 'field_cp_introduction',
                'label'       => 'Introduction',
                'name'        => 'cp_introduction',
                'type'        => 'text',
                'placeholder' => 'Create a new password to secure your account.',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'page-change-password.php',
                ),
            ),
        ),
    ) );
}

// ---------------------------------------------------------------------------
// Helpers — build social / legal link arrays from individual ACF fields
// (ACF Free compatible replacement for repeater fields)
// ---------------------------------------------------------------------------

/**
 * Returns an array of social link entries from individual ACF fields.
 * Each entry: [ 'social_icon' => array, 'social_url' => string ]
 *
 * @return array
 */
function abc_get_social_links() {
    $links = array();
    for ( $i = 1; $i <= 4; $i++ ) {
        $icon = get_field( 'social_icon_' . $i );
        $url  = get_field( 'social_url_' . $i );
        if ( $icon && $url ) {
            $links[] = array(
                'social_icon' => $icon,
                'social_url'  => $url,
            );
        }
    }
    return $links;
}

/**
 * Returns an array of legal link entries from individual ACF fields.
 * Each entry: [ 'legal_label' => string, 'legal_url' => string ]
 *
 * @return array
 */
function abc_get_legal_links() {
    $links = array();
    for ( $i = 1; $i <= 3; $i++ ) {
        $label = get_field( 'legal_label_' . $i );
        $url   = get_field( 'legal_url_' . $i );
        if ( $label && $url ) {
            $links[] = array(
                'legal_label' => $label,
                'legal_url'   => $url,
            );
        }
    }
    return $links;
}

// ---------------------------------------------------------------------------
// Footer settings: page-ID helper
// ---------------------------------------------------------------------------

/**
 * Returns the ID of the 'footer-settings' WP page, or null if not found.
 * Result is statically cached for the request.
 *
 * @return int|null
 */
function abc_get_footer_page_id() {
    static $id = null;
    if ( null === $id ) {
        $page = get_page_by_path( 'footer-settings' );
        $id   = $page ? (int) $page->ID : 0;
    }
    return $id ?: null;
}

// ---------------------------------------------------------------------------
// B01 — Homepage: enqueue page-specific stylesheet + script
// ---------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', 'abc_enqueue_homepage_assets' );
function abc_enqueue_homepage_assets() {
    if ( ! is_page_template( 'page-homepage.php' ) ) {
        return;
    }
    $ver = wp_get_theme()->get( 'Version' );

    wp_enqueue_style(
        'abc-homepage',
        get_template_directory_uri() . '/assets/css/homepage.css',
        array( 'abc-style' ),
        $ver
    );

    wp_enqueue_script(
        'abc-homepage',
        get_template_directory_uri() . '/assets/js/homepage.js',
        array(),
        $ver,
        true
    );
}

// ---------------------------------------------------------------------------
// Footer CSS: enqueue globally (footer appears on all authenticated pages)
// ---------------------------------------------------------------------------

// ---------------------------------------------------------------------------
// C02/E02 — Generic Content page: stylesheet + icon-library script
// ---------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', 'abc_enqueue_generic_content_assets' );
function abc_enqueue_generic_content_assets() {
    if ( ! is_page_template( 'page-generic-content.php' ) ) {
        return;
    }
    $ver = wp_get_theme()->get( 'Version' );

    wp_enqueue_style(
        'abc-generic-content',
        get_template_directory_uri() . '/assets/css/generic-content.css',
        array( 'abc-style', 'abc-sidebar' ),
        $ver
    );

    wp_enqueue_script(
        'abc-icon-library',
        get_template_directory_uri() . '/assets/js/icon-library.js',
        array(),
        $ver,
        true
    );
}

// ---------------------------------------------------------------------------
// Footer CSS: enqueue globally (footer appears on all authenticated pages)
// ---------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', 'abc_enqueue_footer_assets' );
function abc_enqueue_footer_assets() {
    wp_enqueue_style(
        'abc-footer',
        get_template_directory_uri() . '/assets/css/footer.css',
        array( 'abc-style' ),
        wp_get_theme()->get( 'Version' )
    );
}

// ---------------------------------------------------------------------------
// NAV — Search filter checkbox on nav menu items (Appearance → Menus)
// Adds a "Show as search filter" checkbox to each menu item in the admin.
// ---------------------------------------------------------------------------

add_action( 'wp_nav_menu_item_custom_fields', 'abc_menu_item_search_filter_field', 10, 4 );
/**
 * Renders the "Show as search filter" checkbox inside each menu item row.
 *
 * @param int     $item_id  Menu item post ID.
 * @param WP_Post $item     Menu item post object.
 * @param int     $depth    Depth in menu hierarchy.
 * @param object  $args     Walker arguments.
 */
function abc_menu_item_search_filter_field( $item_id, $item, $depth, $args ) {
    $checked = get_post_meta( $item_id, '_abc_search_filter', true );
    ?>
    <p class="field-move description description-wide" style="margin-top:6px;">
        <label>
            <input
                type="checkbox"
                name="menu-item-search-filter[<?php echo esc_attr( $item_id ); ?>]"
                value="1"
                <?php checked( $checked, '1' ); ?>
            >
            <?php esc_html_e( 'Show as search filter on homepage', 'brand-center' ); ?>
        </label>
    </p>
    <?php
}

add_action( 'wp_update_nav_menu_item', 'abc_save_menu_item_search_filter', 10, 3 );
/**
 * Saves the search-filter checkbox meta when a menu item is saved.
 *
 * @param int   $menu_id          Nav menu term ID.
 * @param int   $menu_item_db_id  Menu item post ID.
 * @param array $args             Menu item data.
 */
function abc_save_menu_item_search_filter( $menu_id, $menu_item_db_id, $args ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    if ( isset( $_POST['menu-item-search-filter'][ $menu_item_db_id ] ) ) {
        update_post_meta( $menu_item_db_id, '_abc_search_filter', '1' );
    } else {
        delete_post_meta( $menu_item_db_id, '_abc_search_filter' );
    }
}

// ---------------------------------------------------------------------------
// B01 — Homepage: ACF field group
// ---------------------------------------------------------------------------

add_action( 'acf/init', 'abc_register_homepage_acf_fields' );
function abc_register_homepage_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    $fields = array();

    // --- Hero -----------------------------------------------------------------
    $fields[] = array(
        'key'      => 'field_hero_image',
        'label'    => 'Hero Image',
        'name'     => 'hero_image',
        'type'     => 'image',
        'required' => 1,
        'instructions' => 'Full-width background image for the hero section.',
    );
    $fields[] = array(
        'key'         => 'field_hero_headline',
        'label'       => 'Hero Headline',
        'name'        => 'hero_headline',
        'type'        => 'text',
        'required'    => 1,
        'placeholder' => 'The Home of the Brand',
    );
    $fields[] = array(
        'key'          => 'field_hero_intro',
        'label'        => 'Hero Intro (hidden on mobile)',
        'name'         => 'hero_intro',
        'type'         => 'text',
        'instructions' => 'Optional. Displayed below the headline on desktop only.',
    );

    // --- Section 1 ------------------------------------------------------------
    $fields[] = array(
        'key'         => 'field_section1_title',
        'label'       => 'Section 1 — Title',
        'name'        => 'section1_title',
        'type'        => 'text',
        'required'    => 1,
        'placeholder' => 'NEW IN',
    );
    $fields[] = array(
        'key'         => 'field_section1_intro',
        'label'       => 'Section 1 — Intro',
        'name'        => 'section1_intro',
        'type'        => 'text',
        'instructions' => 'Optional introductory text below the section title.',
    );

    // Highlight slots 1–6 (Section 1)
    for ( $i = 1; $i <= 6; $i++ ) {
        $fields[] = array(
            'key'          => "field_h1_image_{$i}",
            'label'        => "Section 1 — Card {$i} Image",
            'name'         => "highlight1_image_{$i}",
            'type'         => 'image',
            'instructions' => "Leave empty to hide slot {$i}.",
        );
        $fields[] = array(
            'key'   => "field_h1_title_{$i}",
            'label' => "Section 1 — Card {$i} Title",
            'name'  => "highlight1_title_{$i}",
            'type'  => 'text',
        );
        $fields[] = array(
            'key'   => "field_h1_url_{$i}",
            'label' => "Section 1 — Card {$i} URL",
            'name'  => "highlight1_url_{$i}",
            'type'  => 'url',
        );
        $fields[] = array(
            'key'          => "field_h1_tab_{$i}",
            'label'        => "Section 1 — Card {$i} Open in new tab",
            'name'         => "highlight1_new_tab_{$i}",
            'type'         => 'true_false',
            'default_value' => 0,
        );
    }

    // --- Section 2 ------------------------------------------------------------
    $fields[] = array(
        'key'         => 'field_section2_title',
        'label'       => 'Section 2 — Title',
        'name'        => 'section2_title',
        'type'        => 'text',
        'required'    => 1,
        'placeholder' => 'QUICK ACCESS',
    );
    $fields[] = array(
        'key'          => 'field_section2_intro',
        'label'        => 'Section 2 — Intro',
        'name'         => 'section2_intro',
        'type'         => 'text',
        'instructions' => 'Optional introductory text below the section title.',
    );

    // Highlight slots 1–6 (Section 2)
    for ( $i = 1; $i <= 6; $i++ ) {
        $fields[] = array(
            'key'          => "field_h2_image_{$i}",
            'label'        => "Section 2 — Card {$i} Image",
            'name'         => "highlight2_image_{$i}",
            'type'         => 'image',
            'instructions' => "Leave empty to hide slot {$i}.",
        );
        $fields[] = array(
            'key'   => "field_h2_title_{$i}",
            'label' => "Section 2 — Card {$i} Title",
            'name'  => "highlight2_title_{$i}",
            'type'  => 'text',
        );
        $fields[] = array(
            'key'   => "field_h2_url_{$i}",
            'label' => "Section 2 — Card {$i} URL",
            'name'  => "highlight2_url_{$i}",
            'type'  => 'url',
        );
        $fields[] = array(
            'key'           => "field_h2_tab_{$i}",
            'label'         => "Section 2 — Card {$i} Open in new tab",
            'name'          => "highlight2_new_tab_{$i}",
            'type'          => 'true_false',
            'default_value' => 0,
        );
    }

    // --- Help buttons — fixed labels, BO-configurable URLs -------------------
    // Labels are hardcoded in the template; only the target URL is editable.
    $fields[] = array(
        'key'          => 'field_help_contact_url',
        'label'        => 'Help Button — Contact Us URL',
        'name'         => 'help_contact_url',
        'type'         => 'url',
        'instructions' => 'URL for the "Contact us" button. Leave blank to use /contact/.',
    );
    $fields[] = array(
        'key'          => 'field_help_nav_tips_url',
        'label'        => 'Help Button — Navigation Tips URL',
        'name'         => 'help_nav_tips_url',
        'type'         => 'url',
        'instructions' => 'URL for the "Navigation Tips" button. Leave blank to use /navigation-tips/.',
    );
    $fields[] = array(
        'key'          => 'field_help_faqs_url',
        'label'        => 'Help Button — FAQs URL',
        'name'         => 'help_faqs_url',
        'type'         => 'url',
        'instructions' => 'URL for the "FAQs" button. Leave blank to use /faqs/.',
    );

    acf_add_local_field_group( array(
        'key'      => 'group_homepage',
        'title'    => 'Homepage Settings',
        'fields'   => $fields,
        'location' => array(
            array(
                array(
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'page-homepage.php',
                ),
            ),
        ),
        'position' => 'normal',
        'style'    => 'default',
    ) );
}

// ---------------------------------------------------------------------------
// Footer settings: ACF field group
// (attached to the 'footer-settings' page by slug)
// ---------------------------------------------------------------------------

add_action( 'acf/init', 'abc_register_footer_acf_fields' );
function abc_register_footer_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    $fields = array();

    $fields[] = array(
        'key'         => 'field_footer_brand_name',
        'label'       => 'Brand Name',
        'name'        => 'footer_brand_name',
        'type'        => 'text',
        'required'    => 1,
        'placeholder' => 'Ascendum Group',
    );
    $fields[] = array(
        'key'          => 'field_footer_institutional_url',
        'label'        => 'Institutional Link URL',
        'name'         => 'footer_institutional_url',
        'type'         => 'url',
        'instructions' => 'Optional. Brand name becomes a link when set.',
    );

    // Social icon slots 1–4
    for ( $i = 1; $i <= 4; $i++ ) {
        $fields[] = array(
            'key'           => "field_footer_social_icon_{$i}",
            'label'         => "Social Icon {$i}",
            'name'          => "footer_social_icon_{$i}",
            'type'          => 'image',
            'return_format' => 'array',
            'instructions'  => "Upload social icon image (32×32px recommended).",
        );
        $fields[] = array(
            'key'   => "field_footer_social_url_{$i}",
            'label' => "Social Link {$i} URL",
            'name'  => "footer_social_url_{$i}",
            'type'  => 'url',
        );
    }

    // Legal link slots 1–3
    for ( $i = 1; $i <= 3; $i++ ) {
        $fields[] = array(
            'key'   => "field_footer_legal_label_{$i}",
            'label' => "Legal Link {$i} Label",
            'name'  => "footer_legal_label_{$i}",
            'type'  => 'text',
        );
        $fields[] = array(
            'key'   => "field_footer_legal_url_{$i}",
            'label' => "Legal Link {$i} URL",
            'name'  => "footer_legal_url_{$i}",
            'type'  => 'url',
        );
    }

    $fields[] = array(
        'key'         => 'field_footer_copyright',
        'label'       => 'Copyright Text',
        'name'        => 'footer_copyright',
        'type'        => 'text',
        'placeholder' => '© Ascendum 2025. All rights reserved.',
    );

    acf_add_local_field_group( array(
        'key'      => 'group_footer_settings',
        'title'    => 'Footer Settings',
        'fields'   => $fields,
        'location' => array(
            array(
                array(
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'page-footer-settings.php',
                ),
            ),
        ),
        'position' => 'normal',
        'style'    => 'default',
    ) );
}

// ---------------------------------------------------------------------------
// Custom block category
// ---------------------------------------------------------------------------

add_filter( 'block_categories_all', 'abc_block_categories', 10, 2 );
function abc_block_categories( $categories, $block_editor_context ) {
    return array_merge(
        array(
            array(
                'slug'  => 'ascendum',
                'title' => __( 'Brand Center', 'brand-center' ),
                'icon'  => null,
            ),
        ),
        $categories
    );
}
