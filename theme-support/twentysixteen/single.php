<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package    WordPress
 * @subpackage Twenty_Sixteen
 * @since      Twenty Sixteen 1.0
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php
		// Start the loop.
		while ( have_posts() ) :
			the_post();
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'type-page' ); ?>>
				<?php if ( ! in_array( get_post_type(), [ 'sl_game', 'sl_tournament' ], true ) ) : ?>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header><!-- .entry-header -->
				<?php endif; ?>

				<div class="entry-content">
					<?php the_content(); ?>
				</div><!-- .entry-content -->
			</article><!-- #post-## -->
			<?php
			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}

			// End of the loop.
		endwhile;
		?>

	</main><!-- .site-main -->

	<?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
