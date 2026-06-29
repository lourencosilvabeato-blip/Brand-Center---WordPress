<?php
/**
 * Template Name: Change Password
 *
 * A05 — Authenticated external users change their own password.
 * SSO roles (internal_user, local_admin, administrator) are redirected to the homepage.
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

// Must be logged in — abc_auth_redirect handles the unauthenticated redirect,
// but we guard here as well for direct access.
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( abc_login_url() );
    exit;
}

// Only external users may access this page.
$current_user = wp_get_current_user();
if ( ! in_array( 'external_user', (array) $current_user->roles, true ) ) {
    wp_safe_redirect( abc_homepage_url() );
    exit;
}

// ----- Error message map ----------------------------------------------------
$cp_error_messages = array(
    'required'      => __( 'This field is required.', 'brand-center' ),
    'wrong_current' => __( 'The current password you entered is incorrect. Please try again.', 'brand-center' ),
    'weak'          => __( 'Password must be at least 8 characters long and include an uppercase letter, a number, and a special character.', 'brand-center' ),
    'mismatch'      => __( 'The new passwords do not match. Please ensure both fields are identical.', 'brand-center' ),
    'system'        => __( 'An unexpected error occurred. Please try again shortly.', 'brand-center' ),
);

$cp_error_key = isset( $_GET['cp_error'] ) ? sanitize_key( $_GET['cp_error'] ) : '';
$cp_error_msg = isset( $cp_error_messages[ $cp_error_key ] ) ? $cp_error_messages[ $cp_error_key ] : '';

// Determine which inputs carry the error border.
$err_current = in_array( $cp_error_key, array( 'required', 'wrong_current' ), true );
$err_new     = in_array( $cp_error_key, array( 'required', 'weak', 'mismatch' ), true );
$err_confirm = in_array( $cp_error_key, array( 'required', 'mismatch' ), true );

// ----- ACF field values -----------------------------------------------------
$cp_title = get_field( 'cp_title' );
$cp_intro = get_field( 'cp_introduction' );

get_header();
?>

<main class="change-password-page" id="main">
    <div class="change-password-container">

        <!-- Headline (optional, BO-configurable) -->
        <?php if ( $cp_title || $cp_intro ) : ?>
        <div class="change-password-headline">
            <?php if ( $cp_title ) : ?>
                <h1 class="change-password-title"><?php echo esc_html( $cp_title ); ?></h1>
            <?php endif; ?>
            <?php if ( $cp_intro ) : ?>
                <p class="change-password-intro"><?php echo esc_html( $cp_intro ); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Change password form -->
        <form
            class="change-password-form"
            method="post"
            action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
            novalidate
        >
            <input type="hidden" name="action" value="abc_change_password">
            <?php wp_nonce_field( 'abc_change_password', 'abc_change_password_nonce' ); ?>

            <!-- Old password -->
            <div class="change-password-field">
                <label class="change-password-label" for="cp-current">
                    <?php esc_html_e( 'Old password', 'brand-center' ); ?>
                </label>
                <?php if ( $err_current && $cp_error_msg ) : ?>
                <div class="change-password-error-message" role="alert">
                    <?php echo esc_html( $cp_error_msg ); ?>
                </div>
                <?php endif; ?>
                <input
                    type="password"
                    id="cp-current"
                    name="current_password"
                    class="change-password-input<?php echo $err_current ? ' is-error' : ''; ?>"
                    placeholder="<?php esc_attr_e( 'Insert your old password', 'brand-center' ); ?>"
                    autocomplete="current-password"
                >
            </div>

            <!-- New password -->
            <div class="change-password-field">
                <label class="change-password-label" for="cp-new">
                    <?php esc_html_e( 'New password', 'brand-center' ); ?>
                </label>
                <?php if ( $err_new && $cp_error_msg && ! $err_current ) : ?>
                <div class="change-password-error-message" role="alert">
                    <?php echo esc_html( $cp_error_msg ); ?>
                </div>
                <?php endif; ?>
                <input
                    type="password"
                    id="cp-new"
                    name="password"
                    class="change-password-input<?php echo $err_new ? ' is-error' : ''; ?>"
                    placeholder="<?php esc_attr_e( 'Insert your new password', 'brand-center' ); ?>"
                    autocomplete="new-password"
                >
                <p class="change-password-hint">
                    <?php esc_html_e( 'Password must be at least 8 characters long and include an uppercase letter, a number, and a special character.', 'brand-center' ); ?>
                </p>
            </div>

            <!-- Confirm new password -->
            <div class="change-password-field">
                <label class="change-password-label" for="cp-confirm">
                    <?php esc_html_e( 'Confirm new password', 'brand-center' ); ?>
                </label>
                <?php if ( $err_confirm && $cp_error_msg && ! $err_current && ! $err_new ) : ?>
                <div class="change-password-error-message" role="alert">
                    <?php echo esc_html( $cp_error_msg ); ?>
                </div>
                <?php endif; ?>
                <input
                    type="password"
                    id="cp-confirm"
                    name="password2"
                    class="change-password-input<?php echo $err_confirm ? ' is-error' : ''; ?>"
                    placeholder="<?php esc_attr_e( 'Confirm your new password', 'brand-center' ); ?>"
                    autocomplete="new-password"
                >
            </div>

            <?php if ( 'system' === $cp_error_key ) : ?>
            <div class="change-password-error-message" role="alert">
                <?php echo esc_html( $cp_error_msg ); ?>
            </div>
            <?php endif; ?>

            <button type="submit" class="change-password-btn">
                <?php esc_html_e( 'Save password', 'brand-center' ); ?>
            </button>

        </form>

    </div>
</main>

<?php get_footer(); ?>
