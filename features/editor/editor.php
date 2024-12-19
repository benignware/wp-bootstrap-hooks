<?php

namespace benignware\wp\bootstrap_hooks;

function enqueue_editor_block_assets() {
	if (is_block_editor()) {
    wp_enqueue_script( 'bootstrap-editor',  plugin_dir_url( dirname(__FILE__) ) . '/assets/editor.js' );
	}
}

add_action( 'enqueue_block_assets', 'benignware\wp\bootstrap_hooks\enqueue_editor_block_assets' );
