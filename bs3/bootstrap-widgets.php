<?php
/**
 * Dynamic Sidebar Params
 */
function wp_bootstrap_dynamic_sidebar_params( $sidebar_params ) {
  if ( is_admin() ) {
    return $sidebar_params;
  }
  global $wp_registered_widgets;
  $widget_id = $sidebar_params[0]['widget_id'];
  $wp_registered_widgets[ $widget_id ]['original_callback'] = $wp_registered_widgets[ $widget_id ]['callback'];
  $wp_registered_widgets[ $widget_id ]['callback'] = 'wp_bootstrap_widget_callback_function';
  return $sidebar_params;
}
add_filter( 'dynamic_sidebar_params', 'wp_bootstrap_dynamic_sidebar_params' );

// Widget Callback
function wp_bootstrap_widget_callback_function() {
 
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


/**
 * Widget Output
 */
function wp_bootstrap_widget_output( $widget_output, $widget_id_base, $widget_id) {
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
add_filter( 'widget_output', 'wp_bootstrap_widget_output', 10, 3 );


/**
 * Dropdown in widget
 * Reference: http://webinspiration.gallery/5-tips-build-wordpress-theme-using-bootstrap-3/
 */
function wp_bootstrap_widget_categories_dropdown_args( $args ) {
    if ( array_key_exists( 'class', $args ) ) {
        $args['class'] .= ' form-control';
    } else {
        $args['class'] = 'form-control';
    }
    return $args;
}
add_filter( 'widget_categories_dropdown_args', 'wp_bootstrap_widget_categories_dropdown_args' );
?>