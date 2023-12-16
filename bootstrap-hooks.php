<?php

/**
 Plugin Name: Bootstrap Hooks
 Plugin URI: http://github.com/benignware/wp-bootstrap-hooks
 Description: A collection of action and filters for bootstrap based themes
 Version: 1.0.0-beta.26
 Author: Rafael Nowrotek, Benignware
 Author URI: http://benignware.com
 License: MIT
*/

require_once 'lib/util/dom.php';
require_once 'lib/util/colors.php';
require_once 'lib/helpers.php';
require_once 'features/functions.php';

function wp_bootstrap_hooks() {
  $args = func_get_args();

  if (!count($args)) {
    $args = array(
      'content',
      'functions',
      'comments',
      'blocks',
      'forms',
      // 'gallery',
      'navigation',
      'pagination',
      'widgets',
      'header',
      'taxonomies',
      'thumbnails'
    );
  }

  foreach ($args as $arg) {
    require_once "features/$arg.php";
  }
}

function wp_bootstrap_options() {
  global $_wp_theme_features;

  $defaults = array(
    // Buttons
    'button_class' => 'btn btn-%1$s',
    'button_outline_class' => 'btn btn-outline-%1$s',
    // WP Forms REMOVE
    'search_form_class' => '',
    'search_submit_label' => __('Search'),
    // Forms
    'label_class' => 'form-label',
    'text_input_class' => 'form-control',
    'input_group_class' => 'input-group',
    'checkbox_container_class' => 'form-check',
    'checkbox_input_class' => 'form-check-input',
    'checkbox_label_class' => 'form-check-label',
    'field_class' => 'form-group',
    'submit_class' => 'btn btn-primary',
    'submit_button_class' => 'btn btn-primary', // FIXME: Redundant option
    // Comments
    'reply_link_class' => 'btn btn-primary btn-xs',
    'comment_label' => 'Comment',
    // Content
    'align_left_class' => 'float-left',
    'align_right_class' => 'float-right',
    'align_center_class' => 'mx-auto',
    'img_class' => 'img-fluid',
    'img_caption_tag' => 'figure',
    'img_caption_class' => 'figure',
    'img_caption_text_tag' => 'figcaption',
    'img_caption_text_class' => 'figure-caption',
    'img_caption_img_class' => 'figure-img',
    'table_class' => 'table',
    'table_striped_class' => 'table-striped',
    'table_container_tag' => 'div',
    'table_container_class' => 'table-responsive',
    'blockquote_class' => 'blockquote',
    'blockquote_footer_tag' => 'footer',
    'blockquote_footer_class' => 'blockquote-footer',
    // Embeds
    'embed_class' => 'embed-responsive-item',
    'embed_container_class' => 'embed-responsive',
    'embed_preset_ratios' => array('21:9', '16:9', '4:3', '1:1'),
    'embed_ratio_class_prefix' => 'embed-responsive-',
    'embed_ratio_class_divider' => 'by',
    // Edit post link
    'edit_post_link_class' => 'btn btn-sm btn-outline-secondary',
    'edit_post_link_container_class' => 'btn-group btn-group-sm d-block my-2',
    // Tags
    'post_tag_class' => 'btn btn-sm btn-outline-primary text-wrap mb-1',
    'post_tag_count_class' => 'badge bg-primary',
    // Gallery
    'gallery_thumbnail_size' => 'thumbnail',
    'gallery_thumbnail_class' => '',
    'gallery_thumbnail_img_class' => 'img-thumbnail mb-2',
    'gallery_zoom_size' => 'large',
    'carousel_item_class' => 'carousel-item',
    // Grid
    'columns_class' => 'row',
    'column_class' => 'col col-%2$s-%1$s',
    // TODO: Modals
    'close_button_class' => 'btn btn-secondary',
    'close_button_label' => __('Close'),
    // Menu
    'menu_class' => 'nav',
    'menu_item_class' => 'nav-item',
    'menu_item_link_class' => 'nav-link',
    'menu_item_link_active_class' => 'active',
    'sub_menu_tag' => 'ul',
    'sub_menu_class' => 'dropdown-menu',
    'sub_menu_header_class' => 'dropdown-header',
    'sub_menu_item_tag' => 'li',
    'sub_menu_item_class' => '',
    'sub_menu_item_link_class' => 'dropdown-item',
    'divider_class' => 'divider',
    // Pagination
    'pagination_class' => 'pagination',
    'page_item_class' => 'page-item',
    'page_item_active_class' => 'active',
    'page_link_class' => 'page-link',
    'post_nav_class' => 'pagination',
    'post_nav_tag' => 'ul',
    'post_nav_item_class' => 'page-item',
    'post_nav_item_tag' => 'li',
    'post_nav_link_class' => 'page-link',
    'next_posts_link_class' => 'btn btn-outline-secondary float-right',
    'previous_posts_link_class' => 'btn btn-outline-secondary float-left',
    // Deprecated
    'paginated_class' => 'pagination',
    'paginated_tag' => 'ul',
    'paginated_item_class' => 'page-item',
    'paginated_item_tag' => 'li',
    'paginated_link_class' => 'page-link',
    // Widgets
    'widget_class' => 'card mb-3',
    'widget_context_class' => 'bg-%s', // Used to retrieve current context
    'widget_modifier_class' => 'card-widget',
    'widget_header_class' => 'card-header',
    'widget_content_class' => 'card-body',
    'widget_menu_class' => 'list-group list-group-flush',
    'widget_menu_item_class' => 'list-group-item',
    'widget_menu_item_link_class' => 'list-group-item-action',
    'widget_menu_item_context_class' => 'list-group-item-%s',
    // Categories
    'category_list_class' => 'breadcrumb',
    'category_list_item_class' => 'breadcrumb-item',
    'category_list_item_active_class' => 'active',
  );

  $args = array_merge(
    [
      'version' => 5
    ],
    isset($_wp_theme_features[ 'bootstrap' ]) && is_array($_wp_theme_features[ 'bootstrap' ])
      ? $_wp_theme_features[ 'bootstrap' ]
      : []
  );

  // Apply option filters
  $options = apply_filters( 'bootstrap_options', $defaults, $args );

  // Sanitize options
  $result = array();

  foreach ( $options as $key => $value ) {
    $result[$key] = $value;
  }

  return $result;
}

wp_bootstrap_hooks();

add_filter('bootstrap_options', function($options, $args) {
  if ($args['version'] <= 4) {
    return array_merge($options, [
      'post_tag_class' => 'badge badge-secondary text-wrap mb-1',
      'input_group_append_class' => 'input-group-append',
      'caret' => '<span class="%s"></span>',
      'caret_class' => 'caret',
    ]);
  }

  return $options;
}, 1, 2);
