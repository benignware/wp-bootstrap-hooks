<?php

namespace benignware\wp\bootstrap_hooks;

require_once 'content.php';

function get_options($context = null) {
  $content = wp_bootstrap_the_content($content);

  return $content;
}

function get_markup($content) {
  $content = wp_bootstrap_the_content($content);

  return $content;
}

function get_login_form($args = []) {
  ob_start();
  wp_login_form($args);
  $content = ob_get_clean();
  $content = get_markup($content);

  return $content;
}