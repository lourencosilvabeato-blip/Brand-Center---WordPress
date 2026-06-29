<?php
/**
 * Header template — site header, mega-menu, profile dropdown, mobile menu.
 *
 * @package brand-center
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Shared data used by both desktop header and mobile menu.
$menu_tree    = abc_get_menu_tree();
$current_user = wp_get_current_user();
$display_name = $current_user->display_name ?: $current_user->user_login;
$avatar_src   = get_avatar_url( $current_user->ID, array( 'size' => 80 ) );
if ( ! $avatar_src ) {
    $avatar_src = get_template_directory_uri() . '/assets/images/avatar-default.png';
}
$dropdown_items = abc_get_profile_dropdown_items();
$logo_src       = get_template_directory_uri() . '/assets/images/logo.svg';
?>

<!-- =========================================================
     Desktop header (hidden ≤ 1024px via CSS)
     ========================================================= -->
<header id="site-header" class="site-header" role="banner">
    <div class="site-header-inner">

        <!-- Logo -->
        <a
            href="<?php echo esc_url( abc_homepage_url() ); ?>"
            class="site-header-logo"
            aria-label="<?php esc_attr_e( 'Brand Center — Home', 'brand-center' ); ?>"
        >
            <img
                src="<?php echo esc_url( $logo_src ); ?>"
                alt="<?php esc_attr_e( 'Brand Center', 'brand-center' ); ?>"
                width="140"
                height="32"
            >
        </a>

        <!-- L1 navigation -->
        <?php if ( ! empty( $menu_tree ) ) : ?>
        <nav class="site-nav" aria-label="<?php esc_attr_e( 'Main navigation', 'brand-center' ); ?>">
            <ul class="site-nav-list" role="list">
                <?php foreach ( $menu_tree as $l1_node ) :
                    $l1_item      = $l1_node['item'];
                    $l1_children  = $l1_node['children'];
                    $has_dropdown = ! empty( $l1_children );
                    $is_active    = abc_menu_node_is_active( $l1_node );
                ?>
                <li class="site-nav-item<?php echo $has_dropdown ? ' has-dropdown' : ''; ?><?php echo $is_active ? ' is-active' : ''; ?>">

                    <a
                        href="<?php echo esc_url( $l1_item->url ); ?>"
                        class="site-nav-link<?php echo $is_active ? ' is-active' : ''; ?>"
                        <?php if ( $has_dropdown ) : ?>
                            aria-haspopup="true"
                            aria-expanded="false"
                        <?php endif; ?>
                    >
                        <?php echo esc_html( $l1_item->title ); ?>
                    </a>

                    <?php if ( $has_dropdown ) :
                        $columns = abc_distribute_menu_columns( $l1_children );
                    ?>
                    <div
                        class="mega-menu"
                        role="region"
                        aria-label="<?php echo esc_attr( $l1_item->title ); ?>"
                    >
                        <div class="mega-menu-inner">
                            <?php foreach ( $columns as $column ) : ?>
                            <div class="mega-menu-column">
                                <?php foreach ( $column as $l2_node ) :
                                    $l2_item     = $l2_node['item'];
                                    $l3_children = $l2_node['children'];
                                ?>
                                <div class="mega-menu-group">
                                    <a
                                        href="<?php echo esc_url( $l2_item->url ); ?>"
                                        class="mega-menu-l2"
                                    >
                                        <?php echo esc_html( $l2_item->title ); ?>
                                    </a>
                                    <?php if ( ! empty( $l3_children ) ) : ?>
                                    <ul class="mega-menu-l3-list" role="list">
                                        <?php foreach ( $l3_children as $l3_raw ) :
                                            // L3 may be a raw WP_Post or a nested node array.
                                            $l3 = is_array( $l3_raw ) ? $l3_raw['item'] : $l3_raw;
                                        ?>
                                        <li>
                                            <a
                                                href="<?php echo esc_url( $l3->url ); ?>"
                                                class="mega-menu-l3"
                                            >
                                                <?php echo esc_html( $l3->title ); ?>
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div><!-- /.mega-menu -->
                    <?php endif; ?>

                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <!-- Profile area -->
        <div class="site-header-profile">
            <button
                type="button"
                class="profile-trigger"
                aria-expanded="false"
                aria-haspopup="true"
                aria-controls="profile-dropdown"
            >
                <span class="profile-avatar" aria-hidden="true">
                    <img src="<?php echo esc_url( $avatar_src ); ?>" alt="">
                </span>
                <span class="profile-welcome"><?php echo esc_html( $display_name ); ?></span>
                <span class="profile-caret" aria-hidden="true"><?php abc_icon( 'caret-down' ); ?></span>
            </button>

            <div id="profile-dropdown" class="profile-dropdown" hidden>
                <ul class="profile-dropdown-list" role="list">
                    <?php foreach ( $dropdown_items as $dd_item ) : ?>
                    <li class="profile-dropdown-item">
                        <a
                            href="<?php echo esc_url( $dd_item['url'] ); ?>"
                            class="profile-dropdown-link"
                        >
                            <?php abc_icon( $dd_item['icon'] ); ?>
                            <?php echo esc_html( $dd_item['label'] ); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div><!-- /.site-header-profile -->

    </div><!-- /.site-header-inner -->
</header><!-- /#site-header -->


<!-- =========================================================
     Mobile header bar (hidden > 1024px via CSS)
     ========================================================= -->
<header class="mobile-header" role="banner">
    <a
        href="<?php echo esc_url( abc_homepage_url() ); ?>"
        class="mobile-header-logo"
        aria-label="<?php esc_attr_e( 'Brand Center — Home', 'brand-center' ); ?>"
    >
        <img
            src="<?php echo esc_url( $logo_src ); ?>"
            alt="<?php esc_attr_e( 'Brand Center', 'brand-center' ); ?>"
            height="28"
        >
    </a>
    <button
        class="mobile-menu-trigger"
        aria-expanded="false"
        aria-controls="mobile-menu"
        aria-label="<?php esc_attr_e( 'Open navigation menu', 'brand-center' ); ?>"
    >
        <?php abc_icon( 'menu' ); ?>
    </button>
</header><!-- /.mobile-header -->


<!-- =========================================================
     Mobile menu overlay (full-screen, drill-down)
     Hidden on desktop via CSS; managed by header.js.
     ========================================================= -->
<div
    id="mobile-menu"
    class="mobile-menu"
    hidden
    role="dialog"
    aria-modal="true"
    aria-label="<?php esc_attr_e( 'Navigation menu', 'brand-center' ); ?>"
>

    <!-- L1 screen ------------------------------------------ -->
    <div class="mobile-menu-l1" data-mobile-screen="l1">

        <div class="mobile-menu-header">
            <div class="mobile-menu-user">
                <span class="mobile-menu-avatar" aria-hidden="true">
                    <img src="<?php echo esc_url( $avatar_src ); ?>" alt="">
                </span>
                <span class="mobile-menu-welcome"><?php echo esc_html( $display_name ); ?></span>
            </div>
            <button
                class="mobile-menu-close"
                aria-label="<?php esc_attr_e( 'Close navigation menu', 'brand-center' ); ?>"
            >
                <?php abc_icon( 'close' ); ?>
                <span><?php esc_html_e( 'Close', 'brand-center' ); ?></span>
            </button>
        </div>

        <div class="mobile-menu-content">
            <ul class="mobile-menu-l1-list" role="list">
                <?php foreach ( $menu_tree as $l1_node ) :
                    $l1_item      = $l1_node['item'];
                    $l1_children  = $l1_node['children'];
                    $has_children = ! empty( $l1_children );
                    $l1_id        = (int) $l1_item->ID;
                ?>
                <li class="mobile-menu-l1-item">
                    <?php if ( $has_children ) : ?>
                    <button
                        class="mobile-menu-l1-link"
                        data-l1-id="<?php echo esc_attr( $l1_id ); ?>"
                    >
                        <?php echo esc_html( $l1_item->title ); ?>
                        <?php abc_icon( 'chevron-right-20' ); ?>
                    </button>
                    <?php else : ?>
                    <a
                        href="<?php echo esc_url( $l1_item->url ); ?>"
                        class="mobile-menu-l1-link"
                    >
                        <?php echo esc_html( $l1_item->title ); ?>
                    </a>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="mobile-menu-actions">
                <?php if ( in_array( 'local_admin', (array) $current_user->roles, true )
                        || in_array( 'administrator', (array) $current_user->roles, true ) ) : ?>
                <a href="<?php echo esc_url( home_url( '/invite-user/' ) ); ?>" class="mobile-menu-action">
                    <?php abc_icon( 'user-plus' ); ?>
                    <?php esc_html_e( 'Invite new user', 'brand-center' ); ?>
                </a>
                <?php endif; ?>
                <a href="<?php echo esc_url( abc_get_logout_url() ); ?>" class="mobile-menu-action">
                    <?php abc_icon( 'logout' ); ?>
                    <?php esc_html_e( 'Logout', 'brand-center' ); ?>
                </a>
            </div>
        </div>

    </div><!-- /.mobile-menu-l1 -->


    <!-- L2 screens (one per L1 with children) -------------- -->
    <?php foreach ( $menu_tree as $l1_node ) :
        $l1_item     = $l1_node['item'];
        $l1_children = $l1_node['children'];
        if ( empty( $l1_children ) ) {
            continue;
        }
        $l1_id = (int) $l1_item->ID;
    ?>
    <div
        class="mobile-menu-l2"
        data-mobile-screen="l2"
        data-for-l1="<?php echo esc_attr( $l1_id ); ?>"
        hidden
    >

        <div class="mobile-menu-header">
            <div class="mobile-menu-user">
                <span class="mobile-menu-avatar" aria-hidden="true">
                    <img src="<?php echo esc_url( $avatar_src ); ?>" alt="">
                </span>
                <span class="mobile-menu-welcome"><?php echo esc_html( $display_name ); ?></span>
            </div>
            <button
                class="mobile-menu-close"
                aria-label="<?php esc_attr_e( 'Close navigation menu', 'brand-center' ); ?>"
            >
                <?php abc_icon( 'close' ); ?>
                <span><?php esc_html_e( 'Close', 'brand-center' ); ?></span>
            </button>
        </div>

        <div class="mobile-menu-content">

            <button class="mobile-menu-back">
                <?php abc_icon( 'chevron-left-20' ); ?>
                <?php echo esc_html( $l1_item->title ); ?>
            </button>

            <?php foreach ( $l1_children as $l2_node ) :
                $l2_item     = $l2_node['item'];
                $l3_children = $l2_node['children'];
            ?>
            <div class="mobile-menu-l2-group">
                <?php if ( ! empty( $l3_children ) ) : ?>
                    <h2 class="mobile-menu-l2-heading"><?php echo esc_html( $l2_item->title ); ?></h2>
                    <ul class="mobile-menu-l3-list" role="list">
                        <?php foreach ( $l3_children as $l3_raw ) :
                            $l3 = is_array( $l3_raw ) ? $l3_raw['item'] : $l3_raw;
                        ?>
                        <li>
                            <a
                                href="<?php echo esc_url( $l3->url ); ?>"
                                class="mobile-menu-l3"
                            >
                                <?php echo esc_html( $l3->title ); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <a
                        href="<?php echo esc_url( $l2_item->url ); ?>"
                        class="mobile-menu-l2-heading"
                    >
                        <?php echo esc_html( $l2_item->title ); ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

        </div>

    </div><!-- /.mobile-menu-l2 -->
    <?php endforeach; ?>

</div><!-- /#mobile-menu -->
