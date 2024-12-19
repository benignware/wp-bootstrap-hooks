<?php

namespace benignware\wp\bootstrap_hooks;

function is_block_editor() {
	global $current_screen;

	if (!function_exists('get_current_screen')) {
		return false;
	}
	
	$current_screen = get_current_screen();

	if ($current_screen
    && method_exists($current_screen, 'is_block_editor')
    && $current_screen->is_block_editor()
  ) {
		return true;
	}

	return false;
}
