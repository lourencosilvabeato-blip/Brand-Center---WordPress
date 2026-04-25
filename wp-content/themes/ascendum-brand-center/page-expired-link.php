<?php
/**
 * Template Name: Expired Link
 *
 * A02.3 — Shown when an invitation link is invalid or has expired.
 * Public page; no authentication required.
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

// Redirect already-authenticated users away.
if ( is_user_logged_in() ) {
    wp_safe_redirect( abc_homepage_url() );
    exit;
}

// ----- ACF field values (shared group_login_page) ---------------------------
$bg_image            = get_field( 'login_image' );
$login_title         = get_field( 'login_title' );
$login_introduction  = get_field( 'login_introduction' );
$login_subtitle      = get_field( 'login_subtitle' );
$institutional_label = get_field( 'login_institutional_label' );
$institutional_url   = get_field( 'login_institutional_url' );
$social_links        = abc_get_social_links();
$legal_links         = abc_get_legal_links();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'expired-link-page-body' ); ?>>
<?php wp_body_open(); ?>

<div class="set-password-page">

    <!-- ── Left column: background image ───────────────────────────────────── -->
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

    <!-- ── Right column: sidebar ────────────────────────────────────────────── -->
    <aside class="set-password-sidebar" aria-label="<?php esc_attr_e( 'Expired link', 'ascendum-brand-center' ); ?>">

        <!-- Content: headline + expired message -->
        <div class="set-password-sidebar-content">

            <!-- Headline area (optional) -->
            <?php if ( $login_title || $login_introduction || $login_subtitle ) : ?>
            <div class="set-password-headline">
                <?php if ( $login_title ) : ?>
                    <h1 class="set-password-title"><?php echo esc_html( $login_title ); ?></h1>
                <?php endif; ?>
                <?php if ( $login_introduction ) : ?>
                    <p class="set-password-introduction"><?php echo esc_html( $login_introduction ); ?></p>
                <?php endif; ?>
                <?php if ( $login_subtitle ) : ?>
                    <p class="set-password-subtitle"><?php echo esc_html( $login_subtitle ); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Expired link message -->
            <div class="set-password-expired">
                <p class="set-password-expired-heading">
                    <?php esc_html_e( 'This link is not available anymore.', 'ascendum-brand-center' ); ?>
                </p>
                <p class="set-password-expired-body">
                    <?php esc_html_e( 'This link is invalid or has expired. Please, request a new one.', 'ascendum-brand-center' ); ?>
                </p>
                <a href="<?php echo esc_url( abc_login_url() ); ?>" class="set-password-btn">
                    <?php esc_html_e( 'Reset password', 'ascendum-brand-center' ); ?>
                </a>
            </div>

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
                <?php esc_html_e( '@Ascendum 2025 All rights reserved', 'ascendum-brand-center' ); ?>
            </p>

        </footer>

    </aside><!-- /.set-password-sidebar -->

</div><!-- /.set-password-page -->

<?php wp_footer(); ?>
</body>
</html>
