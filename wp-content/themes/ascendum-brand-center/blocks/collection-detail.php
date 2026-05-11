<?php
/**
 * Block: Collection Detail
 *
 * 3-column gallery viewer for a single collection.
 * Left: asset description for the selected asset (updated via JS).
 * Center: large main image + thumbnail strip to cycle through assets.
 * Right: collection label, title, description, download-all button.
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

// Only include assets that have an image with a URL.
$valid_assets = array();
foreach ( $assets as $a ) {
	$img = is_array( $a['asset_image'] ?? null ) ? $a['asset_image'] : null;
	if ( $img && ! empty( $img['url'] ) ) {
		$valid_assets[] = $a;
	}
}

$first_img = ! empty( $valid_assets )
	? ( is_array( $valid_assets[0]['asset_image'] ?? null ) ? $valid_assets[0]['asset_image'] : null )
	: null;
?>
<div class="collection-detail">

	<!-- Back link -->
	<a href="<?php echo esc_url( $back_url ); ?>" class="collection-detail-back">
		<?php abc_icon( 'chevron-left-20' ); ?>
		<?php esc_html_e( 'Back', 'ascendum-brand-center' ); ?>
	</a>

	<!-- 3-column gallery viewer -->
	<div class="collection-viewer" data-collection-viewer>

		<!-- Left: asset description for the active asset -->
		<div class="collection-viewer-left">
			<?php foreach ( $valid_assets as $i => $asset ) :
				$desc       = trim( $asset['asset_description'] ?? '' );
				$asset_file = is_array( $asset['asset_file'] ?? null ) ? $asset['asset_file'] : null;
			?>
			<div
				class="collection-asset-info<?php echo 0 === $i ? ' is-active' : ''; ?>"
				data-asset-desc="<?php echo $i; ?>"
			>
				<?php if ( $desc ) : ?>
				<div class="collection-asset-desc-text rich-text"><?php echo wp_kses_post( $desc ); ?></div>
				<?php endif; ?>

				<?php if ( $asset_file && ! empty( $asset_file['url'] ) ) : ?>
				<a
					href="<?php echo esc_url( $asset_file['url'] ); ?>"
					class="bc-btn bc-btn--download collection-asset-download"
					download
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php abc_icon( 'download' ); ?>
					<?php esc_html_e( 'Download', 'ascendum-brand-center' ); ?>
				</a>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>

		<!-- Center: main image + thumbnail strip -->
		<div class="collection-viewer-center">
			<div class="collection-viewer-main">
				<?php if ( $first_img ) : ?>
				<img
					src="<?php echo esc_url( $first_img['url'] ); ?>"
					alt="<?php echo esc_attr( $first_img['alt'] ?? '' ); ?>"
					class="collection-viewer-img"
					data-main-img
				>
				<?php endif; ?>
			</div>

			<?php if ( count( $valid_assets ) > 1 ) : ?>
			<div class="collection-thumb-strip">
				<?php foreach ( $valid_assets as $i => $asset ) :
					$img = is_array( $asset['asset_image'] ?? null ) ? $asset['asset_image'] : null;
					if ( ! $img || empty( $img['url'] ) ) { continue; }
				?>
				<button
					type="button"
					class="collection-thumb-btn<?php echo 0 === $i ? ' is-active' : ''; ?>"
					data-asset-idx="<?php echo $i; ?>"
					data-src="<?php echo esc_attr( $img['url'] ); ?>"
					data-alt="<?php echo esc_attr( $img['alt'] ?? '' ); ?>"
					aria-label="<?php printf( esc_attr__( 'View asset %d', 'ascendum-brand-center' ), $i + 1 ); ?>"
				>
					<img
						src="<?php echo esc_url( $img['url'] ); ?>"
						alt=""
						loading="lazy"
					>
				</button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>

		<!-- Right: collection info -->
		<div class="collection-viewer-right">
			<?php if ( $label ) : ?>
			<span class="collection-detail-label"><?php echo esc_html( $label ); ?></span>
			<?php endif; ?>

			<h1 class="collection-detail-title"><?php echo esc_html( $title ); ?></h1>

			<?php if ( $description ) : ?>
			<div class="collection-detail-desc rich-text"><?php echo wp_kses_post( $description ); ?></div>
			<?php endif; ?>

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

	</div><!-- /.collection-viewer -->

</div><!-- /.collection-detail -->
