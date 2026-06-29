<?php
/**
 * Block: Collection Detail
 *
 * Renders the full detail view of a single collection.
 * Accessed via ?collection=N on the parent page.
 * Shows: back link, title, description, full asset list.
 * Sticky "Download collection" button shown at screen bottom if download_all_file is set.
 *
 * $block is set by abc_render_collection_detail() in inc/blocks.php.
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

$title        = trim( $block['collection_title']       ?? '' );
$label        = trim( $block['collection_label']       ?? '' );
$description  = trim( $block['collection_description'] ?? '' );
$assets       = is_array( $block['collection_assets']  ?? null ) ? $block['collection_assets'] : array();
$download_all = is_array( $block['download_all_file']  ?? null ) ? $block['download_all_file'] : null;
$back_url     = get_permalink();
?>
<div class="collection-detail">

    <!-- Back link -->
    <a href="<?php echo esc_url( $back_url ); ?>" class="collection-detail-back">
        <?php abc_icon( 'chevron-left-20' ); ?>
        <?php esc_html_e( 'Back', 'brand-center' ); ?>
    </a>

    <!-- Header -->
    <div class="collection-detail-header">
        <?php if ( $label ) : ?>
        <span class="collection-detail-label"><?php echo esc_html( $label ); ?></span>
        <?php endif; ?>
        <h1 class="collection-detail-title"><?php echo esc_html( $title ); ?></h1>
    </div>

    <!-- Description -->
    <?php if ( $description ) : ?>
    <div class="collection-detail-desc rich-text"><?php echo wp_kses_post( $description ); ?></div>
    <?php endif; ?>

    <!-- Asset list -->
    <?php if ( ! empty( $assets ) ) : ?>
    <ul class="collection-detail-list" role="list">
        <?php foreach ( $assets as $asset ) :
            $img        = is_array( $asset['asset_image'] ?? null ) ? $asset['asset_image'] : null;
            $desc       = trim( $asset['asset_description'] ?? '' );
            $asset_file = is_array( $asset['asset_file'] ?? null ) ? $asset['asset_file'] : null;

            if ( ! $img && ! $desc ) {
                continue;
            }
        ?>
        <li class="collection-detail-item">

            <?php if ( $img && ! empty( $img['url'] ) ) : ?>
            <div class="collection-detail-thumb">
                <img
                    src="<?php echo esc_url( $img['url'] ); ?>"
                    alt="<?php echo esc_attr( $img['alt'] ?? '' ); ?>"
                    loading="lazy"
                >
            </div>
            <?php endif; ?>

            <div class="collection-detail-item-body">
                <?php if ( $desc ) : ?>
                <div class="collection-detail-item-desc rich-text"><?php echo wp_kses_post( $desc ); ?></div>
                <?php endif; ?>

                <?php if ( $asset_file && ! empty( $asset_file['url'] ) ) : ?>
                <a
                    href="<?php echo esc_url( $asset_file['url'] ); ?>"
                    class="collection-detail-item-download"
                    download
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="<?php esc_attr_e( 'Download', 'brand-center' ); ?>"
                >
                    <?php abc_icon( 'download' ); ?>
                </a>
                <?php endif; ?>
            </div>

        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

</div><!-- /.collection-detail -->

<?php if ( $download_all && ! empty( $download_all['url'] ) ) : ?>
<div class="collection-detail-sticky-bar">
    <a
        href="<?php echo esc_url( $download_all['url'] ); ?>"
        class="collection-detail-sticky-btn"
        download
        target="_blank"
        rel="noopener noreferrer"
    >
        <?php abc_icon( 'download' ); ?>
        <?php esc_html_e( 'Download collection', 'brand-center' ); ?>
    </a>
</div>
<?php endif; ?>
