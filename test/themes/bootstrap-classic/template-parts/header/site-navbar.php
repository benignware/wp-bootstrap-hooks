<?php
/**
 * Displays header site branding
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */
?>
<div class="container">

	<div class="navbar-brand d-flex justify-content-center">
		<?php if ( has_custom_logo() ) : ?>
			<div class="site-logo d-flex align-items-center"><?php the_custom_logo(); ?></div>
		<?php endif; ?>
		<div class="site-title d-flex align-items-center"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></div>
	</div>

	<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>

	<div id="navbarCollapse" class="collapse navbar-collapse">
		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Top Menu', 'twentynineteen' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_class'     => 'main-menu navbar-nav',
						'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
					)
				);
				?>
			</nav><!-- #site-navigation -->
		<?php endif; ?>
		<?php if ( has_nav_menu( 'social' ) ) : ?>
			<nav class="social-navigation" aria-label="<?php esc_attr_e( 'Social Links Menu', 'twentynineteen' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'social',
						'menu_class'     => 'social-links-menu',
						'link_before'    => '<span class="screen-reader-text">',
						'link_after'     => '</span>' . twentynineteen_get_icon_svg( 'link' ),
						'depth'          => 1,
					)
				);
				?>
			</nav><!-- .social-navigation -->
		<?php endif;  ?>
	</div><!-- .navbar-collapse -->
</div><!-- .container -->
