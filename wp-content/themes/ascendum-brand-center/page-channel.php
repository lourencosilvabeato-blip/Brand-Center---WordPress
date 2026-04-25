<?php
/**
 * Template Name: Channel Page
 *
 * C01 — Pages with children (channel/landing page).
 * Layout: full-width, no sidebar, no anchor bar.
 *
 * Structure:
 *  - Breadcrumb (full-width)
 *  - Page header: H1 (auto from WP title) | optional description + CTA buttons
 *  - Card grid: each card links to a sub-page or destination
 *
 * All content is driven by ACF fields registered in inc/channel.php.
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

// ACF fields.
$cp_description = '';
$cp_buttons     = array();
$cp_cards       = array();

if ( function_exists( 'get_field' ) ) {
    $cp_description = trim( (string) get_field( 'cp_description' ) );
    $raw_buttons    = get_field( 'buttons' );
    $cp_buttons     = is_array( $raw_buttons ) ? $raw_buttons : array();
    $raw_cards      = get_field( 'cp_cards' );
    $cp_cards       = is_array( $raw_cards ) ? $raw_cards : array();
}

$has_right_col = $cp_description || ! empty( $cp_buttons );

get_header();
?>

<div class="channel-page-wrap">

    <?php abc_render_breadcrumb(); ?>

    <main id="main-content" class="channel-page-main">

        <!-- ── Page header ─────────────────────────────────────────────── -->
        <div class="channel-header<?php echo $has_right_col ? ' channel-header--two-col' : ''; ?>">

            <div class="channel-header-title-col">
                <h1 class="channel-header-title"><?php echo esc_html( get_the_title() ); ?></h1>
            </div>

            <?php if ( $has_right_col ) : ?>
            <div class="channel-header-right-col">

                <?php if ( $cp_description ) : ?>
                <div class="channel-header-description rich-text">
                    <?php echo wp_kses_post( $cp_description ); ?>
                </div>
                <?php endif; ?>

                <?php if ( ! empty( $cp_buttons ) ) : ?>
                <div class="channel-header-buttons">
                    <?php foreach ( $cp_buttons as $btn ) :
                        $label   = trim( $btn['button_label'] ?? '' );
                        $url     = trim( $btn['button_url']   ?? '' );
                        $file    = $btn['button_file'] ?? null;
                        $new_tab = ! empty( $btn['button_new_tab'] );

                        if ( ! $label ) {
                            continue;
                        }

                        if ( $file && ! empty( $file['url'] ) ) {
                            $href    = esc_url( $file['url'] );
                            $target  = ' target="_blank" rel="noopener noreferrer"';
                            $dl_attr = ' download';
                            $icon    = abc_icon( 'download' );
                        } elseif ( $url ) {
                            $href    = esc_url( $url );
                            $target  = $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
                            $dl_attr = '';
                            $icon    = abc_icon( 'arrow--up-right' );
                        } else {
                            continue;
                        }
                    ?>
                    <a
                        href="<?php echo $href; ?>"
                        class="channel-btn"
                        <?php echo $target; ?>
                        <?php echo $dl_attr; ?>
                    >
                        <?php echo esc_html( $label ); ?>
                        <?php echo $icon; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div><!-- /.channel-header-right-col -->
            <?php endif; ?>

        </div><!-- /.channel-header -->

        <!-- ── Card grid ───────────────────────────────────────────────── -->
        <?php if ( ! empty( $cp_cards ) ) : ?>
        <div class="channel-cards">
            <?php foreach ( $cp_cards as $card ) :
                $title    = trim( $card['card_title']    ?? '' );
                $subtitle = trim( (string) ( $card['card_subtitle'] ?? '' ) );
                $card_url = trim( $card['card_url']      ?? '' );
                $image    = $card['card_image'] ?? null;

                if ( ! $title || ! $card_url ) {
                    continue;
                }
            ?>
            <a
                href="<?php echo esc_url( $card_url ); ?>"
                class="channel-card"
                aria-label="<?php echo esc_attr( $title ); ?>"
            >
                <div class="channel-card-details">
                    <div class="channel-card-text">
                        <h2 class="channel-card-title"><?php echo esc_html( $title ); ?></h2>
                        <?php if ( $subtitle ) : ?>
                        <div class="channel-card-subtitle rich-text">
                            <?php echo wp_kses_post( $subtitle ); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div><!-- /.channel-card-details -->

                <?php if ( $image && ! empty( $image['url'] ) ) : ?>
                <div class="channel-card-image-wrap">
                    <img
                        src="<?php echo esc_url( $image['url'] ); ?>"
                        alt="<?php echo esc_attr( $image['alt'] ?? $title ); ?>"
                        class="channel-card-image"
                        loading="lazy"
                    >
                </div>
                <?php endif; ?>

                <span class="channel-card-btn" aria-hidden="true">
                    <?php echo abc_icon( 'arrow--up-right' ); ?>
                </span>

            </a><!-- /.channel-card -->
            <?php endforeach; ?>
        </div><!-- /.channel-cards -->
        <?php endif; ?>

    </main><!-- /.channel-page-main -->

</div><!-- /.channel-page-wrap -->

<?php get_footer(); ?>
