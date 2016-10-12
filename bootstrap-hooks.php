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

  require_once "bootstrap-comments.php";
  require_once "bootstrap-content.php";
  require_once "bootstrap-gallery.php";
  require_once "bootstrap-menu.php";
  require_once "bootstrap-pagination.php";
  require_once "bootstrap-searchform.php";
  require_once "bootstrap-widgets.php";

}