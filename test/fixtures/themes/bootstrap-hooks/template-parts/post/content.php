<?php

/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.2
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
		// if ( is_sticky() && is_home() ) :
		// 	echo twentyseventeen_get_svg( array( 'icon' => 'thumb-tack' ) );
		// endif;
	?>
	<!-- <header <?php post_header_class(); ?>> -->
		<!-- <?php bs_post_categories(); ?> -->
	<!-- </header> -->
	<!-- .entry-header -->

	<div class="row g-0">
		<div class="col-md-4">

			<?php if ( '' !== get_the_post_thumbnail() && ! is_single() ) : ?>
				<div class="post-thumbnail figure">
					<a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail( 'twentyseventeen-featured-image', array('class' => 'figure-img card-img-top mb-0') ); ?>
					</a>
				</div><!-- .post-thumbnail -->
			<?php endif; ?>
		</div>
		<div class="col-md-8">
			<div <?php post_content_class(); ?>>
				<?php
					if ( is_single() ):
						the_title( '<h1 class="' . implode(' ', get_post_title_class()) . '">', '</h1>' );
					elseif ( is_front_page() && is_home() ):
						the_title( '<h3 class="' . implode(' ', get_post_title_class()) . '"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' );
					else:
						the_title( '<h2 class="' . implode(' ', get_post_title_class()) . '"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
					endif;
				?>

				<?php if (!bs_is_excerpt()): ?>
					<?php bs_post_tags() ?>
				<?php endif; ?>

				<?php if ( 'post' === get_post_type() ): ?>
					<div class="entry-meta">
						<?php if ( is_single() ): ?>
								<?php bs_posted_on(); ?> <?php bs_posted_by(); ?>
						<?php else: ?>
								<?php echo twentyseventeen_time_link(); ?>
						<?php endif;
						?>
					</div><!-- .entry-meta -->
				<?php endif; ?>
		
				<?php
					if (bs_is_excerpt()) {
						the_excerpt();
					} else {
						the_content( __( 'Continue reading', 'twentytwenty' ) );
					}
				?>

				<?php	
					wp_link_pages( array(
						'before'      => '<div class="page-links">' . __( 'Pages:', 'twentyseventeen' ),
						'after'       => '</div>',
						'link_before' => '<span class="page-number">',
						'link_after'  => '</span>',
					) );
				?>
			</div><!-- .entry-content -->
		</div>
	</div>

	<!-- <footer <?php post_footer_class('default-max-width'); ?>> -->
		<?php // bs_entry_meta_footer(); ?>
	<!-- </footer> -->
	<!-- .entry-footer -->
</article><!-- #post-## -->
