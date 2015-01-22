<?php

function wpbsx_dynamic_sidebar_filter( $sidebar_params ) {
  if ( is_admin() ) {
    return $sidebar_params;
  }
 
  global $wp_registered_widgets;
  $widget_id = $sidebar_params[0]['widget_id'];
 
  $wp_registered_widgets[ $widget_id ]['original_callback'] = $wp_registered_widgets[ $widget_id ]['callback'];
  $wp_registered_widgets[ $widget_id ]['callback'] = 'wpbsx_widget_callback_function';
  return $sidebar_params;
 
}
add_filter( 'dynamic_sidebar_params', 'wpbsx_dynamic_sidebar_filter' );

function wpbsx_widget_callback_function() {
 
  global $wp_registered_widgets;
  $original_callback_params = func_get_args();
  $widget_id = $original_callback_params[0]['widget_id'];
 
  $original_callback = $wp_registered_widgets[ $widget_id ]['original_callback'];
  $wp_registered_widgets[ $widget_id ]['callback'] = $original_callback;
 
  $widget_id_base = $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base;
 
  if ( is_callable( $original_callback ) ) {
    ob_start();
    call_user_func_array( $original_callback, $original_callback_params );
    $widget_output = ob_get_clean();
    echo apply_filters( 'widget_output', $widget_output, $widget_id_base, $widget_id );
  }
}

function wpbsx_widget_output_filter( $widget_output, $widget_id_base, $widget_id) {
  if ($widget_output) {
      
    $html = new DOMDocument();
    @$html->loadHTML('<?xml encoding="utf-8" ?>' . $widget_output );
    $body_elem = $html->getElementsByTagName( 'body' )->item(0);
    
    foreach ($body_elem->childNodes as $widget_root_node) {
      
      $panel_body = null;
      $content_fragment = $html->createDocumentFragment();
      $elems = array();
      
      if ($widget_id_base === "search") {
        $class = $widget_root_node->getAttribute("class");
        $class = preg_replace("/panel-default/", "", $class);
        $widget_root_node->setAttribute('class', $class);
        $style = $widget_root_node->getAttribute('style');
        $widget_root_node->setAttribute('style', $style . " border: none;");
      }

      while ($widget_root_node->hasChildNodes()) {
        array_push($elems, $widget_root_node->firstChild);
        $widget_root_node->removeChild($widget_root_node->firstChild);
      }

      foreach ($elems as $widget_content_node) {

        if ($widget_content_node->nodeType === 1 && strpos($widget_content_node->getAttribute('class'), 'panel-heading') !== false) {
          $content_fragment->appendChild($widget_content_node);
          $panel_body = null;
        } else if ( $widget_content_node->nodeType === 1 && $widget_content_node->nodeName === 'ul') {
          $panel_body = null;
          $list_node = $widget_content_node;
          $class = $widget_content_node->getAttribute('class');
          if (strpos($class, 'list-group') !== true) {
            $list_node->setAttribute('class', $class . " list-group");
            foreach ($list_node->childNodes as $list_item_node) {
              if ($list_item_node->nodeType == 1 && $list_item_node->nodeName == "li") {
                // do something with this node
                $list_item_class = $list_item_node->getAttribute('class');
                if (strpos($list_item_class, 'list-group-item') !== true) {
                  $list_item_node->setAttribute('class', $list_item_class . " list-group-item");
                }
              }
            }
            $content_fragment->appendChild($list_node);
          }
        } else if ($widget_content_node->nodeType === 1 || $widget_content_node->nodeType === 3 && strlen(trim($widget_content_node->nodeType === 1)) > 0){
          // Content Node
          
          // Tables
          $table_elems = $widget_content_node->getElementsByTagName( 'table' );
          foreach ($table_elems as $table_elem) {
            $class = $table_elem->getAttribute('class');
            if (!$class || strpos($class, 'table') !== true) {
              $table_elem->setAttribute('class', $class . " table");
            }
          }
          if ($table_elems->length > 0) {
            $last_table_elem = $table_elems->item($table_elems->length - 1);
            $style = $last_table_elem->getAttribute('style');
            $last_table_elem->setAttribute('style', $style . " margin-bottom: 0");
          }
          
          // ADD TO PANEL BODY
          if ($panel_body === null) {
            $panel_body = $html->createElement( 'div' );
            if ($widget_id_base !== "search") {
              $panel_body->setAttribute('class', 'panel-body');              
            }
            $content_fragment->appendChild($panel_body);
          }
          $panel_body->appendChild($widget_content_node);
        }
      }
      
      if ($content_fragment->hasChildNodes()) {
        $widget_root_node->appendChild($content_fragment);  
      }
    }
    $widget_output = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $html->saveHTML())); 
  }
  return $widget_output;
}
add_filter( 'widget_output', 'wpbsx_widget_output_filter', 10, 3 );

function wpbsx_widgets_init() {
  register_sidebar( array(
    'name'          => __( 'Widget Area', 'twentyfifteen' ),
    'id'            => 'sidebar-1',
    'description'   => __( 'Add widgets here to appear in your sidebar.', 'twentyfifteen' ),
    'before_widget' => '<div id="%1$s" class="panel panel-default panel-widget widget %2$s">',
    'after_widget'  => '</div>',
    'before_title'  => '<div class="panel-heading"><h3 class="panel-title widget-title">',
    'after_title'   => '</h3></div>',
    'class'         => '.list-group'
  ) );
}
add_action( 'widgets_init', 'wpbsx_widgets_init', 20);


function wpbsx_search_form( $form ) {
  $form = '<form role="search" method="get" id="searchform" class="searchform" action="' . home_url( '/' ) . '" >
  <label class="screen-reader-text" for="s">' . __( 'Search for:' ) . '</label>
  <div class="input-group">
  <input class="form-control" type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="'. esc_attr__( 'Search' ) .'..."/>
  <span class="input-group-btn">
    <button class="btn btn-default" type="submit" id="searchsubmit" title="' . esc_attr_x( 'Search', 'submit button' ) . '"><i class="glyphicon glyphicon-search"> </i></button>
  </span>
  </div>
  </form>';

  return $form;
}

add_filter( 'get_search_form', 'wpbsx_search_form' );

// Dropdown in widget
// http://webinspiration.gallery/5-tips-build-wordpress-theme-using-bootstrap-3/
function wpbsx_widget_categories_dropdown_args( $args ) {
    if ( array_key_exists( 'class', $args ) ) {
        $args['class'] .= ' form-control';
    } else {
        $args['class'] = 'form-control';
    }
    return $args;
}
add_filter( 'widget_categories_dropdown_args', 'wpbsx_widget_categories_dropdown_args' );



?>