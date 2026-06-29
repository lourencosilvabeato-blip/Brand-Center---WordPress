<?php
/**
 * Main template fallback.
 *
 * WordPress falls back to this file when no more-specific template is found.
 *
 * @package brand-center
 */

get_header();
?>

<main id="main" class="site-main">
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
    endif;
    ?>
</main>

<?php
get_footer();
