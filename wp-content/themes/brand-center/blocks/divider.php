<?php
/**
 * Block: Divider (Separador)
 *
 * Horizontal separator between content sections.
 * Type: short (default) or long.
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

$type = isset( $block['divider_type'] ) && 'long' === $block['divider_type'] ? 'long' : 'short';
?>
<div class="block-divider block-divider--<?php echo esc_attr( $type ); ?>" role="separator" aria-hidden="true">
    <hr>
</div>
