<?php

function wp_bootstrap_dynamic_sidebar_params( $sidebar_params ) {
  if ( is_admin() ) {
    return $sidebar_params;
  }
  foreach($sidebar_params as $index => $widget_params) {
    $widget_name = isset($widget_params['widget_name']) ? sanitize_title($widget_params['widget_name']) : ''; 
    $sidebar_params[$index] = array_merge( 
      $widget_params, 
      array(
        //'before_widget' => '<div class="widget">',
        //'before_widget' => "<div class=\"widget widget-$widget_name card card-$widget_name\">",
        'after_widget'  => '</div>',
        'before_title'  => '<div class="card-header">',
        'after_title'   => '</div>',
        //'class'         => '.testo'
      )
    );
  }
  global $wp_registered_widgets;
  $widget_id = $sidebar_params[0]['widget_id'];
  $wp_registered_widgets[ $widget_id ]['original_callback'] = $wp_registered_widgets[ $widget_id ]['callback'];
  $wp_registered_widgets[ $widget_id ]['callback'] = 'wp_bootstrap_widget_callback_function';
  return $sidebar_params;
}


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

function wp_bootstrap_widget_output( $widget_output, $widget_id_base, $widget_id) {
  $component_class = 'card';
  $widget_id_base_hyphens = preg_replace("~_~Ui", "-", $widget_id_base);
  
  if ($widget_output) {
    $html = new DOMDocument();
    @$html->loadHTML('<?xml encoding="utf-8" ?>' . $widget_output );
    $html_xpath = new DOMXpath($html);
    
    $body_elem = $html->getElementsByTagName( 'body' )->item(0);
    $widget_root_node = $body_elem->firstChild;
    $panel_body = null;
    
    $content_fragment = $html->createDocumentFragment();
    $elems = array();
    
    if ($widget_root_node) {
      
      if ($widget_id_base === "search") {
        $class = $widget_root_node->getAttribute("class");
        $class = preg_replace("/card/Ui", "", $class);
        $class.= strlen($class) > 0 ? " " . $class : $class; 
        $widget_root_node->setAttribute('class', $class);
        $style = $widget_root_node->getAttribute('style');
        $widget_root_node->setAttribute('style', $style . " border: none;");
      }
      
      $content_parent = $html_xpath->query("//*[contains(@class, '$component_class') and not(contains(@class, '$component_class-'))]")->item(0);
      
      if (!$content_parent) {
        // If no element with component class is found yet, we'll add one and wrap the actual content in it
        $content_parent = $html->createElement('div');
        $content_parent->setAttribute("class", $component_class);
        $content_children = array();
        foreach ($widget_root_node->childNodes as $child) {
          array_push($content_children, $child);
        }
        foreach ($content_children as $child) {
          $content_parent->appendChild($child);
        }
        $widget_root_node->appendChild($content_parent);
      }
      
      // Setup component extra classes
      $component_extra_classes = array($widget_id_base_hyphens, "widget");
      // Try to find additional classes that specify the widget more further
      $widget_class_elem = $html_xpath->query("//*[contains(@class, '$widget_id_base') or contains(@class, '$widget_id_base_hyphens')]")->item(0);
      if ($widget_class_elem) {
        $widget_class_elem_class_attr = $widget_class_elem->getAttribute('class');
        if ($widget_class_elem_class_attr !== null) {
          $widget_class_elem_classes = explode(" ", $widget_class_elem_class_attr);
          $widget_leading_class = "";
          foreach ($widget_class_elem_classes as $widget_class_elem_class) {
            $matched = preg_match("~(" . preg_quote($widget_id_base, "~") . "|" . preg_quote($widget_id_base_hyphens) . ")$~", $widget_class_elem_class, $match);
            if ($matched) {
              $widget_leading_class = $widget_class_elem_class;
              break;
            }
          }
          foreach ($widget_class_elem_classes as $widget_class_elem_class) {
            $matched = preg_match("~^" .preg_quote($widget_leading_class, "~") . "[-_]~", $widget_class_elem_class, $match);
            if ($match) {
              // Additional class found:
              $widget_base_class = (strpos($widget_class_elem_class, $widget_id_base_hyphens) !== false) ? $widget_id_base_hyphens : $widget_id_base;
              $widget_additional_class = preg_replace("~.*(" . preg_quote($widget_base_class, "~") . ")~", "$1", $widget_class_elem_class);
              array_push($component_extra_classes, $widget_additional_class);
            }
          }
          }
      }
      $component_extra_classes = array_unique($component_extra_classes);
      // Clean up extra classes
      foreach ($component_extra_classes as $index => $component_extra_class) {
        // FIXME: Remove null-prefix, i.e. Instagram widget
        $component_extra_class = preg_replace("~^null-~", "", $component_extra_class);
        // Remove -widget suffix
        $component_extra_class = preg_replace("~-widget$~Ui", "", $component_extra_class);
        // Remove -widget prefix
        $component_extra_class = preg_replace("~^widget-~Ui", "", $component_extra_class);
        $component_extra_classes[$index] = $component_extra_class;
      }
      
      // Add component widget class
      $content_parent_classes = explode(" ", $content_parent->getAttribute('class'));
      foreach ($component_extra_classes as $component_extra_class) {
        array_push($content_parent_classes, $component_class . "-" . $component_extra_class);
      }
      $content_parent->setAttribute('class', implode(" ", $content_parent_classes));
      
      // Clean up header position in markup
      $content_header = $html_xpath->query("//*[contains(@class, '$component_class-header')]")->item(0);
      if ($content_header && $content_parent->firstChild !== $content_header) {
        if ($content_parent->firstChild !== null) {
          $content_parent->insertBefore($content_header, $content_parent->firstChild);
        } else {
          $content_parent->appendChild($content_header);
        }
      }
      
      // Collect content elems
      foreach ($content_parent->childNodes as $child) {
        if ($child !== $content_header) {
          array_push($elems, $child);
        }
      }
      
      $panel_body = null;
      foreach ($elems as $widget_content_node) {
        
        if ($widget_content_node->nodeType === 1 && strpos($widget_content_node->getAttribute('class'), 'card-header') !== false) {
          $content_fragment->appendChild($widget_content_node);
          $panel_body = null;
          
        } else {
          
          $list_node = $widget_content_node->nodeType === 1 && $widget_content_node->nodeName === 'ul' ? $widget_content_node : $html_xpath->query("//ul", $widget_content_node)->item(0);
          if ( $list_node ) {
            // List Group
            $panel_body = null;
            $class = $list_node->getAttribute('class');
            if (strpos($class, 'list-group') !== true) {
              $list_node->setAttribute('class', $class . " list-group list-group-flush");
              foreach ($list_node->childNodes as $list_item_node) {
                if ($list_item_node->nodeType == 1 && $list_item_node->nodeName == "li") {
                  // do something with this node
                  $list_item_class = $list_item_node->getAttribute('class');
                  if (strpos($list_item_class, 'list-group-item') !== true) {
                    $list_item_node->setAttribute('class', $list_item_class . " list-group-item");
                  }
                }
              }
              // Rest
              $content_fragment->appendChild($list_node);
            }
          }
          
          
          if ((!$list_node || $widget_content_node !== $list_node) && ($widget_content_node->nodeType === 1 || $widget_content_node->nodeType === 3 && strlen(trim($widget_content_node->nodeType === 1)) > 0)) {
              
            // Content Block
            if ($panel_body == null) {
              $panel_body = $html->createElement( 'div' );
              if ($widget_id_base !== "search") {
                $panel_body->setAttribute('class', 'card-block');              
              }
              // If a listnode has been extracted before, prepend to the listnode
              if ($list_node && $widget_content_node !== $list_node) {
                $content_fragment->insertBefore($panel_body, $list_node);
              } else {
                // Otherwise append to content fragment
                $content_fragment->appendChild($panel_body);
              }
            }
          
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
            
            $widget_content_node_class = $widget_content_node->getAttribute('class');
            
            $panel_body->appendChild($widget_content_node);
          } else {
            // Other
          }
        }
      }
      
      if ($content_fragment->hasChildNodes()) {
        $content_parent->appendChild($content_fragment);
      }
    }
    
    $widget_output = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $html->saveHTML());
  }
  
  return $widget_output;
  
}



function wp_bootstrap_widgets_init() {
  register_sidebar( array(
    'name'          => __( 'Widget Area', 'twentyfifteen' ),
    'id'            => 'sidebar-1',
    'description'   => __( 'Add widgets here to appear in your sidebar.', 'twentyfifteen' ),
    'before_widget' => '<div id="%1$s" class="card widget %2$s">',
    'after_widget'  => '</div>',
    'before_title'  => '<div class="card-header"><span class="card-title">',
    'after_title'   => '</span></div>',
    'class'         => '.list-group'
  ) );
}

function wp_bootstrap_get_search_form( $form ) {
  $form = '<form role="search" method="get" id="searchform" class="searchform" action="' . home_url( '/' ) . '" >
  <label class="screen-reader-text" for="s">' . __( 'Search for:' ) . '</label>
  <div class="form-group">
    <div class="input-group">
    <input class="form-control" type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="'. esc_attr__( 'Search' ) .'..."/>
    <span class="input-group-btn">
      <button class="btn btn-default" type="submit" id="searchsubmit" title="' . esc_attr_x( 'Search', 'submit button' ) . '"><i>🔎</i></button>
    </span>
    </div>
  </div>
  </form>';
  return $form;
}



// Dropdown in widget
// http://webinspiration.gallery/5-tips-build-wordpress-theme-using-bootstrap-3/
function wp_bootstrap_widget_categories_dropdown_args( $args ) {
    if ( array_key_exists( 'class', $args ) ) {
        $args['class'] .= ' form-control';
    } else {
        $args['class'] = 'form-control';
    }
    return $args;
}
?>