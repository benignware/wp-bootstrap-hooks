<?php

/**
 Plugin Name: Bootstrap Hooks
 Plugin URI: http://github.com/benignware/wp-bootstrap-hooks
 Description: A collection of action and filters for bootstrap based themes
 Version: 0.0.1
 Author: Rafael Nowrotek, Benignware
 Author URI: http://benignware.com
 License: MIT
*/

function wp_bootstrap_hooks() {
  $args = func_get_args();
  if (!count($args)) {
    $args = array('comments', 'content', 'forms', 'gallery', 'menu', 'pagination', 'widgets');
  }
  foreach ($args as $arg) {
    require_once "bootstrap-$arg.php";
  }
}

// If file resides in template directory, require all immediately
if (preg_match("~^" . preg_quote(get_template_directory(), "~") . "~", __FILE__)) {
  wp_bootstrap_hooks();
}