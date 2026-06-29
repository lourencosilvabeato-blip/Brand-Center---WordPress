<?php
/**
 * Template Name: Homepage
 *
 * B01 — Entry point for all authenticated users.
 * Full-width layout: no left sidebar, no anchor bar.
 * Sections: hero + search, highlights #1, highlights #2, help buttons.
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

// ----- ACF field values -------------------------------------------------------
$hero_image    = function_exists( 'get_field' ) ? get_field( 'hero_image' )    : null;
$hero_headline = function_exists( 'get_field' ) ? get_field( 'hero_headline' ) : '';
$hero_intro    = function_exists( 'get_field' ) ? get_field( 'hero_intro' )    : '';

$section1_title = function_exists( 'get_field' ) ? get_field( 'section1_title' ) : '';
$section1_intro = function_exists( 'get_field' ) ? get_field( 'section1_intro' ) : '';

$section2_title = function_exists( 'get_field' ) ? get_field( 'section2_title' ) : '';
$section2_intro = function_exists( 'get_field' ) ? get_field( 'section2_intro' ) : '';

// ----- Build highlights arrays ------------------------------------------------
$highlights1 = array();
$highlights2 = array();
if ( function_exists( 'get_field' ) ) {
    for ( $i = 1; $i <= 6; $i++ ) {
        $img = get_field( 'highlight1_image_' . $i );
        $ttl = get_field( 'highlight1_title_' . $i );
        if ( $img && $ttl ) {
            $highlights1[] = array(
                'image'   => $img,
                'title'   => $ttl,
                'url'     => get_field( 'highlight1_url_' . $i ),
                'new_tab' => get_field( 'highlight1_new_tab_' . $i ),
            );
        }
    }
    for ( $i = 1; $i <= 6; $i++ ) {
        $img = get_field( 'highlight2_image_' . $i );
        $ttl = get_field( 'highlight2_title_' . $i );
        if ( $img && $ttl ) {
            $highlights2[] = array(
                'image'   => $img,
                'title'   => $ttl,
                'url'     => get_field( 'highlight2_url_' . $i ),
                'new_tab' => get_field( 'highlight2_new_tab_' . $i ),
            );
        }
    }
}

// ----- Help buttons — fixed labels, BO-configurable URLs --------------------
// Labels are always hardcoded. URLs come from ACF option fields, falling back
// to the corresponding page slugs if the field is left blank in the BO.
$_get = function_exists( 'get_field' ) ? 'get_field' : '__return_empty_string';
$help_buttons = array(
    array(
        'label' => __( 'Contact us', 'brand-center' ),
        'url'   => ( call_user_func( $_get, 'help_contact_url' ) ) ?: home_url( '/contact/' ),
    ),
    array(
        'label' => __( 'Navigation Tips', 'brand-center' ),
        'url'   => ( call_user_func( $_get, 'help_nav_tips_url' ) ) ?: home_url( '/navigation-tips/' ),
    ),
    array(
        'label' => __( 'FAQs', 'brand-center' ),
        'url'   => ( call_user_func( $_get, 'help_faqs_url' ) ) ?: home_url( '/faqs/' ),
    ),
);

// ----- Search filter items ----------------------------------------------------
$search_filters = function_exists( 'abc_get_search_filter_items' ) ? abc_get_search_filter_items() : array();

// ----- Hero image URL ---------------------------------------------------------
$hero_img_url = '';
if ( $hero_image ) {
    $hero_img_url = is_array( $hero_image ) ? ( $hero_image['url'] ?? '' ) : $hero_image;
}

get_header();
?>

<main id="main-content" class="homepage">

    <!-- =====================================================================
         HERO — full-width image, headline, intro, search bar
         ===================================================================== -->
    <section
        class="hp-hero"
        <?php if ( $hero_img_url ) : ?>
            style="background-image: url('<?php echo esc_url( $hero_img_url ); ?>');"
        <?php endif; ?>
        aria-label="<?php esc_attr_e( 'Hero', 'brand-center' ); ?>"
    >
        <div class="hp-hero-overlay" aria-hidden="true"></div>

        <div class="hp-hero-content">
            <?php if ( $hero_headline ) : ?>
            <h1 class="hp-hero-headline"><?php echo esc_html( $hero_headline ); ?></h1>
            <?php endif; ?>

            <?php if ( $hero_intro ) : ?>
            <p class="hp-hero-intro"><?php echo esc_html( $hero_intro ); ?></p>
            <?php endif; ?>
        </div>

        <!-- Search bar -------------------------------------------------------- -->
        <div class="hp-search-wrap">
            <form
                id="homepage-search-form"
                class="hp-search-form"
                method="get"
                action="<?php echo esc_url( home_url( '/' ) ); ?>"
                role="search"
                aria-label="<?php esc_attr_e( 'Search brand center', 'brand-center' ); ?>"
                novalidate
            >
                <!-- Bar: filter + input -->
                <div class="hp-search-bar">

                    <!-- Filter dropdown -->
                    <div class="hp-search-filter-wrap">
                        <label class="screen-reader-text" for="hp-search-filter">
                            <?php esc_html_e( 'Filter by', 'brand-center' ); ?>
                        </label>
                        <select
                            id="hp-search-filter"
                            name="abc_filter"
                            class="hp-search-filter"
                        >
                            <option value=""><?php esc_html_e( 'All content', 'brand-center' ); ?></option>
                            <?php foreach ( $search_filters as $sf ) : ?>
                            <option value="<?php echo esc_attr( $sf['object_id'] ); ?>">
                                <?php echo esc_html( $sf['label'] ); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="hp-search-filter-caret" aria-hidden="true"><?php abc_icon( 'caret-down' ); ?></span>
                    </div>

                    <!-- Text input -->
                    <div class="hp-search-input-wrap">
                        <label class="screen-reader-text" for="hp-search-input">
                            <?php esc_html_e( 'Search', 'brand-center' ); ?>
                        </label>
                        <input
                            type="search"
                            id="hp-search-input"
                            name="s"
                            class="hp-search-input"
                            placeholder="<?php esc_attr_e( 'Search all you need...', 'brand-center' ); ?>"
                            autocomplete="off"
                            aria-describedby="hp-search-error"
                        >
                    </div>

                </div><!-- /.hp-search-bar -->

                <!-- Submit button — 44×44px circle adjacent to bar -->
                <button
                    type="submit"
                    class="hp-search-btn"
                    aria-label="<?php esc_attr_e( 'Search', 'brand-center' ); ?>"
                >
                    <?php abc_icon( 'search' ); ?>
                </button>

                <!-- Inline error (shown by JS when field is empty) -->
                <p
                    id="hp-search-error"
                    class="hp-search-error"
                    hidden
                    role="alert"
                >
                    <?php esc_html_e( 'Please enter a search term before searching.', 'brand-center' ); ?>
                </p>
            </form>
        </div><!-- /.hp-search-wrap -->

    </section><!-- /.hp-hero -->


    <!-- =====================================================================
         HIGHLIGHTS SECTION #1 — e.g. "New In"
         ===================================================================== -->
    <?php if ( $section1_title || ! empty( $highlights1 ) ) : ?>
    <section class="hp-section" aria-labelledby="hp-section1-heading">
        <div class="hp-section-inner">

            <?php if ( $section1_title || $section1_intro ) : ?>
            <div class="hp-section-header">
                <?php if ( $section1_title ) : ?>
                <h2 id="hp-section1-heading" class="hp-section-title">
                    <?php echo esc_html( $section1_title ); ?>
                </h2>
                <?php endif; ?>
                <?php if ( $section1_intro ) : ?>
                <p class="hp-section-intro"><?php echo esc_html( $section1_intro ); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $highlights1 ) ) : ?>
            <div class="hp-cards-grid">
                <?php foreach ( $highlights1 as $card ) :
                    $card_img = is_array( $card['image'] ) ? ( $card['image']['url'] ?? '' ) : $card['image'];
                    $target   = $card['new_tab'] ? '_blank' : '_self';
                    $rel      = $card['new_tab'] ? 'rel="noopener noreferrer"' : '';
                    $tag      = $card['url'] ? 'a' : 'div';
                    $href_attr = $card['url'] ? 'href="' . esc_url( $card['url'] ) . '"' : '';
                    $target_attr = $card['url'] && $card['new_tab'] ? 'target="_blank" rel="noopener noreferrer"' : '';
                ?>
                <<?php echo $tag; ?>
                    class="hp-card"
                    <?php echo $href_attr; ?>
                    <?php echo $target_attr; ?>
                    style="<?php echo $card_img ? 'background-image:url(' . esc_url( $card_img ) . ')' : ''; ?>"
                >
                    <div class="hp-card-overlay" aria-hidden="true"></div>
                    <span class="hp-card-title"><?php echo esc_html( $card['title'] ); ?></span>
                </<?php echo $tag; ?>>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </section>
    <?php endif; ?>


    <!-- =====================================================================
         HIGHLIGHTS SECTION #2 — e.g. "Quick Access"
         ===================================================================== -->
    <?php if ( $section2_title || ! empty( $highlights2 ) ) : ?>
    <section class="hp-section" aria-labelledby="hp-section2-heading">
        <div class="hp-section-inner">

            <?php if ( $section2_title || $section2_intro ) : ?>
            <div class="hp-section-header">
                <?php if ( $section2_title ) : ?>
                <h2 id="hp-section2-heading" class="hp-section-title">
                    <?php echo esc_html( $section2_title ); ?>
                </h2>
                <?php endif; ?>
                <?php if ( $section2_intro ) : ?>
                <p class="hp-section-intro"><?php echo esc_html( $section2_intro ); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $highlights2 ) ) : ?>
            <div class="hp-cards-grid">
                <?php foreach ( $highlights2 as $card ) :
                    $card_img    = is_array( $card['image'] ) ? ( $card['image']['url'] ?? '' ) : $card['image'];
                    $tag         = $card['url'] ? 'a' : 'div';
                    $href_attr   = $card['url'] ? 'href="' . esc_url( $card['url'] ) . '"' : '';
                    $target_attr = $card['url'] && $card['new_tab'] ? 'target="_blank" rel="noopener noreferrer"' : '';
                ?>
                <<?php echo $tag; ?>
                    class="hp-card"
                    <?php echo $href_attr; ?>
                    <?php echo $target_attr; ?>
                    style="<?php echo $card_img ? 'background-image:url(' . esc_url( $card_img ) . ')' : ''; ?>"
                >
                    <div class="hp-card-overlay" aria-hidden="true"></div>
                    <span class="hp-card-title"><?php echo esc_html( $card['title'] ); ?></span>
                </<?php echo $tag; ?>>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </section>
    <?php endif; ?>


    <!-- =====================================================================
         HELP BUTTONS — Contact us / Navigation Tips / FAQs
         Fixed section: always rendered with hardcoded labels.
         ===================================================================== -->
    <section class="hp-section hp-help-section" aria-label="<?php esc_attr_e( 'Quick links', 'brand-center' ); ?>">
        <div class="hp-section-inner">
            <div class="hp-help-buttons">
                <?php foreach ( $help_buttons as $btn ) : ?>
                <a
                    href="<?php echo esc_url( $btn['url'] ); ?>"
                    class="hp-help-btn"
                >
                    <span class="hp-help-btn-label"><?php echo esc_html( $btn['label'] ); ?></span>
                    <?php abc_icon( 'chevron-right-20' ); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

</main><!-- /#main-content -->

<?php get_footer(); ?>
