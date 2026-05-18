<?php
/**
 * Block: Collection (Colecção)
 *
 * Displays a collection card linking to the detail page (?collection=N).
 * Card model: Large (default) or Small (two adjacent smalls render side by side — handled by template).
 * Asset count is calculated automatically from the assets list.
 *
 * Thumbnail strip: 10 portrait slots (88×110px each), overflow-hidden, no interaction on card.
 * Action row: two circle icon buttons — view (filled) and download (outlined, optional).
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

// Resolve the collection index (0-based among all collection blocks on the page).
static $abc_collection_counter = -1;
$abc_collection_counter++;
$collection_url = add_query_arg( 'collection', $abc_collection_counter, get_permalink() );
?>
<div class="block-collection block-collection--<?php echo esc_attr( $card_model ); ?>">

    <div class="bc-card">

        <!-- Portrait thumbnail strip — 10 slots, overflow hidden -->
        <div class="bc-thumb-strip">
            <?php for ( $i = 0; $i < 10; $i++ ) :
                $asset = $assets[ $i ] ?? null;
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
        </div><!-- /.bc-thumb-strip -->

        <!-- Info: label, title, count, actions -->
        <div class="bc-card-info">

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

            <!-- Circle icon buttons -->
            <div class="bc-card-actions">
                <a
                    href="<?php echo esc_url( $collection_url ); ?>"
                    class="bc-icon-btn bc-icon-btn--filled"
                    aria-label="<?php esc_attr_e( 'View collection', 'ascendum-brand-center' ); ?>"
                >
                    <?php abc_icon( 'arrow--up-right' ); ?>
                </a>

                <?php if ( $download_all && ! empty( $download_all['url'] ) ) : ?>
                <a
                    href="<?php echo esc_url( $download_all['url'] ); ?>"
                    class="bc-icon-btn bc-icon-btn--outline"
                    download
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="<?php esc_attr_e( 'Download collection', 'ascendum-brand-center' ); ?>"
                >
                    <?php abc_icon( 'download' ); ?>
                </a>
                <?php endif; ?>
            </div><!-- /.bc-card-actions -->

        </div><!-- /.bc-card-info -->

    </div><!-- /.bc-card -->

</div><!-- /.block-collection -->
