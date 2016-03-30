<?php

require_once "wp_bootstrap_gallery.php";
require_once "wp_bootstrap_widgets.php";
require_once "wp_bootstrap_navbar.php";
require_once "wp_bootstrap_comments.php";
require_once "wp_bootstrap_content.php";


function wp_bootstrap_hooks() {

  add_filter( 'comment_form_default_fields', 'wpbsx_comment_form_default_fields');
  add_filter( 'comment_form_defaults', 'wpbsx_comment_form_defaults' );
  add_action('comment_form_after', 'wpbsx_comment_form_after' );
  add_action('wp_list_comments_args', 'wp_bootstrap_list_comments_args' );
  add_filter('comment_reply_link', 'wp_bootstrap_comment_reply_link', 10, 4);
  
  add_filter('the_content', 'wp_bootstrap_the_content', 11);
  add_filter('post_gallery', 'wpbsx_post_gallery', 10, 2);
  
  add_filter('wp_nav_menu_args', 'wpbsx_nav_menu_args');
  
  add_filter( 'dynamic_sidebar_params', 'wpbsx_dynamic_sidebar_filter' );
  add_filter( 'widget_output', 'wpbsx_widget_output_filter', 10, 3 );
  add_action( 'widgets_init', 'wpbsx_widgets_init', 20);
  add_filter( 'get_search_form', 'wpbsx_search_form' );
  add_filter( 'widget_categories_dropdown_args', 'wpbsx_widget_categories_dropdown_args' );
  
}