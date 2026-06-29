<?php
/**
 * Template Name: Password Recovery
 *
 * A03.1 — External user requests a password reset link.
 * Three states driven by query parameters:
 *   - default:   email entry form (Figma node 4937:4249)
 *   - ?sent=1:   email sent confirmation (Figma node 4937:4275)
 *   - ?expired=1 redirected here from reset-password when token is invalid
 *                (Figma node 5343:15762 — reuses the same expired copy)
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

// Redirect already-authenticated users away.
if ( is_user_logged_in() ) {
    wp_safe_redirect( abc_homepage_url() );
    exit;
}

// ----- State detection -------------------------------------------------------
$state = 'form'; // default
if ( ! empty( $_GET['sent'] ) && '1' === $_GET['sent'] ) {
    $state = 'sent';
} elseif ( ! empty( $_GET['expired'] ) && '1' === $_GET['expired'] ) {
    $state = 'expired';
}

// ----- Error messages (form state only) -------------------------------------
$error_messages = array(
    'required'      => __( 'This field is required.', 'brand-center' ),
    'invalid_email' => __( 'Please enter a valid email address.', 'brand-center' ),
    'system'        => __( 'An unexpected error occurred. Please try again shortly.', 'brand-center' ),
);

$error_key = isset( $_GET['pr_error'] ) ? sanitize_key( $_GET['pr_error'] ) : '';
$error_msg = isset( $error_messages[ $error_key ] ) ? $error_messages[ $error_key ] : '';

// ----- ACF values (shared with login page) ----------------------------------
$bg_image            = get_field( 'login_image' );
$login_title         = get_field( 'login_title' );
$login_introduction  = get_field( 'login_introduction' );
$institutional_label = get_field( 'login_institutional_label' );
$institutional_url   = get_field( 'login_institutional_url' );
$social_links        = abc_get_social_links();
$legal_links         = abc_get_legal_links();

// ----- URLs ------------------------------------------------------------------
$login_url = abc_login_url();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'password-recovery-page-body' ); ?>>
<?php wp_body_open(); ?>

<div class="set-password-page">

    <!-- ── Left column: background image ───────────────────────────────── -->
    <div class="set-password-image" role="img" aria-hidden="true">
        <?php if ( $bg_image ) : ?>
            <img
                src="<?php echo esc_url( $bg_image['url'] ); ?>"
                alt=""
                width="<?php echo esc_attr( $bg_image['width'] ); ?>"
                height="<?php echo esc_attr( $bg_image['height'] ); ?>"
            >
        <?php endif; ?>
    </div>

    <!-- ── Right column: sidebar ────────────────────────────────────────── -->
    <aside class="set-password-sidebar" aria-label="<?php esc_attr_e( 'Password Recovery', 'brand-center' ); ?>">

        <div class="set-password-sidebar-content">

            <!-- Headline area -->
            <?php if ( $login_title || $login_introduction ) : ?>
            <div class="set-password-headline">
                <?php if ( $login_title ) : ?>
                    <h1 class="set-password-title"><?php echo esc_html( $login_title ); ?></h1>
                <?php endif; ?>
                <?php if ( $login_introduction ) : ?>
                    <p class="set-password-introduction"><?php echo esc_html( $login_introduction ); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ( 'sent' === $state ) : ?>

            <!-- ── State: email sent confirmation (Figma 4937:4275) ──── -->
            <div class="set-password-expired">
                <p class="set-password-form-label">
                    <?php esc_html_e( 'E-mail sent!', 'brand-center' ); ?>
                </p>
                <p class="set-password-expired-body">
                    <?php esc_html_e( 'If an account exists for this email, you will receive password reset instructions shortly. Please check your spam folder.', 'brand-center' ); ?>
                </p>
                <a href="<?php echo esc_url( $login_url ); ?>" class="set-password-btn">
                    <?php esc_html_e( 'Back to Login', 'brand-center' ); ?>
                </a>
            </div>

            <?php elseif ( 'expired' === $state ) : ?>

            <!-- ── State: expired / invalid token (Figma 5343:15762) ─── -->
            <div class="set-password-expired">
                <p class="set-password-expired-heading">
                    <?php esc_html_e( 'This link is not available anymore.', 'brand-center' ); ?>
                </p>
                <p class="set-password-expired-body">
                    <?php esc_html_e( 'This link is invalid or has expired. Please, request a new one.', 'brand-center' ); ?>
                </p>
                <a href="<?php echo esc_url( get_permalink() ); ?>" class="set-password-btn">
                    <?php esc_html_e( 'Reset password', 'brand-center' ); ?>
                </a>
            </div>

            <?php else : ?>

            <!-- ── State: email entry form (Figma 4937:4249) ─────────── -->
            <form
                id="recovery-form"
                class="set-password-form"
                method="post"
                action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                novalidate
            >
                <input type="hidden" name="action" value="abc_password_recovery">
                <?php wp_nonce_field( 'abc_password_recovery', 'abc_recovery_nonce' ); ?>

                <div class="set-password-fields">

                    <p class="set-password-form-label">
                        <?php esc_html_e( 'Reset password', 'brand-center' ); ?>
                    </p>

                    <?php if ( $error_msg ) : ?>
                    <div class="set-password-error-message" role="alert">
                        <?php echo esc_html( $error_msg ); ?>
                    </div>
                    <?php endif; ?>

                    <div class="set-password-field">
                        <label class="screen-reader-text" for="recovery-email">
                            <?php esc_html_e( 'Email address', 'brand-center' ); ?>
                        </label>
                        <input
                            type="email"
                            id="recovery-email"
                            name="recovery_email"
                            class="set-password-input<?php echo ( 'required' === $error_key || 'invalid_email' === $error_key ) ? ' is-error' : ''; ?>"
                            placeholder="<?php esc_attr_e( 'E-mail', 'brand-center' ); ?>"
                            autocomplete="email"
                            spellcheck="false"
                        >
                    </div>

                </div><!-- /.set-password-fields -->

                <button type="submit" class="set-password-btn">
                    <?php esc_html_e( 'Reset password', 'brand-center' ); ?>
                </button>

                <div class="set-password-options">
                    <a href="<?php echo esc_url( $login_url ); ?>" class="set-password-back-link">
                        <?php esc_html_e( 'Back to Login', 'brand-center' ); ?>
                    </a>
                </div>

            </form>

            <?php endif; ?>

        </div><!-- /.set-password-sidebar-content -->

        <!-- Footer separator -->
        <div class="set-password-footer-separator" aria-hidden="true"></div>

        <!-- Footer -->
        <footer class="set-password-footer">

            <div class="set-password-social-row">
                <?php if ( $institutional_label && $institutional_url ) : ?>
                <a
                    href="<?php echo esc_url( $institutional_url ); ?>"
                    class="set-password-institutional-link"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php echo esc_html( $institutional_label ); ?>
                </a>
                <?php endif; ?>

                <?php if ( $social_links ) : ?>
                <div class="set-password-social-icons">
                    <?php foreach ( $social_links as $item ) :
                        $icon = $item['social_icon'];
                        $url  = $item['social_url'];
                        if ( ! $icon || ! $url ) {
                            continue;
                        }
                        $alt = ! empty( $icon['alt'] ) ? $icon['alt'] : ( ! empty( $icon['title'] ) ? $icon['title'] : '' );
                    ?>
                    <a
                        href="<?php echo esc_url( $url ); ?>"
                        class="set-password-social-icon-link"
                        target="_blank"
                        rel="noopener noreferrer"
                        <?php if ( $alt ) : ?>aria-label="<?php echo esc_attr( $alt ); ?>"<?php endif; ?>
                    >
                        <img
                            src="<?php echo esc_url( $icon['url'] ); ?>"
                            alt=""
                            width="24"
                            height="24"
                        >
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if ( $legal_links ) : ?>
            <div class="set-password-legal-links">
                <?php foreach ( $legal_links as $item ) :
                    $label = $item['legal_label'];
                    $url   = $item['legal_url'];
                    if ( ! $label || ! $url ) {
                        continue;
                    }
                ?>
                <a
                    href="<?php echo esc_url( $url ); ?>"
                    class="set-password-legal-link"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php echo esc_html( $label ); ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <p class="set-password-copyright">
                <?php esc_html_e( '@Ascendum 2025 All rights reserved', 'brand-center' ); ?>
            </p>

        </footer>

    </aside><!-- /.set-password-sidebar -->

</div><!-- /.set-password-page -->

<?php wp_footer(); ?>
</body>
</html>
