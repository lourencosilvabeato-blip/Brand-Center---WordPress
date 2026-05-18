<?php
/**
 * Template Name: Generic Content
 *
 * C02 / E02 — Authenticated interior content page.
 * Layout: breadcrumb (full-width) → 3-column row: left sidebar | main content | anchor bar.
 *
 * Content is assembled from ACF flexible content layouts (lego blocks).
 * When ?collection=N is present, renders the detail view for that collection.
 *
 * Anchor bar rules (from Miro spec):
 *  - Only renders when ≥1 Section Top block exists.
 *  - When rendered, Page Header is always the first entry.
 *  - Section Top blocks provide subsequent entries.
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

// ----- Collection detail mode ------------------------------------------------
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$collection_index = isset( $_GET['collection'] ) ? (int) $_GET['collection'] : -1;

// ----- Flexible content blocks -----------------------------------------------
$blocks = array();
if ( function_exists( 'get_field' ) ) {
    $raw    = get_field( 'page_content_blocks' );
    $blocks = is_array( $raw ) ? $raw : array();
}

// ----- Build anchor list -------------------------------------------------------
// Page title is always the first anchor entry (auto-generated from WP page title).
// Anchor bar only renders if ≥1 Section Top block exists.
$section_anchors = array();

foreach ( $blocks as $block ) {
    $layout = $block['acf_fc_layout'] ?? '';

    if ( 'section_top' === $layout ) {
        $st_title  = trim( $block['section_title'] ?? '' );
        $st_anchor = sanitize_title( trim( $block['section_anchor'] ?? $st_title ) );
        if ( $st_title && $st_anchor ) {
            $section_anchors[] = array(
                'label'  => $st_title,
                'anchor' => $st_anchor,
            );
        }
    }
}

$has_anchor_bar   = ! empty( $section_anchors );
$page_title       = get_the_title();
$page_title_anchor = sanitize_title( $page_title );

$anchors = array();
if ( $has_anchor_bar ) {
    $anchors[] = array(
        'label'  => $page_title,
        'anchor' => $page_title_anchor,
    );
    foreach ( $section_anchors as $a ) {
        $anchors[] = $a;
    }
}

get_header();
?>

<div class="content-page-wrap">

    <?php abc_render_breadcrumb(); ?>

    <div class="content-page-layout<?php echo ( $has_anchor_bar && $collection_index < 0 ) ? ' has-anchor-bar' : ''; ?><?php echo $collection_index >= 0 ? ' is-collection-detail' : ''; ?>">

        <?php if ( $collection_index < 0 ) abc_render_left_sidebar(); ?>

        <main id="main-content" class="content-page-main">

            <?php if ( $collection_index >= 0 ) :
                abc_render_collection_detail( $blocks, $collection_index );
            else : ?>

                <!-- Auto-generated page title from WordPress page settings -->
                <div
                    class="block-page-header"
                    <?php if ( $page_title_anchor ) : ?>id="<?php echo esc_attr( $page_title_anchor ); ?>"<?php endif; ?>
                >
                    <h1 class="block-page-header-title"><?php echo esc_html( $page_title ); ?></h1>
                </div>

                <?php
                // ----- Render blocks -------------------------------------------
                // Track consecutive small-model collection blocks for side-by-side layout.
                $pending_small_col = null;

                foreach ( $blocks as $index => $block ) :
                    $layout   = $block['acf_fc_layout'] ?? '';
                    $tpl_name = str_replace( '_', '-', $layout );
                    $tpl_path = get_template_directory() . '/blocks/' . $tpl_name . '.php';

                    if ( ! $layout || ! file_exists( $tpl_path ) ) {
                        continue;
                    }

                    // Side-by-side logic for two consecutive small Collections.
                    if ( 'collection' === $layout && 'small' === ( $block['card_model'] ?? 'large' ) ) {
                        if ( null === $pending_small_col ) {
                            // Hold first small card — check next block.
                            $pending_small_col = array( 'block' => $block, 'tpl' => $tpl_path );
                            continue;
                        } else {
                            // Second consecutive small card — wrap both side by side.
                            echo '<div class="collection-pair">';
                            $block_saved = $pending_small_col['block'];
                            $tpl_saved   = $pending_small_col['tpl'];
                            $block = $block_saved; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                            include $tpl_saved;
                            $block = $blocks[ $index ]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                            include $tpl_path;
                            echo '</div>';
                            $pending_small_col = null;
                            continue;
                        }
                    } else {
                        // Flush any orphan pending small card.
                        if ( null !== $pending_small_col ) {
                            $block_saved = $pending_small_col['block'];
                            $tpl_saved   = $pending_small_col['tpl'];
                            $block = $block_saved; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                            include $tpl_saved;
                            $pending_small_col = null;
                            $block = $blocks[ $index ]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                        }
                        include $tpl_path;
                    }
                endforeach;

                // Flush any trailing orphan small collection.
                if ( null !== $pending_small_col ) {
                    $block    = $pending_small_col['block'];
                    $tpl_path = $pending_small_col['tpl'];
                    include $tpl_path;
                }
                ?>

                <?php abc_render_sibling_nav_buttons(); ?>

            <?php endif; ?>

        </main><!-- /.content-page-main -->

        <?php if ( $has_anchor_bar && $collection_index < 0 ) : ?>
        <aside
            class="anchor-bar"
            aria-label="<?php esc_attr_e( 'Page sections', 'ascendum-brand-center' ); ?>"
        >
            <ul class="anchor-bar-list" role="list">
                <?php foreach ( $anchors as $anchor_item ) : ?>
                <li>
                    <a
                        href="#<?php echo esc_attr( $anchor_item['anchor'] ); ?>"
                        class="anchor-bar-link"
                    >
                        <?php echo esc_html( $anchor_item['label'] ); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <?php endif; ?>

    </div><!-- /.content-page-layout -->

</div><!-- /.content-page-wrap -->

<?php get_footer(); ?>
