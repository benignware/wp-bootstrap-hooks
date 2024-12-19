<?php

use function benignware\wp\bootstrap_hooks\add_class;
use function benignware\wp\bootstrap_hooks\has_class;


add_filter( 'wp_link_pages', function ( $output, $args ) {
  if (!current_theme_supports('bootstrap')) {
    return $output;
  }

  if (!$output) {
    return $output;
  }

  // FIXME: Safe extract
  extract(wp_bootstrap_options());
  extract($args);

  // Parse DOM
  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $output );

  $element = $doc->createElement($paginated_tag);
  $element->setAttribute('class', $paginated_class);

  $body_element = $doc->getElementsByTagName('body')->item(0);
  $link_element = $doc->getElementsByTagName('a')->item(0);
  $container_element = $link_element ? $link_element->parentNode : $body_element;

  foreach ($container_element->childNodes as $child_element) {
    $clone = $child_element->cloneNode(true);
    if ($child_element->nodeType == 1 && strtolower($child_element->tagName) == 'a') {
      // Add link class
      $link_element_class = $clone->getAttribute('class');
      $link_element_class.= trim(' ' . $paginated_link_class);
      $clone->setAttribute('class', $link_element_class);
      // Create item
      $item_element = $doc->createElement($paginated_item_tag);
      $item_element->setAttribute('class', $paginated_item_class);
      $item_element->appendChild($clone);
      $element->appendChild($item_element);
    } else {
      // Bootstrap pagination does not support other elements
      //$element->appendChild($clone);
    }
  }

  if ($body_element) {
    // Remove all children from body element
    while ($body_element->hasChildNodes()) {
      $body_element->removeChild($body_element->firstChild);
    }
    // Add paginated element
    $body_element->appendChild($element);
  }

  return preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());
}, 10, 2 );


// Next posts link class
add_filter('next_posts_link_attributes', function($attrs = '') {
  if (!current_theme_supports('bootstrap')) {
    return $attrs;
  }

  $options = wp_bootstrap_options();
  $next_posts_link_class = $options['next_posts_link_class'];

  if (strpos($attrs, "class=") !== FALSE) {
    $attrs = preg_replace('~class=["\']([^"\']*)~', '$1 ' . $next_posts_link_class);
  } else {
    $attrs.= ' class="' . $next_posts_link_class . '"';
  }

  return trim($attrs);
});

// Previous posts link class
add_filter('previous_posts_link_attributes', function($attrs = '') {
  if (!current_theme_supports('bootstrap')) {
    return $attrs;
  }

  $options = wp_bootstrap_options();
  $previous_posts_link_class = $options['previous_posts_link_class'];

  if (strpos($attrs, "class=") !== FALSE) {
    $attrs = preg_replace('~class=["\']([^"\']*)~', '$1 ' . $previous_posts_link_class);
  } else {
    $attrs.= ' class="' . $previous_posts_link_class . '"';
  }

  return trim($attrs);
});

add_filter('paginate_links_output', function($links, $args = []) {
  if (!current_theme_supports('bootstrap')) {
    return $links;
  }

  if (!isset($args['type']) || $args['type'] !== 'list') {
    return $links;
  }
  
  $options = wp_bootstrap_options();

  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $links );
  $doc_xpath = new DOMXpath($doc);

  $lists = $doc_xpath->query("//ul");

  // 'pagination_class' => 'pagination pagination-sm',
  // 'page_item_class' => 'page-item',
  // 'page_item_active_class' => 'active',
  // 'page_link_class' => 'page-link',

  foreach ($lists as $list) {
    add_class($list, $options['pagination_class']);
  }

  $items = $doc_xpath->query("//li");

  foreach ($items as $item) {
    add_class($item, $options['page_item_class']);
  }

  $links = $doc_xpath->query("//li/*");

  foreach ($links as $link) {
    add_class($link, $options['page_link_class']);

    if (has_class($link, 'current')) {
      add_class($link->parentNode, $options['page_item_active_class']);
    }
  }

  $links = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $links;
}, 10, 2);


add_filter( "next_post_link", function($output) {
  if (!current_theme_supports('bootstrap')) {
    return $output;
  }

  extract(wp_bootstrap_options());

  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $output );

  $body = $doc->getElementsByTagName('body')->item(0);

  if (!$body) {
    return $output;
  }

  $root = $body->firstChild;

  if (!$root) {
    return $output;
  }

  add_class($root, $post_navigation_link_wrapper_class);

  $link = $doc->getElementsByTagName('a')->item(0);
  
  add_class($link, $post_navigation_link_class);

  $output = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $output;
} );

add_filter( "previous_post_link", function($output) {
  if (!current_theme_supports('bootstrap')) {
    return $output;
  }

  extract(wp_bootstrap_options());

  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $output );

  $body = $doc->getElementsByTagName('body')->item(0);

  if (!$body) {
    return $output;
  }

  $root = $body->firstChild;

  add_class($root, $post_navigation_link_wrapper_class);

  $link = $doc->getElementsByTagName('a')->item(0);
  add_class($link, $post_navigation_link_class);

  $output = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $output;
} );


add_filter('navigation_markup_template', function($template) {
  if (!current_theme_supports('bootstrap')) {
    return $template;
  }

  $options = wp_bootstrap_options();

  return '<nav class="navigation %1$s" aria-label="%4$s">
    <h2 class="screen-reader-text">%2$s</h2>
    <div class="nav-links ' . $options['pagination_class'] . '">
    %3$s
    </div>
  </nav>';
});