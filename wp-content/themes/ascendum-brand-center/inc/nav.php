<?php
/**
 * Navigation helper functions — Ascendum Brand Center
 *
 * Provides helpers for:
 *  - Primary menu tree (3-level)
 *  - Mega-menu column distribution (8-line rule)
 *  - Breadcrumb (nav-menu-membership based)
 *  - Profile dropdown items (role-based)
 *  - Left sidebar sibling items
 *  - SVG icon output
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Menu tree
// ---------------------------------------------------------------------------

/**
 * Returns all items from the primary nav menu as a flat array.
 *
 * @return WP_Post[]
 */
function abc_get_primary_menu_items() {
    $locations = get_nav_menu_locations();
    if ( empty( $locations['primary'] ) ) {
        return array();
    }
    $menu = wp_get_nav_menu_object( $locations['primary'] );
    if ( ! $menu ) {
        return array();
    }
    return wp_get_nav_menu_items( $menu->term_id ) ?: array();
}

/**
 * Builds a 3-level nested tree of primary menu items.
 *
 * Tree shape:
 *  [ [ 'item' => WP_Post, 'children' => [
 *        [ 'item' => WP_Post, 'children' => [ WP_Post, ... ] ],
 *        ...
 *  ]], ... ]
 *
 * L2 with no children still appear as nodes (leaf L2 = bold, occupies 1 line).
 *
 * @return array
 */
function abc_get_menu_tree() {
    $flat = abc_get_primary_menu_items();
    if ( empty( $flat ) ) {
        return array();
    }

    // Build lookup tables.
    $indexed = array();
    foreach ( $flat as $item ) {
        $indexed[ $item->ID ] = array(
            'item'     => $item,
            'children' => array(),
        );
    }

    $tree = array();
    foreach ( $flat as $item ) {
        $pid = (int) $item->menu_item_parent;
        if ( 0 === $pid ) {
            // L1 — root level.
            $tree[] = &$indexed[ $item->ID ];
        } elseif ( isset( $indexed[ $pid ] ) ) {
            $grandpid = (int) $indexed[ $pid ]['item']->menu_item_parent;
            if ( 0 === $grandpid ) {
                // L2 — parent is L1.
                $indexed[ $pid ]['children'][] = &$indexed[ $item->ID ];
            } else {
                // L3 — parent is L2; store the raw item (no deeper nesting).
                $indexed[ $pid ]['children'][] = $item;
            }
        }
    }

    return $tree;
}

// ---------------------------------------------------------------------------
// Mega-menu: 8-line column distribution
// ---------------------------------------------------------------------------

/**
 * Distributes L2 groups into columns obeying the 8-line rule.
 *
 * Lines per group = 1 (L2 heading) + count(L3 links).
 * Groups are never split. If adding a group would exceed 8 lines, a new column
 * starts. A group that alone exceeds 8 lines gets its own column.
 *
 * @param  array $l2_groups  L2 nodes from abc_get_menu_tree() children.
 * @return array[]           Array of columns, each an array of L2 nodes.
 */
function abc_distribute_menu_columns( $l2_groups ) {
    $columns       = array();
    $current_col   = array();
    $current_lines = 0;

    foreach ( $l2_groups as $group ) {
        $group_lines = 1 + ( is_array( $group['children'] ) ? count( $group['children'] ) : 0 );

        if ( $current_lines > 0 && ( $current_lines + $group_lines ) > 8 ) {
            $columns[]     = $current_col;
            $current_col   = array();
            $current_lines = 0;
        }

        $current_col[]  = $group;
        $current_lines += $group_lines;
    }

    if ( ! empty( $current_col ) ) {
        $columns[] = $current_col;
    }

    return $columns;
}

// ---------------------------------------------------------------------------
// Active-state helpers
// ---------------------------------------------------------------------------

/**
 * Returns true if the current page is the given menu item's target page,
 * or is a descendant of it in the menu.
 *
 * @param  array $node  Tree node with 'item' and 'children' keys.
 * @return bool
 */
function abc_menu_node_is_active( $node ) {
    $post_id = (int) get_the_ID();
    if ( ! $post_id ) {
        return false;
    }

    $item         = $node['item'];
    $item_page_id = (int) $item->object_id;

    // Direct match: current page IS this menu item.
    if ( $item_page_id && $item_page_id === $post_id ) {
        return true;
    }

    // For page-type items: use WordPress page ancestry — reliable regardless of
    // how pages are arranged or duplicated in the menu.
    if ( $item_page_id && 'post_type' === $item->type ) {
        return in_array( $item_page_id, get_post_ancestors( $post_id ), true );
    }

    // Custom-link L1 items (object_id = 0): fall back to recursive menu-tree check.
    if ( ! $item_page_id && ! empty( $node['children'] ) ) {
        foreach ( $node['children'] as $child ) {
            if ( is_array( $child ) ) {
                if ( abc_menu_node_is_active( $child ) ) {
                    return true;
                }
            } else {
                if ( (int) $child->object_id === $post_id ) {
                    return true;
                }
            }
        }
    }

    return false;
}

// ---------------------------------------------------------------------------
// Breadcrumb
// ---------------------------------------------------------------------------

/**
 * Returns breadcrumb items for the current page, based on nav-menu membership.
 *
 * Returns empty array if the current page is not in the primary menu.
 * The last item always has url = null (non-clickable current page).
 *
 * @return array[]  [ [ 'label' => string, 'url' => string|null ], ... ]
 */
function abc_get_breadcrumb_items() {
    if ( ! is_singular() ) {
        return array();
    }

    $post_id = (int) get_the_ID();
    $flat    = abc_get_primary_menu_items();
    if ( empty( $flat ) ) {
        return array();
    }

    // Verify current page is in the primary nav menu (visibility rule).
    $in_menu = false;
    foreach ( $flat as $item ) {
        if ( (int) $item->object_id === $post_id ) {
            $in_menu = true;
            break;
        }
    }
    if ( ! $in_menu ) {
        return array();
    }

    // Build page_id → first menu item map (first occurrence in menu order wins).
    $by_object = array();
    foreach ( $flat as $item ) {
        $oid = (int) $item->object_id;
        if ( $oid > 0 && ! isset( $by_object[ $oid ] ) ) {
            $by_object[ $oid ] = $item;
        }
    }

    // Use WordPress page hierarchy for the chain — immune to menu duplicates or
    // incorrect menu nesting. The chain is: [root ancestor, ..., current page].
    $ancestors  = array_reverse( get_post_ancestors( $post_id ) );
    $page_chain = array_merge( $ancestors, array( $post_id ) );

    $breadcrumb = array(
        array( 'label' => __( 'Home', 'ascendum-brand-center' ), 'url' => abc_homepage_url() ),
    );

    foreach ( $page_chain as $pid ) {
        // Only include pages that are present in the nav menu.
        if ( ! isset( $by_object[ $pid ] ) ) {
            continue;
        }
        $item    = $by_object[ $pid ];
        $is_last = ( (int) $pid === $post_id );
        $breadcrumb[] = array(
            'label' => $item->title,
            'url'   => $is_last ? null : get_permalink( $pid ),
        );
    }

    return count( $breadcrumb ) > 1 ? $breadcrumb : array();
}

// ---------------------------------------------------------------------------
// Profile dropdown
// ---------------------------------------------------------------------------

/**
 * Returns profile dropdown menu items for the current user.
 *
 * Items differ by role:
 *  - administrator : Invite new user, Go to management panel, Logout
 *  - local_admin   : Invite new user, Logout
 *  - internal_user : Logout
 *  - external_user : Change Password, Logout
 *
 * @return array[]  [ [ 'label' => string, 'url' => string, 'icon' => string ], ... ]
 */
function abc_get_profile_dropdown_items() {
    $user  = wp_get_current_user();
    $roles = (array) $user->roles;
    $items = array();

    // Only Local Admin sees the frontend invite form (Admin manages users directly in BO).
    if ( in_array( 'local_admin', $roles, true ) ) {
        $items[] = array(
            'label' => __( 'Invite new user', 'ascendum-brand-center' ),
            'url'   => home_url( '/invite-user/' ),
            'icon'  => 'user-plus',
        );
    }

    if ( in_array( 'administrator', $roles, true ) ) {
        $items[] = array(
            'label' => __( 'Go to management panel', 'ascendum-brand-center' ),
            'url'   => admin_url(),
            'icon'  => 'settings',
        );
    }

    if ( in_array( 'external_user', $roles, true ) ) {
        $items[] = array(
            'label' => __( 'Change Password', 'ascendum-brand-center' ),
            'url'   => home_url( '/change-password/' ),
            'icon'  => 'lock',
        );
    }

    $items[] = array(
        'label' => __( 'Logout', 'ascendum-brand-center' ),
        'url'   => abc_get_logout_url(),
        'icon'  => 'logout',
    );

    return $items;
}

// ---------------------------------------------------------------------------
// Left sidebar items
// ---------------------------------------------------------------------------

/**
 * Returns sibling page items (same parent in the nav menu) for the left sidebar.
 *
 * @return array[]  [ [ 'label' => string, 'url' => string, 'is_current' => bool ], ... ]
 */
function abc_get_sidebar_nav_items() {
    $post_id   = (int) get_the_ID();
    $flat      = abc_get_primary_menu_items();
    $by_object = array();
    $by_id     = array();

    foreach ( $flat as $item ) {
        $by_object[ (int) $item->object_id ] = $item;
        $by_id[ $item->ID ]                  = $item;
    }

    if ( ! isset( $by_object[ $post_id ] ) ) {
        return array();
    }

    $current   = $by_object[ $post_id ];
    $parent_id = (int) $current->menu_item_parent;
    $siblings  = array();

    foreach ( $flat as $item ) {
        if ( (int) $item->menu_item_parent === $parent_id ) {
            $siblings[] = array(
                'id'         => (int) $item->object_id,
                'label'      => $item->title,
                'url'        => $item->url,
                'is_current' => ( (int) $item->object_id === $post_id ),
            );
        }
    }

    return $siblings;
}

/**
 * Outputs the left sidebar HTML.
 * Call this from page templates where the sidebar should appear.
 * Returns early (outputs nothing) if the current page is not in the primary nav menu.
 */
function abc_render_left_sidebar() {
    // Only render for pages that are part of the primary nav menu.
    $post_id = (int) get_the_ID();
    $flat    = abc_get_primary_menu_items();
    $in_menu = false;
    foreach ( $flat as $item ) {
        if ( (int) $item->object_id === $post_id ) {
            $in_menu = true;
            break;
        }
    }
    if ( ! $in_menu ) {
        return;
    }

    $items = abc_get_sidebar_nav_items();

    // Navigation Tips and FAQs: resolved by page slug — no ACF options page required.
    $nav_tips_page = get_page_by_path( 'navigation-tips' );
    $faqs_page     = get_page_by_path( 'faqs' );
    $nav_tips_url  = $nav_tips_page ? get_permalink( $nav_tips_page->ID ) : '';
    $faqs_url      = $faqs_page     ? get_permalink( $faqs_page->ID )     : '';
    ?>
    <aside class="left-sidebar" aria-label="<?php esc_attr_e( 'Section navigation', 'ascendum-brand-center' ); ?>">

        <!-- Search (submits to WP native search; updated to D01 URL when built) -->
        <form
            role="search"
            method="get"
            action="<?php echo esc_url( home_url( '/' ) ); ?>"
            class="left-sidebar-search"
            aria-label="<?php esc_attr_e( 'Search site', 'ascendum-brand-center' ); ?>"
        >
            <label class="screen-reader-text" for="sidebar-search">
                <?php esc_html_e( 'Search', 'ascendum-brand-center' ); ?>
            </label>
            <input
                type="search"
                id="sidebar-search"
                name="s"
                class="left-sidebar-search-input"
                placeholder="<?php esc_attr_e( 'Search', 'ascendum-brand-center' ); ?>"
                value="<?php echo esc_attr( get_search_query() ); ?>"
                autocomplete="off"
            >
            <button
                type="submit"
                class="left-sidebar-search-btn"
                aria-label="<?php esc_attr_e( 'Submit search', 'ascendum-brand-center' ); ?>"
            >
                <?php abc_icon( 'search' ); ?>
            </button>
        </form>

        <!-- Sibling pages -->
        <?php if ( ! empty( $items ) ) : ?>
        <nav class="left-sidebar-nav" aria-label="<?php esc_attr_e( 'Pages in this section', 'ascendum-brand-center' ); ?>">
            <ul class="left-sidebar-list" role="list">
                <?php foreach ( $items as $item ) : ?>
                <li class="left-sidebar-item<?php echo $item['is_current'] ? ' is-active' : ''; ?>">
                    <a
                        href="<?php echo esc_url( $item['url'] ); ?>"
                        class="left-sidebar-link"
                        <?php echo $item['is_current'] ? 'aria-current="page"' : ''; ?>
                    >
                        <?php echo esc_html( $item['label'] ); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <!-- Fixed utility links — always below siblings -->
        <?php if ( $nav_tips_url || $faqs_url ) : ?>
        <nav class="left-sidebar-utility" aria-label="<?php esc_attr_e( 'Resources', 'ascendum-brand-center' ); ?>">
            <ul class="left-sidebar-list" role="list">
                <?php if ( $nav_tips_url ) : ?>
                <li class="left-sidebar-item">
                    <a href="<?php echo esc_url( $nav_tips_url ); ?>" class="left-sidebar-link">
                        <?php esc_html_e( 'Navigation Tips', 'ascendum-brand-center' ); ?>
                    </a>
                </li>
                <?php endif; ?>
                <?php if ( $faqs_url ) : ?>
                <li class="left-sidebar-item">
                    <a href="<?php echo esc_url( $faqs_url ); ?>" class="left-sidebar-link">
                        <?php esc_html_e( 'FAQs', 'ascendum-brand-center' ); ?>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

    </aside>
    <?php
}

/**
 * Outputs the right anchor bar HTML for the current page.
 *
 * Reads anchor definitions from the 'page_anchors' ACF textarea field.
 * Each non-empty line must follow the format: Section Label|section-id
 * Lines that don't match the format are silently skipped.
 *
 * Returns early (no output) when no valid anchors are defined — the page
 * template should then let the main content area expand to full width.
 */
function abc_render_anchor_bar() {
    if ( ! function_exists( 'get_field' ) ) {
        return;
    }

    $raw = get_field( 'page_anchors' );
    if ( empty( $raw ) ) {
        return;
    }

    $anchors = array();
    foreach ( explode( "\n", $raw ) as $line ) {
        $line = trim( $line );
        if ( '' === $line ) {
            continue;
        }
        $parts = explode( '|', $line, 2 );
        if ( 2 !== count( $parts ) ) {
            continue;
        }
        $label = trim( $parts[0] );
        $id    = sanitize_html_class( trim( $parts[1] ) );
        if ( $label && $id ) {
            $anchors[] = array(
                'label' => $label,
                'id'    => $id,
            );
        }
    }

    if ( empty( $anchors ) ) {
        return;
    }
    ?>
    <nav class="anchor-bar" aria-label="<?php esc_attr_e( 'On this page', 'ascendum-brand-center' ); ?>">
        <ul class="anchor-bar-list" role="list">
            <?php foreach ( $anchors as $anchor ) : ?>
            <li class="anchor-bar-item">
                <a href="#<?php echo esc_attr( $anchor['id'] ); ?>" class="anchor-bar-link">
                    <?php echo esc_html( $anchor['label'] ); ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php
}

/**
 * Outputs the breadcrumb HTML for the current page.
 * Outputs nothing if the page is not in the primary nav menu.
 */
function abc_render_breadcrumb() {
    $items = abc_get_breadcrumb_items();
    if ( empty( $items ) ) {
        return;
    }
    ?>
    <nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ascendum-brand-center' ); ?>">
        <ol class="breadcrumb-list" role="list">
            <?php foreach ( $items as $i => $crumb ) :
                $is_last = ( $i === count( $items ) - 1 );
            ?>
            <li class="breadcrumb-item<?php echo $is_last ? ' is-current' : ''; ?>">
                <?php if ( ! $is_last ) : ?>
                    <a href="<?php echo esc_url( $crumb['url'] ); ?>" class="breadcrumb-link"><?php echo esc_html( $crumb['label'] ); ?></a>
                    <span class="breadcrumb-separator" aria-hidden="true"><?php abc_icon( 'chevron-right' ); ?></span>
                <?php else : ?>
                    <span class="breadcrumb-current" aria-current="page"><?php echo esc_html( $crumb['label'] ); ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ol>
    </nav>
    <?php
}

// ---------------------------------------------------------------------------
// Search filter items (Homepage B01)
// ---------------------------------------------------------------------------

/**
 * Returns nav menu items that are enabled as homepage search filter options.
 *
 * Each item has `_abc_search_filter` post meta set to '1' by the admin
 * via the Appearance → Menus editor checkbox.
 *
 * @return array[]  [ [ 'id' => int (menu item ID), 'label' => string, 'object_id' => int ], ... ]
 */
function abc_get_search_filter_items() {
    $flat    = abc_get_primary_menu_items();
    $filters = array();
    foreach ( $flat as $item ) {
        if ( '1' === get_post_meta( $item->ID, '_abc_search_filter', true ) ) {
            $filters[] = array(
                'id'        => (int) $item->ID,
                'label'     => $item->title,
                'object_id' => (int) $item->object_id,
            );
        }
    }
    return $filters;
}

// ---------------------------------------------------------------------------
// Search helpers (D01)
// ---------------------------------------------------------------------------

/**
 * Returns all published descendant page IDs (children, grandchildren, etc.)
 * of a given page, used to scope search results to a menu section.
 *
 * @param int $parent_id Parent post ID.
 * @return int[]
 */
function abc_get_descendant_page_ids( int $parent_id ) : array {
    $pages = get_pages( array(
        'child_of'    => $parent_id,
        'post_status' => 'publish',
        'number'      => 0,
    ) );
    return array_column( $pages ? $pages : array(), 'ID' );
}

/**
 * Returns breadcrumb parts for a search result item.
 *
 * Format:
 *  'path'  => 'L1 / L2 /'   (SemiBold ancestors, empty string if page is at root level)
 *  'title' => ' Page Title'  (Regular, with leading space)
 *
 * Returns empty array when the page is not in the primary nav menu.
 *
 * @param int $post_id Post ID.
 * @return array{'path': string, 'title': string}|array{}
 */
function abc_get_search_result_breadcrumb_parts( int $post_id ) : array {
    $flat = abc_get_primary_menu_items();
    if ( empty( $flat ) ) {
        return array();
    }

    $by_object = array();
    foreach ( $flat as $item ) {
        $oid = (int) $item->object_id;
        if ( $oid > 0 && ! isset( $by_object[ $oid ] ) ) {
            $by_object[ $oid ] = $item;
        }
    }

    if ( ! isset( $by_object[ $post_id ] ) ) {
        return array();
    }

    $ancestors  = array_reverse( get_post_ancestors( $post_id ) );
    $page_chain = array_merge( $ancestors, array( $post_id ) );

    $ancestor_labels = array();
    $title_label     = '';

    foreach ( $page_chain as $pid ) {
        if ( ! isset( $by_object[ $pid ] ) ) {
            continue;
        }
        if ( (int) $pid === (int) $post_id ) {
            $title_label = $by_object[ $pid ]->title;
        } else {
            $ancestor_labels[] = $by_object[ $pid ]->title;
        }
    }

    if ( ! $title_label ) {
        return array();
    }

    $path = ! empty( $ancestor_labels )
        ? implode( ' / ', $ancestor_labels ) . ' /'
        : '';

    return array(
        'path'  => $path,
        'title' => ' ' . $title_label,
    );
}

/**
 * Returns a plain-text breadcrumb path for a given page ID.
 * Format: "Home › L1 › L2 › Page Title"
 * Returns empty string when the page is not in the primary nav menu.
 *
 * Used to show navigation context inside D01 search result items.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function abc_get_search_result_breadcrumb_text( int $post_id ) : string {
    $flat = abc_get_primary_menu_items();
    if ( empty( $flat ) ) {
        return '';
    }

    // Build object_id → menu item map (first occurrence wins).
    $by_object = array();
    foreach ( $flat as $item ) {
        $oid = (int) $item->object_id;
        if ( $oid > 0 && ! isset( $by_object[ $oid ] ) ) {
            $by_object[ $oid ] = $item;
        }
    }

    // Only show breadcrumb for pages that are in the main nav menu.
    if ( ! isset( $by_object[ $post_id ] ) ) {
        return '';
    }

    // Build chain from root ancestors to current page.
    $ancestors  = array_reverse( get_post_ancestors( $post_id ) );
    $page_chain = array_merge( $ancestors, array( $post_id ) );

    $parts = array( __( 'Home', 'ascendum-brand-center' ) );
    foreach ( $page_chain as $pid ) {
        if ( isset( $by_object[ $pid ] ) ) {
            $parts[] = $by_object[ $pid ]->title;
        }
    }

    return implode( ' › ', $parts );
}

// ---------------------------------------------------------------------------
// SVG icon helper
// ---------------------------------------------------------------------------

/**
 * Outputs an inline SVG icon by name.
 *
 * @param string $name  Icon name.
 */
function abc_icon( $name ) {
    $icons = array(

        'caret-down' => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

        'caret-up' => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M12 10L8 6L4 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

        'chevron-right' => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

        'chevron-left' => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M10 4L6 8L10 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

        'menu' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

        'close' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

        'close-20' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

        'close-32' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M8 8L24 24M24 8L8 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

        'user-plus' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M13.333 17.5v-1.667A3.333 3.333 0 0 0 10 12.5H4.167a3.333 3.333 0 0 0-3.334 3.333V17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7.083" cy="5.833" r="3.333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M16.667 6.667v5M19.167 9.167h-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

        'settings' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5"/><path d="M10 1.667v1.666M10 16.667v1.666M1.667 10h1.666M16.667 10h1.666M4.108 4.108l1.179 1.178M14.713 14.713l1.179 1.179M4.108 15.892l1.179-1.179M14.713 5.287l1.179-1.179" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

        'lock' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><rect x="2.5" y="9.167" width="15" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M6.667 9.167V6.667a3.333 3.333 0 0 1 6.666 0v2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

        'logout' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M7.5 17.5H4.167A1.667 1.667 0 0 1 2.5 15.833V4.167A1.667 1.667 0 0 1 4.167 2.5H7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.333 14.167 17.5 10l-4.167-4.167M17.5 10h-10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

        'chevron-right-20' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

        'chevron-left-20' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M12.5 5L7.5 10L12.5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

        'search' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><circle cx="9" cy="9" r="5.5" stroke="currentColor" stroke-width="1.5"/><path d="M13.5 13.5L17 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

        'download' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M10 3.333v10M6.667 10 10 13.333 13.333 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M3.333 15h13.334" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

        'arrow-right' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M4.167 10h11.666M10.833 5L15.833 10l-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

        'arrow--up-right' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M5.833 14.167L14.167 5.833M14.167 5.833H8.333M14.167 5.833V11.667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

    );

    if ( isset( $icons[ $name ] ) ) {
        echo $icons[ $name ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
