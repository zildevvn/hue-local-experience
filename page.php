<?php get_header(); ?>
	<?php if(have_posts()) : ?><?php while(have_posts()) : the_post(); ?>
		<main class="site-main template-home">
			<div class="container" style="min-height:50vh; padding-top:100px;  max-width: 1000px;"> 
				<?php the_content() ?>
			</div>
		</main>
	<?php endwhile; ?><?php endif; ?>
<?php get_footer(); ?>