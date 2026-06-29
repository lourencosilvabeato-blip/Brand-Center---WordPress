<?php
/**
 * Block: Page Introduction
 *
 * Optional introductory text and/or CTA buttons displayed below the
 * auto-rendered page title (H1 comes from the WordPress page title).
 * Renders nothing if both intro and buttons are absent.
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

$intro   = trim( $block['page_intro'] ?? '' );
$buttons = is_array( $block['buttons'] ?? null ) ? $block['buttons'] : array();

if ( ! $intro && empty( $buttons ) ) {
    return;
}
?>
<div class="block-page-header">

    <?php if ( $intro ) : ?>
    <div class="block-page-header-intro rich-text"><?php echo wp_kses_post( $intro ); ?></div>
    <?php endif; ?>

    <?php if ( ! empty( $buttons ) ) : ?>
    <div class="block-buttons">
        <?php foreach ( $buttons as $btn ) :
            $label   = trim( $btn['button_label'] ?? '' );
            $url     = trim( $btn['button_url']   ?? '' );
            $file    = $btn['button_file'] ?? null;
            $new_tab = ! empty( $btn['button_new_tab'] );

            if ( ! $label ) {
                continue;
            }

            if ( $file && ! empty( $file['url'] ) ) {
                $href    = esc_url( $file['url'] );
                $target  = ' target="_blank" rel="noopener noreferrer"';
                $dl_attr = ' download';
            } elseif ( $url ) {
                $href    = esc_url( $url );
                $target  = $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
                $dl_attr = '';
            } else {
                continue;
            }
        ?>
        <a
            href="<?php echo $href; ?>"
            class="block-btn"
            <?php echo $target; ?>
            <?php echo $dl_attr; ?>
        ><?php echo esc_html( $label ); ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
