<?php
/**
 * Displays top navigation
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.2
 */
?>
<nav id="site-navigation" class="main-navigation navbar navbar-expand-md navbar-dark bg-dark" role="navigation" aria-label="<?php esc_attr_e( 'Top Menu', 'twentyseventeen' ); ?>">
	<div class="container">
		<a class="navbar-brand site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" title="<?php bloginfo( 'name' ); ?>">
			<?php bloginfo( 'name' ); ?>
		</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarCollapse">
			<?php
				// Primary navigation menu.
				wp_nav_menu( array(
					'theme_location' => 'top',
					'menu_id'        => 'top-menu'
				));
			?>
		</div>
	</div>
</nav><!-- #site-navigation -->
