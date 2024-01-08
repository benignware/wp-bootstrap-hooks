<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'twentynineteen' ); ?></a>

		<header id="masthead" class="site-header sticky-top bg-body-tertiary border-bottom">

			<div class="navbar navbar-expand-md">
				<?php get_template_part( 'template-parts/header/site', 'navbar' ); ?>
			</div><!-- .layout-wrap -->
		</header><!-- #masthead -->

    <?php if ( is_singular() && has_post_thumbnail() ) : ?>
      <div class="site-featured-image">
        <?php
          the_post_thumbnail('post-thumbnail', [
            'style' => 'aspect-ratio: 16/9; object-fit: cover'
          ]);
          the_post();
          // $discussion = ! is_page() && has_post_thumbnail() ? twentynineteen_get_discussion_data() : null;

          $classes = 'entry-header';
          // if ( ! empty( $discussion ) && absint( $discussion->responses ) > 0 ) {
          // 	$classes = 'entry-header has-discussion';
          // }
        ?>
        <div class="<?php echo $classes; ?>">
          <?php get_template_part( 'template-parts/header/entry', 'header' ); ?>
        </div><!-- .entry-header -->
        <?php rewind_posts(); ?>
      </div>
    <?php endif; ?>

	<div id="content" class="site-content">