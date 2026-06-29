<?php
/**
 * Template Name: Login
 *
 * Public-facing login screen (A01). No navigation. Accessible to unauthenticated
 * users only — logged-in users are redirected to the homepage.
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

// Redirect already-authenticated users away from this page.
if ( is_user_logged_in() ) {
    wp_safe_redirect( abc_homepage_url() );
    exit;
}

// ----- Error message map (spec: Confluence 1228505349) ----------------------
$error_messages = array(
    'required'            => __( 'This field is required.', 'brand-center' ),
    'invalid_email'       => __( 'Please enter a valid email address.', 'brand-center' ),
    'invalid_credentials' => __( 'Invalid credentials. Please check your email and password and try again.', 'brand-center' ),
    'system'              => __( 'An unexpected error occurred. Please try again shortly.', 'brand-center' ),
);

$error_key = isset( $_GET['login_error'] ) ? sanitize_key( $_GET['login_error'] ) : '';
$error_msg = isset( $error_messages[ $error_key ] ) ? $error_messages[ $error_key ] : '';

// ----- ACF field values -----------------------------------------------------
$bg_image            = get_field( 'login_image' );
$login_title         = get_field( 'login_title' );
$login_introduction  = get_field( 'login_introduction' );
$login_subtitle      = get_field( 'login_subtitle' );
$institutional_label = get_field( 'login_institutional_label' );
$institutional_url   = get_field( 'login_institutional_url' );
$social_links        = abc_get_social_links();
$legal_links         = abc_get_legal_links();

// ----- URLs -----------------------------------------------------------------
// A03: password recovery page (to be created when A03 is implemented).
$forgot_password_url = get_permalink( get_page_by_path( 'password-recovery' ) ) ?: home_url( '/password-recovery/' );

// SSO: OAuth redirect URL — populated via filter once OAuth plugin is configured.
// TODO: configure the SSO URL in the OAuth plugin and expose it through this filter.
$sso_url = apply_filters( 'abc_sso_login_url', '' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'login-page-body' ); ?>>
<?php wp_body_open(); ?>

<div class="login-page">

    <!-- ── Left column: background image ───────────────────────────────── -->
    <div class="login-image" role="img" aria-hidden="true">
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
    <aside class="login-sidebar" aria-label="<?php esc_attr_e( 'Login', 'brand-center' ); ?>">

        <!-- Content: headline + form + SSO -->
        <div class="login-sidebar-content">

            <!-- Headline area (all three fields optional) -->
            <?php if ( $login_title || $login_introduction || $login_subtitle ) : ?>
            <div class="login-headline">
                <?php if ( $login_title ) : ?>
                    <h1 class="login-title"><?php echo esc_html( $login_title ); ?></h1>
                <?php endif; ?>
                <?php if ( $login_introduction ) : ?>
                    <p class="login-introduction"><?php echo esc_html( $login_introduction ); ?></p>
                <?php endif; ?>
                <?php if ( $login_subtitle ) : ?>
                    <p class="login-subtitle"><?php echo esc_html( $login_subtitle ); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- External login form -->
            <form
                id="login-form"
                class="login-form"
                method="post"
                action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                novalidate
            >
                <input type="hidden" name="action" value="abc_login">
                <?php wp_nonce_field( 'abc_login', 'abc_login_nonce' ); ?>

                <!-- Form fields group: label + error + email + password -->
                <div class="login-form-fields">

                    <p class="login-form-label">
                        <?php esc_html_e( 'Access Brand Center', 'brand-center' ); ?>
                    </p>

                    <?php if ( $error_msg ) : ?>
                    <div class="login-error-message" role="alert">
                        <?php echo esc_html( $error_msg ); ?>
                    </div>
                    <?php endif; ?>

                    <div class="login-field">
                        <label class="screen-reader-text" for="login-email">
                            <?php esc_html_e( 'Email address', 'brand-center' ); ?>
                        </label>
                        <input
                            type="email"
                            id="login-email"
                            name="log"
                            class="login-input<?php echo ( 'required' === $error_key || 'invalid_email' === $error_key ) ? ' is-error' : ''; ?>"
                            placeholder="<?php esc_attr_e( 'E-mail', 'brand-center' ); ?>"
                            autocomplete="email"
                            spellcheck="false"
                        >
                    </div>

                    <div class="login-field">
                        <label class="screen-reader-text" for="login-password">
                            <?php esc_html_e( 'Password', 'brand-center' ); ?>
                        </label>
                        <input
                            type="password"
                            id="login-password"
                            name="pwd"
                            class="login-input<?php echo ( 'required' === $error_key || 'invalid_credentials' === $error_key ) ? ' is-error' : ''; ?>"
                            placeholder="<?php esc_attr_e( 'Password', 'brand-center' ); ?>"
                            autocomplete="current-password"
                        >
                    </div>

                </div><!-- /.login-form-fields -->

                <!-- Options row: Remember me + Forgot password -->
                <div class="login-options">
                    <label class="login-remember-label">
                        <input
                            type="checkbox"
                            name="rememberme"
                            class="login-checkbox"
                            value="forever"
                        >
                        <span class="login-remember-text">
                            <?php esc_html_e( 'Remember me', 'brand-center' ); ?>
                        </span>
                    </label>
                    <a
                        href="<?php echo esc_url( $forgot_password_url ); ?>"
                        class="login-forgot-link"
                    >
                        <?php esc_html_e( 'Forgot password?', 'brand-center' ); ?>
                    </a>
                </div>

                <!-- Submit -->
                <button type="submit" class="login-btn login-btn--enter">
                    <?php esc_html_e( 'Enter', 'brand-center' ); ?>
                </button>

            </form><!-- /#login-form -->

            <!-- SSO section: divider + button -->
            <div class="login-sso-divider" aria-hidden="true">
                <span class="login-sso-divider-text">
                    <?php esc_html_e( 'Team member? Please, login here:', 'brand-center' ); ?>
                </span>
            </div>

            <?php if ( $sso_url ) : ?>
            <a
                href="<?php echo esc_url( $sso_url ); ?>"
                class="login-btn login-btn--sso"
            >
                <!-- Microsoft Windows logo (standard 4-square mark) -->
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <path d="M0 0H9.5V9.5H0z" fill="#f25022"/>
                    <path d="M10.5 0H20V9.5H10.5z" fill="#7fba00"/>
                    <path d="M0 10.5H9.5V20H0z" fill="#00a4ef"/>
                    <path d="M10.5 10.5H20V20H10.5z" fill="#ffb900"/>
                </svg>
                <?php esc_html_e( 'Login with Ascendum account', 'brand-center' ); ?>
            </a>
            <?php else : ?>
            <button
                type="button"
                class="login-btn login-btn--sso"
                disabled
                aria-disabled="true"
                title="<?php esc_attr_e( 'SSO not yet configured.', 'brand-center' ); ?>"
            >
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <path d="M0 0H9.5V9.5H0z" fill="#f25022"/>
                    <path d="M10.5 0H20V9.5H10.5z" fill="#7fba00"/>
                    <path d="M0 10.5H9.5V20H0z" fill="#00a4ef"/>
                    <path d="M10.5 10.5H20V20H10.5z" fill="#ffb900"/>
                </svg>
                <?php esc_html_e( 'Login with Ascendum account', 'brand-center' ); ?>
            </button>
            <?php endif; ?>

        </div><!-- /.login-sidebar-content -->

        <!-- Footer separator: pushes footer to bottom of sidebar -->
        <div class="login-footer-separator" aria-hidden="true"></div>

        <!-- Footer: institutional link + social icons + legal links + copyright -->
        <footer class="login-footer">

            <div class="login-social-row">
                <?php if ( $institutional_label && $institutional_url ) : ?>
                <a
                    href="<?php echo esc_url( $institutional_url ); ?>"
                    class="login-institutional-link"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php echo esc_html( $institutional_label ); ?>
                </a>
                <?php endif; ?>

                <?php if ( $social_links ) : ?>
                <div class="login-social-icons">
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
                        class="login-social-icon-link"
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
            <div class="login-legal-links">
                <?php foreach ( $legal_links as $item ) :
                    $label = $item['legal_label'];
                    $url   = $item['legal_url'];
                    if ( ! $label || ! $url ) {
                        continue;
                    }
                ?>
                <a
                    href="<?php echo esc_url( $url ); ?>"
                    class="login-legal-link"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php echo esc_html( $label ); ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <p class="login-copyright">
                <?php esc_html_e( '@Ascendum 2025 All rights reserved', 'brand-center' ); ?>
            </p>

        </footer>

    </aside><!-- /.login-sidebar -->

</div><!-- /.login-page -->

<?php wp_footer(); ?>
</body>
</html>
