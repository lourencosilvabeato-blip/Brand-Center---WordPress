<?php
/**
 * Block: Quote
 *
 * Renders a styled blockquote with optional author attribution.
 *
 * Variables available from parent scope:
 *  $block  — current ACF flexible content row
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

$text   = isset( $block['quote_text'] )   ? trim( $block['quote_text'] )   : '';
$author = isset( $block['quote_author'] ) ? trim( $block['quote_author'] ) : '';

if ( ! $text ) {
    return;
}
?>
<div class="block-quote">
    <div class="block-quote-body">
        <span class="block-quote-mark block-quote-mark--open" aria-hidden="true">&#x201C;</span>
        <blockquote class="block-quote-inner">
            <p class="block-quote-text"><?php echo esc_html( $text ); ?></p>
        </blockquote>
    </div>
    <?php if ( $author ) : ?>
    <p class="block-quote-author"><cite><?php echo esc_html( $author ); ?></cite></p>
    <?php endif; ?>
    <span class="block-quote-mark" aria-hidden="true">&#x201D;</span>
</div>
