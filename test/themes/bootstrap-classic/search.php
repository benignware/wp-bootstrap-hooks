<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

get_header();
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main container">
			
		<?php if ( have_posts() ) : ?>
			<header class="page-header mb-4">
				<h1 class="page-title">
					<?php _e( 'Search results for:', 'twentynineteen' ); ?>
				</h1>
				<div class="page-description"><?php echo get_search_query(); ?></div>
			</header><!-- .page-header -->
			<div class="row row-cols-md-3 row-cols-lg-4 g-4">
				

				<?php while ( have_posts() ) : the_post(); ?>
					<div class="col">
						<?php get_template_part( 'template-parts/content/content', 'excerpt' ); ?>
					</div>

				<?php endwhile; ?>

				<?php the_posts_navigation(); ?>

		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
		<?php endif; ?>
		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();