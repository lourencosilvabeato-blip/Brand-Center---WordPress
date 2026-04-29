<?php
/**
 * Footer template.
 *
 * Reads content from the 'footer-settings' WP page via ACF fields.
 * If the page doesn't exist yet, outputs an empty footer shell.
 *
 * @package ascendum-brand-center
 */

    $fpid = null;                                                                                                                                                                      
    $footer_page = get_page_by_path( 'footer-settings' );                                                                                                                                     
    if ( $footer_page ) {                                                                                                                                                                     
      $fpid = (int) $footer_page->ID;  
    }

$brand_name   = $fpid ? get_field( 'footer_brand_name', $fpid )        : '';
$inst_url     = $fpid ? get_field( 'footer_institutional_url', $fpid ) : '';
$copyright    = $fpid ? get_field( 'footer_copyright', $fpid )         : '';
$social_links = array();
$legal_links  = array();

if ( $fpid && function_exists( 'get_field' ) ) {
    for ( $i = 1; $i <= 4; $i++ ) {
        $url  = get_field( 'footer_social_url_' . $i, $fpid );
        if ( $url ) {
            $icon = get_field( 'footer_social_icon_' . $i, $fpid );
            $social_links[] = array( 'icon' => $icon, 'url' => $url );
        }
    }
    for ( $i = 1; $i <= 3; $i++ ) {
        $label = get_field( 'footer_legal_label_' . $i, $fpid );
        $url   = get_field( 'footer_legal_url_' . $i, $fpid );
        if ( $label && $url ) {
            $legal_links[] = array( 'label' => $label, 'url' => $url );
        }
    }
}
?>

<footer id="site-footer" class="site-footer">
    <div class="site-footer-inner">

        <?php if ( $brand_name ) : ?>
        <a
            href="<?php echo esc_url( $inst_url ?: '#' ); ?>"
            class="site-footer-brand"
            <?php if ( $inst_url ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
        >
            <?php echo esc_html( $brand_name ); ?>
        </a>
        <?php endif; ?>

        <?php if ( ! empty( $social_links ) ) : ?>
        <div class="site-footer-divider" aria-hidden="true"></div>
        <div class="site-footer-social">
            <span class="site-footer-social-label"><?php esc_html_e( 'SIGA-NOS', 'ascendum-brand-center' ); ?></span>
            <div class="site-footer-social-icons">
                <?php foreach ( $social_links as $slink ) :
                    if ( is_array( $slink['icon'] ) ) {
                        $icon_url = $slink['icon']['url'] ?? '';
                        $icon_alt = $slink['icon']['alt'] ?? '';
                    } elseif ( is_int( $slink['icon'] ) && $slink['icon'] > 0 ) {
                        $icon_url = wp_get_attachment_image_url( $slink['icon'], 'full' ) ?: '';
                        $icon_alt = get_post_meta( $slink['icon'], '_wp_attachment_image_alt', true );
                    } else {
                        $icon_url = '';
                        $icon_alt = '';
                    }
                ?>
                <a
                    href="<?php echo esc_url( $slink['url'] ); ?>"
                    class="site-footer-social-link"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php if ( $icon_url ) : ?>
                    <img
                        src="<?php echo esc_url( $icon_url ); ?>"
                        alt="<?php echo esc_attr( $icon_alt ); ?>"
                        width="32"
                        height="32"
                    >
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ( ! empty( $legal_links ) ) : ?>
        <div class="site-footer-divider" aria-hidden="true"></div>
        <nav class="site-footer-legal" aria-label="<?php esc_attr_e( 'Legal', 'ascendum-brand-center' ); ?>">
            <?php foreach ( $legal_links as $llink ) : ?>
            <a
                href="<?php echo esc_url( $llink['url'] ); ?>"
                class="site-footer-legal-link"
                target="_blank"
                rel="noopener noreferrer"
            >
                <?php echo esc_html( $llink['label'] ); ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <?php if ( $copyright ) : ?>
        <span class="site-footer-copyright"><?php echo esc_html( $copyright ); ?></span>
        <?php endif; ?>

    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
