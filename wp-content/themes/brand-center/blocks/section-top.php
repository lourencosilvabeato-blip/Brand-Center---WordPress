<?php
/**
 * Block: Section Top (Topo de Secção)
 *
 * Opens a new content section. Sets the anchor point consumed by the anchor bar.
 * Anchor ID defaults to a slugified Section Title when the Anchor ID field is empty.
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

$title    = trim( $block['section_title']  ?? '' );
$label    = trim( $block['section_label']  ?? '' );   // small text above H2
$anchor   = sanitize_title( trim( $block['section_anchor'] ?? $title ) );
$body     = trim( $block['section_body']   ?? '' );
$buttons  = is_array( $block['buttons'] ?? null ) ? $block['buttons'] : array();

if ( ! $title ) {
    return;
}
?>
<div
    id="<?php echo esc_attr( $anchor ); ?>"
    class="block-section-top"
>
    <?php if ( $label ) : ?>
    <span class="block-section-top-label"><?php echo esc_html( $label ); ?></span>
    <?php endif; ?>

    <h2 class="block-section-top-title"><?php echo esc_html( $title ); ?></h2>

    <?php if ( $body ) : ?>
    <div class="block-section-top-body rich-text"><?php echo wp_kses_post( $body ); ?></div>
    <?php endif; ?>

    <?php if ( ! empty( $buttons ) ) : ?>
    <div class="block-buttons">
        <?php foreach ( $buttons as $btn ) :
            $btn_label   = trim( $btn['button_label'] ?? '' );
            $btn_url     = trim( $btn['button_url']   ?? '' );
            $btn_file    = $btn['button_file'] ?? null;
            $btn_new_tab = ! empty( $btn['button_new_tab'] );

            if ( ! $btn_label ) {
                continue;
            }

            if ( $btn_file && ! empty( $btn_file['url'] ) ) {
                $href    = esc_url( $btn_file['url'] );
                $target  = ' target="_blank" rel="noopener noreferrer"';
                $dl_attr = ' download';
            } elseif ( $btn_url ) {
                $href    = esc_url( $btn_url );
                $target  = $btn_new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
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
        ><?php echo esc_html( $btn_label ); ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
