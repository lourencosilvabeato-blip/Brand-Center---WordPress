<?php
/**
 * Template: Search Results (D01)
 *
 * Layout: search-specific left sidebar | content area.
 * Sidebar: search input (showing current term + clear button) + vertical filter links.
 * Content: result count header, result items with excerpt + breadcrumb, pagination.
 *
 * URL parameters:
 *  s           — search term (WP native)
 *  abc_filter  — menu item object_id to scope results
 *  paged       — page number
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended

$search_term   = get_search_query();
$active_filter = isset( $_GET['abc_filter'] ) ? (int) $_GET['abc_filter'] : 0;
$current_page  = max( 1, (int) ( get_query_var( 'paged' ) ?: ( $_GET['paged'] ?? 1 ) ) );

// phpcs:enable

$filter_post_ids = array();
if ( $active_filter > 0 && function_exists( 'abc_get_descendant_page_ids' ) ) {
	$filter_post_ids = array_merge(
		array( $active_filter ),
		abc_get_descendant_page_ids( $active_filter )
	);
}

$query_args = array(
	's'              => $search_term,
	'post_type'      => 'page',
	'post_status'    => 'publish',
	'posts_per_page' => 7,
	'paged'          => $current_page,
	'orderby'        => 'relevance',
	'order'          => 'DESC',
);
if ( ! empty( $filter_post_ids ) ) {
	$query_args['post__in'] = $filter_post_ids;
}
$results_query = new WP_Query( $query_args );
$total_results = $results_query->found_posts;
$total_pages   = $results_query->max_num_pages;
$search_home   = home_url( '/' );

$filter_items = function_exists( 'abc_get_search_filter_items' ) ? abc_get_search_filter_items() : array();

get_header();
?>

<div class="search-page-wrap">

	<!-- Search-specific sidebar: search input + filter links -->
	<aside class="search-sidebar" aria-label="<?php esc_attr_e( 'Search options', 'ascendum-brand-center' ); ?>">
		<div class="search-sidebar-inner">

			<form
				class="search-sidebar-form"
				role="search"
				action="<?php echo esc_url( $search_home ); ?>"
				method="get"
			>
				<?php if ( $active_filter > 0 ) : ?>
				<input type="hidden" name="abc_filter" value="<?php echo esc_attr( $active_filter ); ?>">
				<?php endif; ?>

				<input
					type="search"
					name="s"
					class="search-sidebar-input"
					value="<?php echo esc_attr( $search_term ); ?>"
					placeholder="<?php esc_attr_e( 'Search…', 'ascendum-brand-center' ); ?>"
					aria-label="<?php esc_attr_e( 'Search', 'ascendum-brand-center' ); ?>"
					autocomplete="off"
				>

				<button
					type="button"
					class="search-sidebar-clear"
					aria-label="<?php esc_attr_e( 'Clear search', 'ascendum-brand-center' ); ?>"
					<?php echo ! $search_term ? 'hidden' : ''; ?>
				><?php abc_icon( 'close-20' ); ?></button>

				<span class="search-sidebar-icon" aria-hidden="true" <?php echo $search_term ? 'hidden' : ''; ?>>
					<?php abc_icon( 'search' ); ?>
				</span>
			</form>

			<nav class="search-sidebar-filters" aria-label="<?php esc_attr_e( 'Search filters', 'ascendum-brand-center' ); ?>">
				<a
					href="<?php echo esc_url( add_query_arg( 's', $search_term, $search_home ) ); ?>"
					class="search-sidebar-filter<?php echo ( 0 === $active_filter ) ? ' is-active' : ''; ?>"
				><?php esc_html_e( 'All content', 'ascendum-brand-center' ); ?></a>

				<?php foreach ( $filter_items as $fi ) : ?>
				<a
					href="<?php echo esc_url( add_query_arg( array( 's' => $search_term, 'abc_filter' => $fi['object_id'] ), $search_home ) ); ?>"
					class="search-sidebar-filter<?php echo ( $active_filter === $fi['object_id'] ) ? ' is-active' : ''; ?>"
				><?php echo esc_html( $fi['label'] ); ?></a>
				<?php endforeach; ?>
			</nav>

		</div>
	</aside>

	<main id="main-content" class="search-page-main">

		<!-- Page intro: title + result count -->
		<div class="search-page-intro">
			<h1 class="search-page-title"><?php esc_html_e( 'Search Results', 'ascendum-brand-center' ); ?></h1>

			<?php if ( $search_term ) : ?>
			<p class="search-result-count">
				<span class="search-result-count-regular">
					<?php
					printf(
						/* translators: %d: number of results */
						esc_html( _n( '%d result found for', '%d results found for', $total_results, 'ascendum-brand-center' ) ),
						(int) $total_results
					);
					?>
				</span><span class="search-result-count-term"> &#8220;<?php echo esc_html( $search_term ); ?>&#8221;</span>
			</p>
			<?php endif; ?>
		</div>

		<!-- Results list -->
		<?php if ( $results_query->have_posts() ) : ?>
		<ul class="search-results-list" role="list">
			<?php while ( $results_query->have_posts() ) : $results_query->the_post(); ?>
			<?php
			$result_id = get_the_ID();
			$excerpt   = get_post_field( 'post_excerpt', $result_id );
			$bc_parts  = function_exists( 'abc_get_search_result_breadcrumb_parts' )
				? abc_get_search_result_breadcrumb_parts( (int) $result_id )
				: array();
			?>
			<li class="search-result-item">
				<a href="<?php the_permalink(); ?>" class="search-result-link">

					<div class="search-result-title-row">
						<span class="search-result-title"><?php the_title(); ?></span>
						<?php abc_icon( 'chevron-right-20' ); ?>
					</div>

					<?php if ( $excerpt ) : ?>
					<p class="search-result-excerpt"><?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $bc_parts ) ) : ?>
					<p class="search-result-breadcrumb">
						<?php if ( ! empty( $bc_parts['path'] ) ) : ?>
						<span class="sr-bc-path"><?php echo esc_html( $bc_parts['path'] ); ?></span>
						<?php endif; ?>
						<span class="sr-bc-title"><?php echo esc_html( $bc_parts['title'] ); ?></span>
					</p>
					<?php endif; ?>

				</a>
			</li>
			<?php endwhile; wp_reset_postdata(); ?>
		</ul>

		<!-- Pagination: prev | Page X of Y | next -->
		<?php if ( $total_pages > 1 ) : ?>
		<?php
		$prev_url = $current_page > 1
			? add_query_arg( array( 's' => $search_term, 'paged' => $current_page - 1 ), $search_home )
			: null;
		$next_url = $current_page < $total_pages
			? add_query_arg( array( 's' => $search_term, 'paged' => $current_page + 1 ), $search_home )
			: null;
		?>
		<nav class="search-pagination" aria-label="<?php esc_attr_e( 'Search results pages', 'ascendum-brand-center' ); ?>">

			<?php if ( $prev_url ) : ?>
			<a
				href="<?php echo esc_url( $prev_url ); ?>"
				class="search-pager-btn"
				aria-label="<?php esc_attr_e( 'Previous page', 'ascendum-brand-center' ); ?>"
			><?php abc_icon( 'chevron-left-20' ); ?></a>
			<?php else : ?>
			<span class="search-pager-btn is-disabled" aria-disabled="true" aria-label="<?php esc_attr_e( 'Previous page', 'ascendum-brand-center' ); ?>"><?php abc_icon( 'chevron-left-20' ); ?></span>
			<?php endif; ?>

			<span class="search-pager-page">
				<?php
				printf(
					/* translators: %1$d: current page, %2$d: total pages */
					esc_html__( 'Page %1$d of %2$d', 'ascendum-brand-center' ),
					$current_page,
					$total_pages
				);
				?>
			</span>

			<?php if ( $next_url ) : ?>
			<a
				href="<?php echo esc_url( $next_url ); ?>"
				class="search-pager-btn"
				aria-label="<?php esc_attr_e( 'Next page', 'ascendum-brand-center' ); ?>"
			><?php abc_icon( 'chevron-right-20' ); ?></a>
			<?php else : ?>
			<span class="search-pager-btn is-disabled" aria-disabled="true" aria-label="<?php esc_attr_e( 'Next page', 'ascendum-brand-center' ); ?>"><?php abc_icon( 'chevron-right-20' ); ?></span>
			<?php endif; ?>

		</nav>
		<?php endif; ?>

		<?php else : ?>
		<p class="search-no-results"><?php esc_html_e( 'No results found.', 'ascendum-brand-center' ); ?></p>
		<?php endif; ?>

	</main>

</div>

<?php get_footer(); ?>
