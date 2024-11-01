<?php
/**
 * Layout based on Twenty_Seventeen
 */

get_header(); ?>

	<div class="wrap">
		<div id="primary" class="content-area">
			<main id="main" class="site-main anwp-custom-layout-b" role="main">

				<?php
				/* Start the Loop */
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<?php if ( ! in_array( get_post_type(), [ 'sl_game', 'sl_tournament' ], true ) ) : ?>
							<header class="entry-header">
								<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
							</header><!-- .entry-header -->
						<?php endif; ?>

						<div class="entry-content"><?php the_content(); ?></div>
					</article>
					<?php
					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;

				endwhile; // End of the loop.
				?>

			</main><!-- #main -->
		</div><!-- #primary -->
		<?php get_sidebar(); ?>
	</div><!-- .wrap -->

<?php
get_footer();
