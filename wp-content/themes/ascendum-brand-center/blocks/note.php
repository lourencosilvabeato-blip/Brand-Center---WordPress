<?php
/**
 * Block: Note (Nota)
 *
 * Callout box with background and left border accent.
 * Single visual style. Optional bold title/highlight prefix.
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

$note_title = trim( $block['note_title']   ?? '' );
$content    = trim( $block['note_content'] ?? '' );

if ( ! $content ) {
    return;
}
?>
<div class="block-note" role="note">
    <div class="block-note-content rich-text">
        <?php if ( $note_title ) : ?>
        <strong class="block-note-title"><?php echo esc_html( $note_title ); ?> </strong>
        <?php endif; ?>
        <?php echo wp_kses_post( $content ); ?>
    </div>
</div>
