<?php



function wp_bootstrap_hooks($version = 4) {

  require_once "bs$version/wp_bootstrap_comments.php";
  require_once "bs$version/wp_bootstrap_content.php";
  require_once "bs$version/wp_bootstrap_gallery.php";
  require_once "bs$version/wp_bootstrap_navbar.php";
  require_once "bs$version/wp_bootstrap_widgets.php";

  add_filter( 'comment_form_default_fields', 'wp_bootstrap_comment_form_default_fields' );
  add_filter( 'comment_form_defaults', 'wp_bootstrap_comment_form_defaults' );
  add_action( 'comment_form_after', 'wp_bootstrap_comment_form_after' );
  add_action( 'wp_list_comments_args', 'wp_bootstrap_list_comments_args' );
  add_filter( 'comment_reply_link', 'wp_bootstrap_comment_reply_link', 10, 4 );
  
  add_filter( 'the_content', 'wp_bootstrap_the_content', 11 );
  add_filter( 'post_gallery', 'wp_bootstrap_post_gallery', 10, 2 );
  
  add_filter( 'wp_nav_menu_args', 'wp_bootstrap_nav_menu_args' );
  
  add_filter( 'dynamic_sidebar_params', 'wp_bootstrap_dynamic_sidebar_params' );
  add_filter( 'widget_output', 'wp_bootstrap_widget_output', 10, 3 );
  add_action( 'widgets_init', 'wp_bootstrap_widgets_init', 20 );
  add_filter( 'get_search_form', 'wp_bootstrap_get_search_form' );
  add_filter( 'widget_categories_dropdown_args', 'wp_bootstrap_widget_categories_dropdown_args' );
  
}