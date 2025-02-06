<?php

namespace benignware\wp\bootstrap_hooks;

require_once 'content.php';

function get_options($context = null) {
  $content = wp_bootstrap_the_content($content);

  return $content;
}

function get_block_options($block) {
  return apply_filters(
    'bootstrap_block_options',
    wp_bootstrap_options(),
    $block
  );
}

function get_markup($content) {
  $content = the_content_alerts($content);
  $content = the_content_images($content);
  $content = the_content_tables($content);
  $content = the_content_forms($content);
  $content = the_content_buttons($content);

  return $content;
}

function get_login_form($args = []) {
  ob_start();
  wp_login_form($args);
  $content = ob_get_clean();
  $content = get_markup($content);

  return $content;
}