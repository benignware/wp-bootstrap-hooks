<?php
/**
 * Template part for displaying post archives and search results
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('card'); ?>>
	<?php the_post_thumbnail('medium', [
		'class' => 'card-img-top'
	]); ?>

	<div class="card-body">
		<header class="entry-header card-title">
			<?php
			if ( is_sticky() && is_home() && ! is_paged() ) {
				printf( '<span class="sticky-post">%s</span>', _x( 'Featured', 'post', 'twentynineteen' ) );
			}
			the_title( sprintf( '<h5 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h5>' );
			?>
		</header><!-- .entry-header -->

		<div class="entry-content card-text">
			<?php the_excerpt(); ?>
		</div><!-- .entry-content -->

	</div>
	<?php /*
	<footer class="entry-footer card-footer">
		<?php twentynineteen_entry_footer();  ?>
	</footer><!-- .entry-footer -->
	*/ ?>
</article><!-- #post-${ID} -->
