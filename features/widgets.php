<?php

use function util\dom\add_class;
use function util\dom\has_class;
use function util\dom\remove_class;
use function util\dom\add_style;
use function util\dom\find_by_class;
use function util\dom\find_all_by_class;
use function util\dom\trim_nodes;
use function util\dom\remove_all;
use function util\dom\inner_root;
use function util\dom\replace_tag;


add_filter('register_sidebar_defaults', function($defaults) {
  if (!current_theme_supports('bootstrap')) {
    return $defaults;
  }

  return array_merge(
    $defaults,
    array(
      // 'before_widget' => "<div class=\"widget widget-$widget_id_base $widget_class $widget_modifier_class\">",
      // 'after_widget'  => '</div>',
      // 'before_title'  => '<div class="' . $widget_header_class . '">',
      // 'after_title'   => '</div>'
      'before_widget'  => '<div id="%1$s" class="widget %2$s card card-%2$s mb-4">',
      'after_widget'   => "</div>\n",
      'before_title'   => '<div class="card-header"><span class="widgettitle">',
      'after_title'    => "</span></div>\n",
    )
  );
});

/**
 * Dynamic sidebar params
 */
add_filter( 'dynamic_sidebar_params', function( $sidebar_params ) {
  if ( !current_theme_supports( 'bootstrap' ) ) {
    return $sidebar_params;
  }

  global $wp_registered_widgets;

  if ( is_admin() ) {
    return $sidebar_params;
  }

  $options = wp_bootstrap_options();
  $widget_class = $options['widget_class'];
  $widget_modifier_class = $options['widget_modifier_class'];
  $widget_header_class = $options['widget_header_class'];

  $widget_id = $sidebar_params[0]['widget_id'];

  foreach($sidebar_params as $index => $widget_params) {
    // $widget_name = isset($widget_params['widget_id']) ? sanitize_title($widget_params['widget_id']) : '';
    // $widget_id_base = is_array($wp_registered_widgets[ $widget_id ]['callback']) ? $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base : ;

    $sidebar_params[$index] = $widget_params;
  }

  $wp_registered_widgets[ $widget_id ]['bootstrap_hooks_original_callback'] = $wp_registered_widgets[ $widget_id ]['callback'];
  $wp_registered_widgets[ $widget_id ]['callback'] = 'wp_bootstrap_widget_callback_function';

  return $sidebar_params;
} );

// Widget Callback
function wp_bootstrap_widget_callback_function() {
  global $wp_registered_widgets;
  $original_callback_params = func_get_args();
  $widget_id = $original_callback_params[0]['widget_id'];

  $original_callback = $wp_registered_widgets[ $widget_id ]['bootstrap_hooks_original_callback'];
  $wp_registered_widgets[ $widget_id ]['callback'] = $original_callback;

  if ( is_callable( $original_callback ) ) {
    ob_start();
    call_user_func_array( $original_callback, $original_callback_params );
    $widget_output = ob_get_contents();
    ob_end_clean();

    $widget_id_base = is_array($wp_registered_widgets[ $widget_id ]['callback']) ? $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base : null;

    echo apply_filters( 'bootstrap_widget_output', $widget_output, $widget_id_base, $widget_id );
  }
}

/**
 * Override Widget Output
 */

add_filter( 'bootstrap_widget_output', function($html, $widget_id_base, $widget_id) {
  if (strlen(trim($html)) === 0) {
    return $html;
  }

  $options = wp_bootstrap_options();

  if (function_exists('wp_bootstrap_the_content')) {
    $html = wp_bootstrap_the_content($html);
  }

  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html );
  $xpath = new DOMXpath($doc);

  $root = $xpath->query('/html/body/*')->item(0);

  if (!$root) {
    return $html;
  }

  $card = find_by_class($root, 'card');
  $has_card = !!$card;

  if (!$has_card) {
    return $html;
  }

  $has_card_header = !!find_by_class($root, 'card-header');
  $has_card_body = !!find_by_class($root, 'card-body');
  $has_card_img = !!find_by_class($root, 'card-img-top');
  // $has_card = !!$xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' card ')]", $root)->item(0);
  // $has_card_header = !!$xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' card-header ')]", $root)->item(0);

  // $card = $xpath->query("*//*[contains(concat(' ', normalize-space(@class), ' '), ' card ')]", $root)->item(0);
  // if ($card) {
  //   remove_class($card, 'card');

  //   $html = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  //   return $html;
  // }

  $widget_root = $doc->getElementById($widget_id);

  if (!$widget_root) {
    $widget_root = $root;
  }

  $class = $widget_root->getAttribute('class');
  // $class = $card->getAttribute('class');
  $classes = array_filter(explode(' ', $class));

  // Extract context class via template option
  $context_class = $options['widget_context_class'];
  $context_class_pattern = preg_replace('~^([\w\d_-]+)%s$~', '~$1([\w\d_-]+)~', $context_class);

  $context = current(
    array_map(
      function($class) use ($context_class_pattern) {
        return preg_replace($context_class_pattern, '$1', $class);
      },
      array_values(
        array_filter($classes, function($class) use ($context_class_pattern) {
          return preg_match($context_class_pattern, $class);
        })
      )
    )
  );

  $scripts = $xpath->query('//script');

  foreach ($scripts as $script) {
    $script->parentNode->removeChild($script);
  }
  

  $inner_root = inner_root($root);
  $result = $inner_root->cloneNode();

  $header = null;

  foreach ($inner_root->childNodes as $index => $child) {
    $node = find_by_class($child, 'widgettitle');

    if (!$node) {
      $node = $xpath->query('//h1|//h2', $child)->item(0);
    }

    if ($node) {
      $has_card_header = !!(has_class($child, 'card-header') || find_by_class($child, 'card-header'));

      if (!$has_card_header) {
        add_class($child, 'card-header');
      }

      $header_node = $node;
      $header = $child;
      break;
    }
  }

  if ($header) {
    $header->parentNode->removeChild($header);
    if ($header_node) {
      try {
        $new_header_node = replace_tag($header_node, 'div');
      } catch (Exception $e) {
      }

      if ($new_header_node && $header_node === $header) {
        $header = $new_header_node;
      }
    }

    // $inner_root = inner_root($root);
    // $result = $inner_root->cloneNode();

    if ($header) {
      $result->appendChild($header->cloneNode(true));
    }
  }

  /*if ($xpath->query('//img')->length === 1) {
    $image = null;
    $xp = '/*[1][count(following-sibling::*[not(local-name() = "script")]) = 0 and count(preceding-sibling::*[not(local-name() = "script")]) = 0]';

    foreach ($inner_root->childNodes as $index => $child) {
      $p = '.' . $xp;

      $first_elements = [];

      while ($element = $xpath->query($p, $child)->item(0)) {
        $p.= $xp;
        $first_elements[] = $element;
      }

      if (count($first_elements) > 0) {
        $first_element = $first_elements[count($first_elements) - 1];
        
        if ($first_element->nodeName === 'img') {
          add_class($child, 'mb-0');

          foreach ($first_elements as $first_element) {
            add_class($first_element, 'mb-0');
          }

          add_class($first_element, $widget_img_class);

          $image = $child;
        }
      }
    }

    if ($image) {
      $image->parentNode->removeChild($image);
      $result->appendChild($image->cloneNode(true));
    }
  }*/

  foreach ($inner_root->childNodes as $index => $child) {
    $is_list_group = $child->nodeName === 'ul' && !has_class($child, $options['menu_class']);

    if ($is_list_group) {
      $is_action_list = $xpath->query('./li/a', $child)->length > 0;
      $list = $is_action_list ? $doc->createElement('div') : $child->cloneNode();

      add_class($list, $options['widget_menu_class']);

      foreach ($child->childNodes as $child) {
        $menu_item_classes = [
          $options['widget_menu_item_class']
        ];
  
        if ($context) {
          $menu_item_classes[] = sprintf($options['widget_menu_item_context_class'], $context);
        }

        if ($is_action_list && $child->nodeType === 1 && $child->nodeName === 'li') {
          $children = $xpath->query('./*', $child);

          if ($children->length > 1) {
            $item = $doc->createElement('div');

            foreach ($children as $node) {
              // remove_class($node, 'nav-link');
              $item->appendChild($node->cloneNode(true));
            }
          } else {
            $item = $children->item(0);
          }

          if ($item->nodeName === 'a') {
            $menu_item_classes[] = $options['widget_menu_item_link_class'];
            // remove_class($item, 'nav-link');
          }
        } else {
          $item = $child;
        }

        if ($item->nodeType === 1) {
          $item->setAttribute('class', implode(' ', $menu_item_classes));
        }

        if ($item) {
          $list->appendChild($item->cloneNode(true));
        }
      }

      $result->appendChild($list);
      
    } else {
      $content = $doc->createElement('div');
      $content->setAttribute('class', 'card-body');
      $content->appendChild($child->cloneNode(true));

      if (strlen(trim($content->textContent))) {
        $result->appendChild($content);
      }
    }
  }

  $inner_root->parentNode->insertBefore($result, $inner_root);

  foreach($scripts as $script) {
    $inner_root->parentNode->insertBefore($script, $inner_root);
  }

  $inner_root->parentNode->removeChild($inner_root);

  $html = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $html;
}, 10, 3 );


/**
 * Dropdown in category widget dropdown
 * Reference: http://webinspiration.gallery/5-tips-build-wordpress-theme-using-bootstrap-3/
 */

add_filter( 'widget_categories_dropdown_args', function( $args ) {
  if (!current_theme_supports('bootstrap')) {
    return $args;
  }

  $options = wp_bootstrap_options();
  $text_input_class = $options['text_input_class'];

  if ( array_key_exists( 'class', $args ) && $args['class']) {
    $args['class'].= ' ' . $text_input_class;
  } else {
    $args['class'] = $text_input_class;
  }

  return $args;
});

/**
 * Custom Styles
 */

// add_action('wp_head', function() {
//   if (!current_theme_supports('bootstrap')) {
//     return;
//   }

//   $options = wp_bootstrap_options();
//   $widget_class = $options['widget_class'];
//   $widget_header_class = $options['widget_header_class'];

//   echo
// <<<EOT
//   <style type="text/css">
//     /* FIXME: https://github.com/twbs/bootstrap/issues/20395 */
//     .$widget_class > .list-group > .list-group-item {
//       border-left: 0;
//       border-right: 0;
//     }
//     .$widget_class > .list-group:first-child .list-group-item:first-of-type {
//       border-top: 0;
//     }
//     .$widget_class > .list-group:last-child .list-group-item:last-of-type {
//       border-bottom: 0;
//     }
//     /* FIXME: https://github.com/twbs/bootstrap/issues/19047 */
//     .$widget_class > .$widget_header_class + .list-group > .list-group-item:first-child,
//     .$widget_class > .list-group + .$widget_class-footer {
//       border-top: 0;
//     }
//     /* Strip borders in search widget */
//     .$widget_class.$widget_class-search {
//       border: 0;
//     }
//     .$widget_class.$widget_class-search .form-group {
//       margin: 0;
//     }

//     .badge {
//       text-overflow: ellipsis;
//       overflow: hidden;
//       max-width: 100%;
//       vertical-align: middle;
//     }
//   </style>
// EOT;
// }, 100);

function bs_topic_count_text_callback($text) {
  return ' dsfasdfa ' . $text;
}

// Tag cloud widget
add_filter( 'widget_tag_cloud_args', function ($args) {
  if (!current_theme_supports('bootstrap')) {
    return $args;
  }

  return array_merge($args, array(
    'format' => 'flat',
  ));
});

add_filter( 'wp_generate_tag_cloud_data', function ($tags_data) {
  if (!current_theme_supports('bootstrap')) {
    return $tags_data;
  }

  $options = wp_bootstrap_options();
  $post_tag_class = $options['post_tag_class'];
  $post_tag_count_class = $options['post_tag_count_class'];

  foreach ($tags_data as $index => $tag_data) {
    $tags_data[$index] = array_merge($tag_data, array(
      'class' => isset($tag_data['class']) ? $tag_data['class'] . ' ' . $post_tag_class : $post_tag_class,
      'show_count' => sprintf('<span class="%s">%d</span>', $post_tag_count_class, $tag_data['real_count'])
    ));
  }

  return $tags_data;
}, 10, 1 );
