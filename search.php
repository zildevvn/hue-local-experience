<?php
/**
 * The template for displaying search results pages
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 * @package hle
 */

get_header();
global $wp_query;
$search_query = trim(get_search_query());
$total_results = $wp_query->found_posts;
$bg_banner = get_field('bg_banner', 'option');
?>
<section class="hle-section hero-section-shared search-hero-section">
	<div class="hle-section__bg">
		<?php if (!empty($bg_banner)): ?>
			<img src="<?= esc_url($bg_banner) ?>" alt="background hero for search page" />
		<?php else: ?>
			<div style="background-color:#1B365D; width: 100%; height: 100%;"></div>
		<?php endif; ?>
	</div>

	<div class="container">
		<div class="hero-section-shared__box">
			<?php if (!empty($search_query)): ?>
				<h1 class="search-title">
					Search: <span>"<?php echo esc_html($search_query); ?>"</span>
				</h1>
				<?php if ($total_results > 0): ?>
					<p class="search-subtitle">
						We found <strong><?php echo esc_html($total_results); ?></strong>
						result<?php echo $total_results !== 1 ? 's' : ''; ?> for your search.
					</p>
				<?php else: ?>
					<p class="search-subtitle">
						We couldn't find any results matching your search.
					</p>
				<?php endif; ?>
			<?php else: ?>
				<h1 class="hle-heading-animation search-title">Search Our Site</h1>
				<p class="search-subtitle">Enter a keyword below to find tours, cars, and articles.</p>
			<?php endif; ?>

			<div class="search-form-wrapper">
				<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>"
					class="modern-search-form">
					<div class="input-group">
						<svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
							stroke-linecap="round" stroke-linejoin="round">
							<circle cx="11" cy="11" r="8"></circle>
							<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
						</svg>
						<input type="search" name="s" placeholder="What are you looking for?"
							value="<?php echo esc_attr($search_query); ?>" required />
						<button type="submit" class="hle-button hle-button--primary search-submit">Search</button>
					</div>
				</form>
			</div>

			<?php hle_breadcrumbs('Search Results'); ?>
		</div>
	</div>
</section>

<div class="search-results" id="search-results">
	<div class="hle-results-search-section">
		<div class="container">
			<?php if (have_posts() && !empty($search_query)): ?>
				<?php
				global $wp_query;

				$total_results = $wp_query->found_posts;
				$paged = max(1, get_query_var('paged'));
				$per_page = get_query_var('posts_per_page') ?: 10;

				$start = ($paged - 1) * $per_page + 1;
				$end = min($paged * $per_page, $total_results);

				$search_query = get_search_query();

				echo '<h4 class="hle-results-search-section__total">';

				if ($total_results > 10) {
					echo 'Showing ' . esc_html($start) . ' – ' . esc_html($end) . ' of ' . esc_html($total_results) . ' results for: “' . esc_html($search_query) . '”';
				} else {
					echo esc_html($total_results) . ' results for “' . esc_html($search_query) . '”';
				}

				echo '</h4>';
				?>

				<div class="hle-results-search-section__list">
					<?php while (have_posts()):
						the_post();
						$post_type = get_post_type();
						$post_type_label = get_post_type_object($post_type)->labels->singular_name ?? $post_type;
						$thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
						?>
						<a href="<?php the_permalink(); ?>" class="search-card">
							<?php if ($thumbnail_url): ?>
								<div class="search-card__image">
									<img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>"
										loading="lazy">
									<span class="search-card__badge"><?php echo esc_html($post_type_label); ?></span>
								</div>
							<?php else: ?>
								<div class="search-card__image"
									style="background: #E5E7EB; display: flex; align-items: center; justify-content: center; position: relative;">
									<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.5"
										stroke-linecap="round" stroke-linejoin="round">
										<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
										<circle cx="8.5" cy="8.5" r="1.5"></circle>
										<polyline points="21 15 16 10 5 21"></polyline>
									</svg>
									<span class="search-card__badge"><?php echo esc_html($post_type_label); ?></span>
								</div>
							<?php endif; ?>

							<div class="search-card__content">
								<div class="search-card__meta">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
										stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
										<line x1="16" y1="2" x2="16" y2="6"></line>
										<line x1="8" y1="2" x2="8" y2="6"></line>
										<line x1="3" y1="10" x2="21" y2="10"></line>
									</svg>
									<?php echo get_the_date(); ?>
								</div>

								<h3 class="search-card__title">
									<?php the_title(); ?>
								</h3>

								<div class="search-card__excerpt">
									<?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
								</div>

								<div class="search-card__footer">
									<span class="read-more">
										Read More
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
											stroke-linecap="round" stroke-linejoin="round">
											<line x1="5" y1="12" x2="19" y2="12"></line>
											<polyline points="12 5 19 12 12 19"></polyline>
										</svg>
									</span>
								</div>
							</div>
						</a>
					<?php endwhile; ?>
				</div>

				<?php
				hle_pagination();
				wp_reset_postdata();
				?>
			<?php else: ?>
				<div class="search-empty-state">
					<div class="search-empty-state__card">
						<div class="search-empty-state__content">
							<?php if (!empty($search_query)): ?>
								<h2 class="search-empty-state__title">No Results Found</h2>
								<p class="search-empty-state__desc">
									Sorry, we couldn't find any results for "<span
										class="search-empty-state__keyword"><?php echo esc_html($search_query); ?></span>".
								</p>
								<p class="search-empty-state__subdesc">It seems this keyword may be misspelled or too specific.
									Try searching again using the search bar above or explore our suggestions below.</p>
							<?php else: ?>
								<h2 class="search-empty-state__title">Search Our Experiences</h2>
								<p class="search-empty-state__desc">
									Enter a keyword in the search bar above to discover tours, cars, and travel guides.
								</p>
								<p class="search-empty-state__subdesc">Or explore some of our most popular tours and helpful
									search tips below.</p>
							<?php endif; ?>

							<div class="search-empty-state__actions">
								<a href="<?php echo esc_url(home_url('/hue-experience-all-tour/')); ?>" class="hle-button">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
										stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<circle cx="12" cy="12" r="10"></circle>
										<polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
									</svg>
									Explore Tours
								</a>
								<a href="<?php echo esc_url(home_url('/')); ?>" class="hle-button hle-button--outline">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
										stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<line x1="19" y1="12" x2="5" y2="12"></line>
										<polyline points="12 19 5 12 12 5"></polyline>
									</svg>
									Back Home
								</a>
							</div>

							<div class="search-empty-state__tips">
								<div class="search-empty-state__tip-card">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
										stroke-linecap="round" stroke-linejoin="round">
										<polyline points="20 6 9 17 4 12"></polyline>
									</svg>
									<span>Check your spelling</span>
								</div>
								<div class="search-empty-state__tip-card">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
										stroke-linecap="round" stroke-linejoin="round">
										<polyline points="20 6 9 17 4 12"></polyline>
									</svg>
									<span>Try shorter keywords</span>
								</div>
								<div class="search-empty-state__tip-card">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
										stroke-linecap="round" stroke-linejoin="round">
										<polyline points="20 6 9 17 4 12"></polyline>
									</svg>
									<span>Search destinations</span>
								</div>
								<div class="search-empty-state__tip-card">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
										stroke-linecap="round" stroke-linejoin="round">
										<polyline points="20 6 9 17 4 12"></polyline>
									</svg>
									<span>Search tour names</span>
								</div>
							</div>
						</div>
					</div>

					<?php
					$popular_tours = new WP_Query([
						'post_type' => 'tours',
						'posts_per_page' => 3,
						'post_status' => 'publish',
						'orderby' => 'date',
						'order' => 'DESC'
					]);
					if ($popular_tours->have_posts()):
						?>
						<div class="search-recommendations">
							<h3 class="hle-heading search-recommendations__title">You Might Also Like</h3>
							<div class="hle-results-search-section__list">
								<?php while ($popular_tours->have_posts()):
									$popular_tours->the_post();
									if (function_exists('hle_tour_item')) {
										hle_tour_item();
									}
								endwhile;
								wp_reset_postdata(); ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
