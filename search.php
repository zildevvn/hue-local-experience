<?php

/**
 * The template for displaying search results pages
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 * @package hle
 */

get_header();
$search_query = trim(get_search_query());
?>
<div class="layout layout--hero-block layout--first">
	<div class="hero-block hero-layout-460 hero-type-bg_color" style="background-color:#1B365D;">
		<div class="hero-block__circle">
			<svg width="800" height="800" viewBox="0 0 800 800" fill="none" xmlns="http://www.w3.org/2000/svg">
				<g style="mix-blend-mode:color-dodge" opacity="0.5">
					<path fill-rule="evenodd" clip-rule="evenodd"
						d="M400 0C620.914 0 800 179.086 800 400C800 620.914 620.914 800 400 800C179.086 800 0 620.914 0 400C0 179.086 179.086 0 400 0ZM400.5 134C253.211 134 134 253.318 134 400.5C134 547.682 253.211 667 400.5 667C547.789 667 667 547.682 667 400.5C667 253.318 547.551 134 400.5 134Z"
						fill="#0F4F75"></path>
				</g>
			</svg>
		</div>
		<div class="container">
			<h1>Search</h1>

			<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
				<input type="search" name="s" placeholder="" value="<?= $search_query; ?>" required />
				<span class="search-info">Hit enter to search or ESC to close</span>
				<button type="submit" class="search-submit">Search</button>
			</form>
		</div>
	</div>
</div>

<?php if (!empty($search_query)): ?>
	<div class="hle-results-search-section">
		<div class="container">
			<?php if (have_posts()): ?>
				<?php
				global $wp_query;

				$total_results = $wp_query->found_posts;
				$paged = max(1, get_query_var('paged'));
				$per_page = get_query_var('posts_per_page') ?: 10;

				$start = ($paged - 1) * $per_page + 1;
				$end = min($paged * $per_page, $total_results);

				$search_query = get_search_query();

				echo '<h4 class="hle-results-search-section__total ">';

				if ($total_results > 10) {
					echo 'Showing ' . $start . ' – ' . $end . ' of ' . $total_results . ' results for: “' . esc_html($search_query) . '”';
				} else {
					echo $total_results . ' results for “' . esc_html($search_query) . '”';
				}

				echo '</h4>';
				?>

				<div class="hle-results-search-section__list d-flex flex-wrap">
					<?php while (have_posts()):
						the_post();
						$post_type = get_post_type();
						$cta_group = [
							'cta' => [
								'url' => get_permalink(),
								'title' => 'View',
								'target' => '_self'
							],
							'cta_style' => 'outline'
						];
						?>
						<div class="item-post">
							<span style="display:block; margin-bottom: 20px; text-transform: uppercase;"><?= $post_type ?></span>
							<h4 class="item-post__title">
								<a href="<?php the_permalink() ?>"><?php the_title() ?></a>
							</h4>
							<span class="line d-flex"></span>

							<div class="item-post__excerpt" style="-webkit-box-orient:vertical;">
								<?php
								$excerpt = get_the_excerpt();
								echo $excerpt;
								?>
							</div>

							<?php get_template_part('template-parts/cta', null, ['cta' => $cta_group]); ?>
						</div>
					<?php endwhile; ?>
				</div>

				<?php
				hle_the_posts_navigation([
					'prev_text' => hle_svg_icon('arrow_prev') . __('Prev'),
					'next_text' => __('Next') . hle_svg_icon('arrow_next'),
				]);
				wp_reset_postdata();
				?>
			<?php else: ?>
				<h3 class="hle-results-search-section__total" style="text-align: center;">No results found</h3>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
<?php
get_footer();
