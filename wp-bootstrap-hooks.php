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


function wp_bootstrap_hooks($version = 4) {

  require_once "bs$version/bootstrap-comments.php";
  require_once "bs$version/bootstrap-content.php";
  require_once "bs$version/bootstrap-gallery.php";
  require_once "bs$version/bootstrap-navbar.php";
  require_once "bs$version/bootstrap-pagination.php";
  require_once "bs$version/bootstrap-searchform.php";
  require_once "bs$version/bootstrap-widgets.php";

}