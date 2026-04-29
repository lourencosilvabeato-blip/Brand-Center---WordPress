<?php
/**
 * Block: Collection (Colecção)
 *
 * Displays a collection card linking to the detail page (?collection=N).
 * Card model: Large (default) or Small (two adjacent smalls render side by side — handled by template).
 * Asset count is calculated automatically from the assets list.
 * "Download collection" button appears only when a download_all_file is set (sticky on detail page).
 *
 * Thumbnail grid: uses first N asset images; empty slots show grey placeholder.
 * Images use object-fit: contain (letterboxed in grey) to avoid cropping.
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

$title        = trim( $block['collection_title']       ?? '' );
$label        = trim( $block['collection_label']       ?? '' );
$card_model   = ( 'small' === ( $block['card_model'] ?? 'large' ) ) ? 'small' : 'large';
$assets       = is_array( $block['collection_assets']  ?? null ) ? $block['collection_assets'] : array();
$download_all = is_array( $block['download_all_file']  ?? null ) ? $block['download_all_file'] : null;

if ( ! $title ) {
    return;
}

$asset_count = count( $assets );

// Resolve the collection index (0-based among all collection blocks on the page)
// by reading a counter stored as a page-level static variable.
// The template iterates all blocks sequentially so this increment is reliable.
static $abc_collection_counter = -1;
$abc_collection_counter++;
$collection_url = add_query_arg( 'collection', $abc_collection_counter, get_permalink() );

// Determine how many thumbnail slots to show based on card model.
$thumb_slots = 'large' === $card_model ? 4 : 2;
$thumbnails  = array_slice( $assets, 0, $thumb_slots );
?>
<div class="block-collection block-collection--<?php echo esc_attr( $card_model ); ?>">

    <div class="bc-card">

        <?php if ( $label ) : ?>
        <span class="bc-card-label"><?php echo esc_html( $label ); ?></span>
        <?php endif; ?>

        <h3 class="bc-card-title"><?php echo esc_html( $title ); ?></h3>

        <p class="bc-card-count">
            <?php
            printf(
                /* translators: %d: number of assets */
                esc_html( _n( '%d asset', '%d assets', $asset_count, 'ascendum-brand-center' ) ),
                $asset_count
            );
            ?>
        </p>

        <!-- Thumbnail grid -->
        <div class="bc-thumb-grid bc-thumb-grid--<?php echo esc_attr( $thumb_slots ); ?>">
            <?php for ( $i = 0; $i < $thumb_slots; $i++ ) :
                $asset = $thumbnails[ $i ] ?? null;
                $img   = is_array( $asset['asset_image'] ?? null ) ? $asset['asset_image'] : null;
            ?>
            <div class="bc-thumb-slot">
                <?php if ( $img && ! empty( $img['url'] ) ) : ?>
                <img
                    src="<?php echo esc_url( $img['url'] ); ?>"
                    alt="<?php echo esc_attr( $img['alt'] ?? '' ); ?>"
                    loading="lazy"
                >
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Actions -->
        <div class="bc-card-actions">
            <a
                href="<?php echo esc_url( $collection_url ); ?>"
                class="bc-btn bc-btn--detail"
            ><?php esc_html_e( 'View collection', 'ascendum-brand-center' ); ?></a>

            <?php if ( $download_all && ! empty( $download_all['url'] ) ) : ?>
            <a
                href="<?php echo esc_url( $download_all['url'] ); ?>"
                class="bc-btn bc-btn--download"
                download
                target="_blank"
                rel="noopener noreferrer"
            >
                <?php abc_icon( 'download' ); ?>
                <?php esc_html_e( 'Download collection', 'ascendum-brand-center' ); ?>
            </a>
            <?php endif; ?>
        </div>

    </div><!-- /.bc-card -->

</div><!-- /.block-collection -->
