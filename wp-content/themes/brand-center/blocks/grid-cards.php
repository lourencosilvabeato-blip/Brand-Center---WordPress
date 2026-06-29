<?php
/**
 * Block: Grid Cards (Cartões em Grelha)
 *
 * Responsive card grid. Column count set in BO (1 / 2 / 3 / 3+).
 * Each card: optional image, title, description, and a single icon-only button.
 *  - Button with URL → arrow/external icon.
 *  - Button with file → download icon.
 * If card has an image, the button overlays the image.
 * If no image, the button aligns beside the text content.
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

$columns = $block['columns_desktop'] ?? '3';
$cards   = is_array( $block['cards'] ?? null ) ? $block['cards'] : array();

if ( empty( $cards ) ) {
    return;
}

$allowed_cols = array( '1', '2', '3', '3plus' );
if ( ! in_array( $columns, $allowed_cols, true ) ) {
    $columns = '3';
}
?>
<div class="block-grid-cards block-grid-cards--cols-<?php echo esc_attr( $columns ); ?>">
    <?php foreach ( $cards as $card ) :
        $img        = is_array( $card['card_image'] ?? null ) ? $card['card_image'] : null;
        $title      = trim( $card['card_title']       ?? '' );
        $desc       = trim( $card['card_description'] ?? '' );
        $btn        = is_array( $card['card_button'] ?? null ) ? $card['card_button'] : null;
        $btn_url    = trim( $btn['btn_url']  ?? '' );
        $btn_file   = is_array( $btn['btn_file'] ?? null ) ? $btn['btn_file'] : null;
        $has_img    = $img && ! empty( $img['url'] );
        $has_button = $btn_url || ( $btn_file && ! empty( $btn_file['url'] ) );

        // Determine if card is empty — skip.
        if ( ! $has_img && ! $title && ! $desc && ! $has_button ) {
            continue;
        }

        // Resolve button href and type.
        $btn_href = '';
        $btn_type = ''; // 'url' or 'file'
        if ( $btn_file && ! empty( $btn_file['url'] ) ) {
            $btn_href = esc_url( $btn_file['url'] );
            $btn_type = 'file';
        } elseif ( $btn_url ) {
            $btn_href = esc_url( $btn_url );
            $btn_type = 'url';
        }

        $card_class = 'gc-card';
        if ( $has_img ) {
            $card_class .= ' gc-card--has-image';
        }
    ?>
    <div class="<?php echo esc_attr( $card_class ); ?>">

        <?php if ( $has_img ) : ?>
        <div class="gc-card-image-wrap">
            <img
                src="<?php echo esc_url( $img['url'] ); ?>"
                alt="<?php echo esc_attr( $img['alt'] ?? '' ); ?>"
                class="gc-card-image"
                loading="lazy"
            >
            <?php if ( $btn_href ) : ?>
            <a
                href="<?php echo $btn_href; ?>"
                class="gc-card-btn gc-card-btn--on-image gc-card-btn--<?php echo esc_attr( $btn_type ); ?>"
                <?php if ( 'file' === $btn_type ) : ?>
                    download
                    target="_blank" rel="noopener noreferrer"
                <?php else : ?>
                    target="_blank" rel="noopener noreferrer"
                <?php endif; ?>
                aria-label="<?php echo 'file' === $btn_type
                    ? esc_attr__( 'Download', 'brand-center' )
                    : esc_attr__( 'View', 'brand-center' ); ?>"
            >
                <?php abc_icon( 'file' === $btn_type ? 'download' : 'arrow-right' ); ?>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="gc-card-body">
            <?php if ( $title ) : ?>
            <p class="gc-card-title"><?php echo esc_html( $title ); ?></p>
            <?php endif; ?>

            <?php if ( $desc ) : ?>
            <div class="gc-card-desc rich-text"><?php echo wp_kses_post( $desc ); ?></div>
            <?php endif; ?>

            <?php if ( $btn_href && ! $has_img ) : ?>
            <a
                href="<?php echo $btn_href; ?>"
                class="gc-card-btn gc-card-btn--beside-text gc-card-btn--<?php echo esc_attr( $btn_type ); ?>"
                <?php if ( 'file' === $btn_type ) : ?>
                    download
                    target="_blank" rel="noopener noreferrer"
                <?php else : ?>
                    target="_blank" rel="noopener noreferrer"
                <?php endif; ?>
                aria-label="<?php echo 'file' === $btn_type
                    ? esc_attr__( 'Download', 'brand-center' )
                    : esc_attr__( 'View', 'brand-center' ); ?>"
            >
                <?php abc_icon( 'file' === $btn_type ? 'download' : 'arrow-right' ); ?>
            </a>
            <?php endif; ?>
        </div>

    </div>
    <?php endforeach; ?>
</div>
