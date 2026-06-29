<?php
/**
 * Block: Paragraph
 *
 * Renders a rich-text (WYSIWYG) content block.
 * Supports custom list styles (Check List, Red Cross List) via TinyMCE formats.
 *
 * Variables available from parent scope:
 *  $block  — current ACF flexible content row
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

$content = isset( $block['paragraph_content'] ) ? trim( $block['paragraph_content'] ) : '';

if ( ! $content ) {
    return;
}
?>
<div class="block-paragraph">
    <div class="block-paragraph-content rich-text">
        <?php echo wp_kses_post( $content ); ?>
    </div>
</div>
