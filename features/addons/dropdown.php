<?php

namespace benignware\wp\bootstrap_hooks;

function enqueue_dropdown_assets() {
  wp_enqueue_style(
    'bootstrap-dropdown',
    plugins_url('dropdown.css', __FILE__),
    []
  );

  wp_enqueue_script(
    'bootstrap-dropdown',
    plugins_url('dropdown.js', __FILE__),
    [],
    '',
    true
  );
}

add_action('wp_enqueue_scripts', 'benignware\wp\bootstrap_hooks\enqueue_dropdown_assets');