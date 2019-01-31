<?php

/**
 Plugin Name: Bootstrap Hooks
 Plugin URI: http://github.com/benignware/wp-bootstrap-hooks
 Description: A collection of action and filters for bootstrap based themes
 Version: 0.1.0-beta.5
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

function wp_bootstrap_options() {
  $defaults = array(
    // Forms
    'search_form_class' => '',
    'search_submit_label' => '<i>🔎</i>',
    'text_input_class' => 'form-control',
    'input_group_class' => 'input-group',
    'input_group_append_class' => 'input-group-append',
    'field_class' => 'form-group',
    'submit_class' => 'btn btn-primary',
    'submit_button_class' => 'btn btn-primary', // FIXME: Redundant option
    // Comments
    'reply_link_class' => 'btn btn-primary btn-xs',
    'comment_label' => 'Comment',
    // Content
    'align_left_class' => 'pull-left',
    'align_right_class' => 'pull-right',
    'align_center_class' => 'mx-auto',
    'img_class' => 'img-fluid',
    'img_caption_tag' => 'figure',
    'img_caption_class' => 'figure',
    'img_caption_text_tag' => 'figcaption',
    'img_caption_text_class' => 'figure-caption',
    'img_caption_img_class' => 'figure-img',
    'table_class' => 'table table-responsive',
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
    'edit_post_link_class' => 'btn btn-sm btn-secondary',
    'edit_post_link_container_class' => 'form-group btn-group btn-group-sm d-block my-2',
    // Tags
    'post_tag_class' => 'badge badge-primary mb-1',
    // Gallery
    'gallery_thumbnail_size' => 'thumbnail',
    'gallery_thumbnail_class' => '',
    'gallery_thumbnail_img_class' => 'img-thumbnail mb-2',
    'gallery_zoom_size' => 'large',
    'carousel_item_class' => 'carousel-item',
    // TODO: Modals
    'close_button_class' => 'btn btn-secondary',
    'close_button_label' => __('Close'),
    // Menu
    'menu_item_class' => 'nav-item',
    'menu_item_link_class' => 'nav-link',
    'sub_menu_tag' => 'ul',
    'sub_menu_class' => 'dropdown-menu',
    'sub_menu_header_class' => 'dropdown-header',
    'sub_menu_item_tag' => 'li',
    'sub_menu_item_class' => '',
    'sub_menu_item_link_class' => 'dropdown-item',
    'divider_class' => 'divider',
    'caret' => '<span class="caret"></span>',
    // Pagination
    'pagination_class' => 'pagination pagination-sm',
    'page_item_class' => 'page-item',
    'page_item_active_class' => 'active',
    'page_link_class' => 'page-link',
    'post_nav_class' => 'pagination',
    'post_nav_tag' => 'ul',
    'post_nav_item_class' => 'page-item',
    'post_nav_item_tag' => 'li',
    'post_nav_link_class' => 'page-link',
    'paginated_class' => 'pagination pagination-sm',
    'paginated_tag' => 'ul',
    'paginated_item_class' => 'page-item',
    'paginated_item_tag' => 'li',
    'paginated_link_class' => 'page-link',
    // Widgets
    'widget_class' => 'card mb-3',
    'widget_modifier_class' => 'card-widget',
    'widget_header_class' => 'card-header',
    'widget_content_class' => 'card-body'
  );

  // Apply option filters
  $options = apply_filters( 'bootstrap_options', $defaults );

  // Sanitize options
  $result = array();
  foreach ( $options as $key => $value ) {
    $result[$key] = $value;
  }

  return $result;
}

// If file resides in template directory, require all immediately
if (preg_match("~^" . preg_quote(get_template_directory(), "~") . "~", __FILE__)) {
  wp_bootstrap_hooks();
}
