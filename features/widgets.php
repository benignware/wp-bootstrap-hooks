<?php

use function util\dom\add_class;
use function util\dom\add_style;
use function util\dom\find_by_class;
use function util\dom\trim_nodes;
use function util\dom\remove_all;
use function util\dom\nested_root;

add_filter('register_sidebar_defaults', function($defaults) {
  return array_merge(
    $defaults,
    array(
      // 'before_widget' => "<div class=\"widget widget-$widget_id_base $widget_class $widget_modifier_class\">",
      // 'after_widget'  => '</div>',
      // 'before_title'  => '<div class="' . $widget_header_class . '">',
      // 'after_title'   => '</div>'
      'before_widget'  => '<div id="%1$s" class="widget %2$s card card-%2$s mb-4">',
      'after_widget'   => "</div>\n",
      'before_title'   => '<div class="card-header"><span class="widgettitle card-title">',
      'after_title'    => "</span></div>\n",
    )
  );
});

/**
 * Dynamic sidebar params
 */
function wp_bootstrap_dynamic_sidebar_params( $sidebar_params ) {
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
    $widget_name = isset($widget_params['widget_id']) ? sanitize_title($widget_params['widget_id']) : '';
    $widget_id_base = $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base;

    $sidebar_params[$index] = $widget_params;
  }

  $wp_registered_widgets[ $widget_id ]['bootstrap_hooks_original_callback'] = $wp_registered_widgets[ $widget_id ]['callback'];
  $wp_registered_widgets[ $widget_id ]['callback'] = 'wp_bootstrap_widget_callback_function';

  return $sidebar_params;
}
add_filter( 'dynamic_sidebar_params', 'wp_bootstrap_dynamic_sidebar_params' );

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

    $widget_id_base = $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base;

    echo apply_filters( 'bootstrap_widget_output', $widget_output, $widget_id_base, $widget_id );
  }
}

/**
 * Override Widget Output
 */
function wp_bootstrap_widget_output($html, $widget_id_base, $widget_id) {
  if (strlen(trim($html)) === 0) {
    return $html;
  }

  $options = wp_bootstrap_options();

  if (function_exists('wp_bootstrap_the_content')) {
    $html = wp_bootstrap_the_content($html);
  }

  $modifier = preg_replace("~_~Ui", "-", $widget_id_base);

  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html );
  $xpath = new DOMXpath($doc);
  $root = $doc->getElementsByTagName( 'body' )->item(0)->firstChild;

  if (!$root) {
    return $html;
  }

  $result = $root->cloneNode();

  $inner_roots = $xpath->query('//div[1][count(following-sibling::*) = 0]', $root);
  $inner_root = $inner_roots->item($inner_roots->length - 1);

  $header = null;

  foreach ($inner_root->childNodes as $index => $child) {
    $node = find_by_class($child, 'widgettitle');

    if ($node) {
      $header = $child;
      break;
    }
  }

  if ($header) {
    $result->appendChild($header->cloneNode(true));
    $header->parentNode->removeChild($header);
  }

  $inner_roots = $xpath->query('//div[1][count(following-sibling::*[not(local-name() = "script")]) = 0]', $root);
  $inner_root = $inner_roots->item($inner_roots->length - 1);

  $content = $doc->createElement('div');
  $content->setAttribute('class', 'card-body');

  foreach ($inner_root->childNodes as $index => $child) {
    if ($child->nodeType !== 1) {
      $content->appendChild($child->cloneNode(true));
      continue;
    }

    if ($child->nodeName === 'ul') {
      $is_action_list = $xpath->query('./li', $child)->length === $xpath->query('./li/a', $child)->length;
      $list = $is_action_list ? $doc->createElement('div') : $child->cloneNode();

      add_class($list, 'list-group list-group-flush');

      foreach ($child->childNodes as $child) {
        if ($is_action_list && $child->nodeType === 1) {
          $item = $xpath->query('./a', $child)->item(0);

          if ($item) {
            add_class($item, 'list-group-item list-group-item-action');
          }
        } else {
          $item = $child;

          if ($item->nodeType === 1) {
            add_class($item, 'list-group-item');
          }
        }

        if ($item) {
          $list->appendChild($item->cloneNode(true));
        }
      }

      $result->appendChild($list);
    } else {
      $content->appendChild($child->cloneNode(true));
    }
  }

  if (strlen(trim($content->textContent))) {
    $result->appendChild($content);
  }

  $root->parentNode->insertBefore($result, $root);
  $root->parentNode->removeChild($root);

  $html = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $html;
}
add_filter( 'bootstrap_widget_output', 'wp_bootstrap_widget_output', 10, 3 );


/**
 * Dropdown in category widget dropdown
 * Reference: http://webinspiration.gallery/5-tips-build-wordpress-theme-using-bootstrap-3/
 */
function wp_bootstrap_widget_categories_dropdown_args( $args ) {
  if ( array_key_exists( 'class', $args ) && $args['class']) {
    $args['class'].= ' form-control';
  } else {
    $args['class'] = 'form-control';
  }
  return $args;
}
add_filter( 'widget_categories_dropdown_args', 'wp_bootstrap_widget_categories_dropdown_args' );

/**
 * Custom Styles
 */
function wp_bootstrap_widget_styles() {
  $options = wp_bootstrap_options();
  $widget_class = $options['widget_class'];
  $widget_header_class = $options['widget_header_class'];
  echo
<<<EOT
  <style type="text/css">
    /* FIXME: https://github.com/twbs/bootstrap/issues/20395 */
    .$widget_class > .list-group > .list-group-item {
      border-left: 0;
      border-right: 0;
    }
    .$widget_class > .list-group:first-child .list-group-item:first-of-type {
      border-top: 0;
    }
    .$widget_class > .list-group:last-child .list-group-item:last-of-type {
      border-bottom: 0;
    }
    /* FIXME: https://github.com/twbs/bootstrap/issues/19047 */
    .$widget_class > .$widget_header_class + .list-group > .list-group-item:first-child,
    .$widget_class > .list-group + .$widget_class-footer {
      border-top: 0;
    }
    /* Strip borders in search widget */
    .$widget_class.$widget_class-search {
      border: 0;
    }
    .$widget_class.$widget_class-search .form-group {
      margin: 0;
    }

    .badge {
      text-overflow: ellipsis;
      overflow: hidden;
      max-width: 100%;
      vertical-align: middle;
    }
  </style>
EOT;
}
add_action('wp_head', 'wp_bootstrap_widget_styles', 100);


// Tag cloud widget
add_filter( 'widget_tag_cloud_args', function ($args) {
  $tagcloud_class = 'test';

  return array_merge($args, array(
    'format' => 'flat'
  ));
});

add_filter( 'wp_generate_tag_cloud_data', function ($tags_data) {
  $options = wp_bootstrap_options();
  $post_tag_class = $options['post_tag_class'];

  foreach ($tags_data as $index => $tag_data) {
    $tags_data[$index] = array_merge($tag_data, array(
      'class' => isset($tag_data['class']) ? $tag_data['class'] . ' ' . $post_tag_class : $post_tag_class
    ));
  }

  return $tags_data;
}, 10, 1 );
?>
