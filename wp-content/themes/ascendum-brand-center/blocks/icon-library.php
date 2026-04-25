<?php
/**
 * Block: Icon Library (Biblioteca de Ícones)
 *
 * Displays a searchable grid of icons (all loaded at once, no pagination).
 * Real-time search filters by icon name via icon-library.js.
 * No-results message: "No icons found matching your search."
 * Optional "Download all" button when a download_all_file is set.
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

$title        = trim( $block['icon_library_title']  ?? '' );
$description  = trim( $block['icon_description']    ?? '' );
$download_all = is_array( $block['icon_download_all'] ?? null ) ? $block['icon_download_all'] : null;
$icons        = is_array( $block['icon_library_icons'] ?? null ) ? $block['icon_library_icons'] : array();

if ( empty( $icons ) && ! $title && ! $description ) {
    return;
}
?>
<div class="block-icon-library">

    <?php if ( $title ) : ?>
    <h3 class="block-icon-library-title"><?php echo esc_html( $title ); ?></h3>
    <?php endif; ?>

    <?php if ( $description ) : ?>
    <div class="block-icon-library-desc rich-text"><?php echo wp_kses_post( $description ); ?></div>
    <?php endif; ?>

    <?php if ( ! empty( $icons ) ) : ?>

    <!-- Search input -->
    <div class="icon-library-search">
        <label class="screen-reader-text" for="icon-library-search-<?php echo esc_attr( get_the_ID() ); ?>">
            <?php esc_html_e( 'Search icons', 'ascendum-brand-center' ); ?>
        </label>
        <input
            type="search"
            id="icon-library-search-<?php echo esc_attr( get_the_ID() ); ?>"
            class="icon-library-search-input"
            placeholder="<?php esc_attr_e( 'Search icons…', 'ascendum-brand-center' ); ?>"
            autocomplete="off"
        >
        <?php abc_icon( 'search' ); ?>
    </div>

    <!-- Icon grid -->
    <div class="icon-library-grid" role="list">
        <?php foreach ( $icons as $icon ) :
            $name  = trim( $icon['icon_name']  ?? '' );
            $img   = is_array( $icon['icon_image'] ?? null ) ? $icon['icon_image'] : null;
            $file  = is_array( $icon['icon_file']  ?? null ) ? $icon['icon_file']  : null;

            if ( ! $name || ! $img || empty( $img['url'] ) ) {
                continue;
            }
        ?>
        <div
            class="icon-library-item"
            data-icon-name="<?php echo esc_attr( strtolower( $name ) ); ?>"
            role="listitem"
        >
            <div class="icon-library-item-img">
                <img
                    src="<?php echo esc_url( $img['url'] ); ?>"
                    alt="<?php echo esc_attr( $img['alt'] ?? $name ); ?>"
                    loading="lazy"
                >
            </div>
            <span class="icon-library-item-name"><?php echo esc_html( $name ); ?></span>
            <?php if ( $file && ! empty( $file['url'] ) ) : ?>
            <a
                href="<?php echo esc_url( $file['url'] ); ?>"
                class="icon-library-item-download"
                download
                target="_blank"
                rel="noopener noreferrer"
                aria-label="<?php echo esc_attr( sprintf( __( 'Download %s', 'ascendum-brand-center' ), $name ) ); ?>"
            >
                <?php abc_icon( 'download' ); ?>
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- No results message (shown by JS) -->
    <p class="icon-library-no-results" hidden role="alert">
        <?php esc_html_e( 'No icons found matching your search.', 'ascendum-brand-center' ); ?>
    </p>

    <?php endif; ?>

    <?php if ( $download_all && ! empty( $download_all['url'] ) ) : ?>
    <div class="icon-library-actions">
        <a
            href="<?php echo esc_url( $download_all['url'] ); ?>"
            class="block-btn block-btn--download"
            download
            target="_blank"
            rel="noopener noreferrer"
        >
            <?php abc_icon( 'download' ); ?>
            <?php esc_html_e( 'Download all', 'ascendum-brand-center' ); ?>
        </a>
    </div>
    <?php endif; ?>

</div><!-- /.block-icon-library -->
