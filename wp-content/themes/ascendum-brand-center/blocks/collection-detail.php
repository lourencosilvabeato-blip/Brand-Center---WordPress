<?php
/**
 * Block: Collection Detail
 *
 * Two-panel interactive viewer — left: preview + filmstrip, right: info panel.
 * Accessed via ?collection=N on the parent page.
 *
 * Left panel: back button (absolute circle), asset description text + image side
 * by side, navigation pill < N / total >, filmstrip with arrow buttons.
 * Inactive thumbnails carry a dark overlay; active thumbnail has no overlay.
 * Per-asset download: overlay circle button on the image (top-right).
 *
 * Right panel: full-height, --bg-lightest-grey. Title (H4), label + chip row,
 * description (flex: 1), outlined "Download collection" button at bottom.
 *
 * Asset data is embedded as JSON for collection-detail.js.
 *
 * $block is set by abc_render_collection_detail() in inc/blocks.php.
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

$title        = trim( $block['collection_title']       ?? '' );
$label        = trim( $block['collection_label']       ?? '' );
$description  = trim( $block['collection_description'] ?? '' );
$assets       = is_array( $block['collection_assets']  ?? null ) ? $block['collection_assets'] : array();
$download_all = is_array( $block['download_all_file']  ?? null ) ? $block['download_all_file'] : null;
$back_url     = get_permalink();

$total = count( $assets );

if ( empty( $assets ) ) {
    return;
}

// First asset — server-rendered initial state.
$first_img  = is_array( $assets[0]['asset_image'] ?? null ) ? $assets[0]['asset_image'] : null;
$first_file = is_array( $assets[0]['asset_file']  ?? null ) ? $assets[0]['asset_file']  : null;
$first_desc = trim( $assets[0]['asset_description'] ?? '' );

// Asset data for JS.
$cd_json = array_map(
    function ( $asset ) {
        $img  = is_array( $asset['asset_image'] ?? null ) ? $asset['asset_image'] : null;
        $file = is_array( $asset['asset_file']  ?? null ) ? $asset['asset_file']  : null;
        return array(
            'src'  => $img  ? $img['url']          : '',
            'alt'  => $img  ? ( $img['alt'] ?? '' ) : '',
            'desc' => trim( $asset['asset_description'] ?? '' ),
            'file' => $file ? $file['url']          : '',
        );
    },
    $assets
);
?>
<div class="collection-detail">

    <!-- Back button — circle, absolutely positioned top-left of panel -->
    <a
        href="<?php echo esc_url( $back_url ); ?>"
        class="collection-detail-back"
        aria-label="<?php esc_attr_e( 'Back', 'ascendum-brand-center' ); ?>"
    >
        <?php abc_icon( 'chevron-left-20' ); ?>
    </a>

    <div class="collection-detail-viewer">

        <!-- Left: preview area -->
        <div class="collection-detail-left">

            <!-- Asset: description text (left) + image with download overlay (right) -->
            <div class="collection-detail-asset">

                <div class="collection-detail-asset-text rich-text" id="cd-asset-text">
                    <?php echo wp_kses_post( $first_desc ); ?>
                </div>

                <div class="collection-detail-asset-img-wrap">
                    <img
                        id="cd-preview-img"
                        src="<?php echo $first_img ? esc_url( $first_img['url'] ) : ''; ?>"
                        alt="<?php echo $first_img ? esc_attr( $first_img['alt'] ?? '' ) : ''; ?>"
                    >
                    <a
                        id="cd-asset-dl"
                        class="cd-asset-download-btn"
                        href="<?php echo ( $first_file && ! empty( $first_file['url'] ) ) ? esc_url( $first_file['url'] ) : '#'; ?>"
                        download
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="<?php esc_attr_e( 'Download asset', 'ascendum-brand-center' ); ?>"
                        <?php echo ( ! $first_file || empty( $first_file['url'] ) ) ? 'hidden' : ''; ?>
                    >
                        <?php abc_icon( 'download' ); ?>
                    </a>
                </div><!-- /.collection-detail-asset-img-wrap -->

            </div><!-- /.collection-detail-asset -->

            <!-- Navigation pill: < N / total > -->
            <div class="collection-detail-nav">
                <div class="collection-detail-nav-pill">
                    <button
                        type="button"
                        id="cd-prev"
                        class="cd-pag-btn"
                        aria-label="<?php esc_attr_e( 'Previous asset', 'ascendum-brand-center' ); ?>"
                    >
                        <?php abc_icon( 'chevron-left-20' ); ?>
                    </button>
                    <span class="cd-pag-counter">
                        <span id="cd-current">1</span> / <span id="cd-total"><?php echo absint( $total ); ?></span>
                    </span>
                    <button
                        type="button"
                        id="cd-next"
                        class="cd-pag-btn"
                        aria-label="<?php esc_attr_e( 'Next asset', 'ascendum-brand-center' ); ?>"
                    >
                        <?php abc_icon( 'chevron-right-20' ); ?>
                    </button>
                </div>
            </div><!-- /.collection-detail-nav -->

            <!-- Filmstrip with scroll arrows -->
            <div class="collection-detail-filmstrip-wrap">

                <button
                    type="button"
                    class="cd-film-arrow cd-film-arrow--prev"
                    aria-label="<?php esc_attr_e( 'Scroll filmstrip left', 'ascendum-brand-center' ); ?>"
                >
                    <?php abc_icon( 'chevron-left-20' ); ?>
                </button>

                <div class="collection-detail-filmstrip" id="cd-filmstrip" role="list">
                    <?php foreach ( $assets as $i => $asset ) :
                        $thumb = is_array( $asset['asset_image'] ?? null ) ? $asset['asset_image'] : null;
                    ?>
                    <button
                        type="button"
                        class="cd-film-thumb<?php echo 0 === $i ? ' is-active' : ''; ?>"
                        data-index="<?php echo absint( $i ); ?>"
                        aria-label="<?php echo esc_attr( sprintf( __( 'Asset %d', 'ascendum-brand-center' ), $i + 1 ) ); ?>"
                        role="listitem"
                    >
                        <?php if ( $thumb && ! empty( $thumb['url'] ) ) : ?>
                        <img
                            src="<?php echo esc_url( $thumb['url'] ); ?>"
                            alt="<?php echo esc_attr( $thumb['alt'] ?? '' ); ?>"
                            loading="lazy"
                        >
                        <?php endif; ?>
                    </button>
                    <?php endforeach; ?>
                </div><!-- /#cd-filmstrip -->

                <button
                    type="button"
                    class="cd-film-arrow cd-film-arrow--next"
                    aria-label="<?php esc_attr_e( 'Scroll filmstrip right', 'ascendum-brand-center' ); ?>"
                >
                    <?php abc_icon( 'chevron-right-20' ); ?>
                </button>

            </div><!-- /.collection-detail-filmstrip-wrap -->

        </div><!-- /.collection-detail-left -->

        <!-- Right: info panel — full height, grey bg -->
        <aside class="collection-detail-info">

            <h2 class="collection-detail-title"><?php echo esc_html( $title ); ?></h2>

            <?php if ( $label || $total ) : ?>
            <div class="collection-detail-meta">
                <?php if ( $label ) : ?>
                <span class="collection-detail-label"><?php echo esc_html( $label ); ?></span>
                <?php endif; ?>
                <span class="collection-detail-chip">
                    <?php printf(
                        /* translators: %d: number of assets */
                        esc_html( _n( '%d asset', '%d assets', $total, 'ascendum-brand-center' ) ),
                        $total
                    ); ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if ( $description ) : ?>
            <div class="collection-detail-desc rich-text"><?php echo wp_kses_post( $description ); ?></div>
            <?php endif; ?>

            <?php if ( $download_all && ! empty( $download_all['url'] ) ) : ?>
            <a
                href="<?php echo esc_url( $download_all['url'] ); ?>"
                class="collection-detail-download-btn"
                download
                target="_blank"
                rel="noopener noreferrer"
            >
                <?php esc_html_e( 'Download collection', 'ascendum-brand-center' ); ?>
            </a>
            <?php endif; ?>

        </aside><!-- /.collection-detail-info -->

    </div><!-- /.collection-detail-viewer -->

    <!-- Asset data for collection-detail.js -->
    <script id="cd-data" type="application/json">
    <?php echo wp_json_encode( $cd_json ); ?>
    </script>

</div><!-- /.collection-detail -->
