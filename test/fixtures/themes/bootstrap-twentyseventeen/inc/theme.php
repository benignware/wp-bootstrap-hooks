<?php

function bootstrap_twentyseventeen_color_scheme() {
	return ('dark' === get_theme_mod( 'colorscheme', 'light' ) || is_customize_preview());
}
