<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

get_header();
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header py-5">
				<div class="container">
					<?php
						the_archive_title( '<h1 class="page-title">', '</h1>' );
					?>
				</div>
			</header><!-- .page-header -->

			<div class="container">
				<div class="row g-4">
					<?php while ( have_posts() ) : the_post() ?>
						<div class="col-md-4">
							<?php get_template_part( 'template-parts/content/content', 'excerpt' ); ?>
						</div>
					<?php endwhile ?>
				</div>
				<?php the_posts_navigation([
					'class' => 'my-4'
				]) ?>
				<div>Hello</div>
				<?php the_posts_pagination([
					'type' => 'list',
					'class' => 'my-4'
				]) ?>
			</div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
		<?php endif ?>
		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();