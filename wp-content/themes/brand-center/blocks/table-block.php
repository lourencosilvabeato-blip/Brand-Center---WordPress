<?php
/**
 * Block: Table (Tabela)
 *
 * Structured table with configurable column count (1–5) and a row repeater.
 * First row is optionally rendered as <thead>. Horizontal scroll on overflow.
 * Max 5 columns per spec. Cells support basic rich text.
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

$col_count  = max( 1, min( 5, (int) ( $block['table_columns'] ?? 3 ) ) );
$has_header = ! empty( $block['table_has_header'] );
$rows       = is_array( $block['table_rows'] ?? null ) ? $block['table_rows'] : array();

if ( empty( $rows ) ) {
    return;
}

$cell_keys = array( 'cell_1', 'cell_2', 'cell_3', 'cell_4', 'cell_5' );

// Split rows into header row (first, if option is on) and body rows.
$header_row = null;
$body_rows  = $rows;
if ( $has_header ) {
    $header_row = array_shift( $body_rows );
}
?>
<div class="block-table<?php echo $has_header ? ' has-header' : ''; ?>">
    <div class="block-table-scroll">
        <table>

            <?php if ( $header_row ) : ?>
            <thead>
                <tr>
                    <?php for ( $c = 0; $c < $col_count; $c++ ) : ?>
                    <th scope="col"><?php echo wp_kses_post( $header_row[ $cell_keys[ $c ] ] ?? '' ); ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <?php endif; ?>

            <?php if ( ! empty( $body_rows ) ) : ?>
            <tbody>
                <?php foreach ( $body_rows as $row ) : ?>
                <tr>
                    <?php for ( $c = 0; $c < $col_count; $c++ ) : ?>
                    <td><?php echo wp_kses_post( $row[ $cell_keys[ $c ] ] ?? '' ); ?></td>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <?php elseif ( ! $has_header ) : ?>
            <tbody>
                <?php foreach ( $rows as $row ) : ?>
                <tr>
                    <?php for ( $c = 0; $c < $col_count; $c++ ) : ?>
                    <td><?php echo wp_kses_post( $row[ $cell_keys[ $c ] ] ?? '' ); ?></td>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <?php endif; ?>

        </table>
    </div>
</div>
