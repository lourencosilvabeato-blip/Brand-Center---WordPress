<?php
/**
 * SSO integration — Microsoft Azure AD / O365 via WPO365 | LOGIN
 *
 * Responsibilities:
 *  - Expose the WPO365 OAuth entry point via the abc_sso_login_url filter
 *  - Register the internal_user custom role
 *  - Assign the correct role to SSO users on login
 *  - Fetch and cache the O365 profile photo via Microsoft Graph API
 *  - Serve the cached photo (or a theme fallback) as the WP avatar
 *
 * Prerequisites:
 *  - WPO365 | LOGIN plugin installed and configured with:
 *      Tenant ID, Client ID, Client Secret
 *      Scopes: openid profile email User.Read
 *      "User.Read" is required for the Graph photo endpoint
 *  - Azure AD app registration with the Redirect URI configured in WPO365
 *    and API permissions: openid, profile, email, User.Read (delegated)
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Custom roles
// ---------------------------------------------------------------------------

add_action( 'init', 'abc_register_roles' );
/**
 * Registers platform-specific roles.
 * Runs on every request but is a no-op once the roles exist in the DB.
 */
function abc_register_roles() {
    if ( ! get_role( 'internal_user' ) ) {
        add_role(
            'internal_user',
            __( 'Internal User', 'ascendum-brand-center' ),
            array(
                'read' => true, // Read-only. No admin access, no publishing.
            )
        );
    }

    if ( ! get_role( 'local_admin' ) ) {
        add_role(
            'local_admin',
            __( 'Local Admin', 'ascendum-brand-center' ),
            array(
                'read' => true,
            )
        );
    }

    if ( ! get_role( 'external_user' ) ) {
        add_role(
            'external_user',
            __( 'External User', 'ascendum-brand-center' ),
            array(
                'read' => true,
            )
        );
    }
}

// ---------------------------------------------------------------------------
// SSO login URL
// ---------------------------------------------------------------------------

add_filter( 'abc_sso_login_url', 'abc_get_sso_url' );
/**
 * Returns the WPO365 OAuth entry-point URL so the login button in
 * page-login.php is activated only when the plugin is installed.
 *
 * @param  string $url  Default value passed by the filter (empty string).
 * @return string       OAuth URL, or empty string to keep the button disabled.
 */
function abc_get_sso_url( $url ) {
    if ( ! class_exists( '\Wpo\Login' ) ) {
        // Plugin not installed — button stays disabled (see page-login.php).
        return '';
    }

    return add_query_arg(
        array(
            'action'      => 'openidredirect',
            'redirect_to' => abc_homepage_url(),
        ),
        home_url( '/' )
    );
}

// ---------------------------------------------------------------------------
// Post-SSO-login: role assignment + avatar refresh
// ---------------------------------------------------------------------------

add_action( 'wpo365_login_success', 'abc_on_sso_login', 10, 1 );
/**
 * Fires after WPO365 has authenticated a user and set the WP auth cookie,
 * while the access token is still live in the current request.
 *
 * @param int $user_id  WordPress user ID.
 */
function abc_on_sso_login( $user_id ) {
    $user = get_userdata( $user_id );
    if ( ! $user ) {
        return;
    }

    abc_maybe_assign_internal_role( $user );
    abc_fetch_o365_avatar( $user_id );
}

/**
 * Assigns the internal_user role to any SSO user who has not been given
 * an elevated role (administrator, local_admin) through WP admin.
 * Idempotent — safe to call on every login.
 *
 * @param WP_User $user
 */
function abc_maybe_assign_internal_role( WP_User $user ) {
    $elevated = array( 'administrator', 'editor', 'author', 'contributor', 'local_admin' );
    $roles    = (array) $user->roles;

    if ( empty( array_intersect( $elevated, $roles ) ) ) {
        $user->set_role( 'internal_user' );
    }
}

// ---------------------------------------------------------------------------
// Microsoft Graph — profile photo
// ---------------------------------------------------------------------------

/**
 * Retrieves the WPO365 Graph-compatible access token for the given user.
 *
 * WPO365 exposes a filter (v20+) for external consumers; we also check the
 * user meta fallback used by earlier versions.
 *
 * Note: the Azure AD app registration must include the "User.Read" scope
 * and WPO365 must be configured to request it, otherwise the token returned
 * will not have permission to call the photo endpoint.
 *
 * @param  int          $user_id
 * @return string|false  Bearer token string, or false if unavailable.
 */
function abc_get_wpo365_access_token( $user_id ) {
    // WPO365 v20+ filter.
    $token = apply_filters( 'wpo365_get_access_token', false, $user_id );
    if ( $token && is_string( $token ) ) {
        return $token;
    }

    // User meta fallback (WPO365 v12–v19).
    $token = get_user_meta( $user_id, 'wpo365_access_token', true );
    if ( $token && is_string( $token ) ) {
        return $token;
    }

    return false;
}

/**
 * Fetches the O365 profile photo from Microsoft Graph, saves it to
 * wp-content/uploads/abc-avatars/{user_id}.jpg, and stores the URL
 * in the abc_avatar_url user meta field.
 *
 * Silently no-ops if the token is unavailable, the user has no photo set,
 * or the upload directory is not writable.
 *
 * @param int $user_id
 */
function abc_fetch_o365_avatar( $user_id ) {
    $token = abc_get_wpo365_access_token( $user_id );
    if ( ! $token ) {
        return;
    }

    $response = wp_remote_get(
        'https://graph.microsoft.com/v1.0/me/photo/$value',
        array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
            ),
            'timeout' => 10,
        )
    );

    if ( is_wp_error( $response ) ) {
        return;
    }

    $code = (int) wp_remote_retrieve_response_code( $response );

    // 404 → user has no photo in O365; any other non-200 → transient error.
    // In both cases we leave the existing cached photo (or none) in place.
    if ( 200 !== $code ) {
        return;
    }

    $body = wp_remote_retrieve_body( $response );
    if ( empty( $body ) ) {
        return;
    }

    $upload     = wp_upload_dir();
    $avatar_dir = trailingslashit( $upload['basedir'] ) . 'abc-avatars';
    $file_name  = (int) $user_id . '.jpg';
    $file_path  = $avatar_dir . '/' . $file_name;
    $file_url   = trailingslashit( $upload['baseurl'] ) . 'abc-avatars/' . $file_name;

    if ( ! file_exists( $avatar_dir ) ) {
        wp_mkdir_p( $avatar_dir );
    }

    if ( ! is_writable( $avatar_dir ) ) {
        return;
    }

    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
    if ( false === file_put_contents( $file_path, $body ) ) {
        return;
    }

    update_user_meta( $user_id, 'abc_avatar_url', esc_url_raw( $file_url ) );
}

// ---------------------------------------------------------------------------
// Avatar URL filter — serve O365 photo or theme fallback
// ---------------------------------------------------------------------------

add_filter( 'get_avatar_url', 'abc_get_avatar_url', 10, 3 );
/**
 * Returns the locally cached O365 profile photo for the requested user,
 * or the theme's default avatar image if none is stored.
 *
 * Falls back to the original Gravatar URL for users outside the platform
 * (e.g. comment authors without a WP account).
 *
 * @param  string           $url          Original Gravatar URL.
 * @param  mixed            $id_or_email  User ID, email, WP_User, or WP_Comment.
 * @param  array            $args         Avatar arguments.
 * @return string
 */
function abc_get_avatar_url( $url, $id_or_email, $args ) {
    $user = false;

    if ( is_numeric( $id_or_email ) ) {
        $user = get_userdata( (int) $id_or_email );
    } elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
        $user = get_user_by( 'email', $id_or_email );
    } elseif ( $id_or_email instanceof WP_User ) {
        $user = $id_or_email;
    } elseif ( $id_or_email instanceof WP_Comment && ! empty( $id_or_email->user_id ) ) {
        $user = get_userdata( (int) $id_or_email->user_id );
    }

    if ( ! $user ) {
        return $url; // Not a registered user — leave Gravatar URL as-is.
    }

    $cached = get_user_meta( $user->ID, 'abc_avatar_url', true );
    if ( $cached ) {
        return esc_url( $cached );
    }

    // Default avatar: theme asset.
    // File: /assets/images/avatar-default.png — add this image to the theme.
    return esc_url( get_template_directory_uri() . '/assets/images/avatar-default.png' );
}
