<?php
/**
 * Posts Pagination
 */
function wp_bootstrap_posts_pagination( $args = array() ) {
  $navigation = '';

  extract(wp_bootstrap_options());

  // Don't print empty markup if there's only one page.
  if ( $GLOBALS['wp_query']->max_num_pages > 1 ) {
    $args = wp_parse_args( $args, array(
      'mid_size'           => 1,
      'prev_text'          => _x( 'Previous', 'previous post' ),
      'next_text'          => _x( 'Next', 'next post' ),
      'screen_reader_text' => __( 'Posts navigation' )
    ) );

        // Make sure we get a string back. Plain is the next best thing.
    if ( isset( $args['type'] ) && 'array' == $args['type'] ) {
        $args['type'] = 'plain';
    }

    // Set up paginated links.
    $links = paginate_links( $args );
    $document = new DOMDocument();
    @$document->loadHTML('<?xml encoding="utf-8" ?>' . "<ul class=\"$pagination_class\">" . $links . "</ul>");
    $page_links = $document->getElementsByTagName('ul')->item(0)->childNodes;
    foreach($page_links as $page_link) {
      if ($page_link->nodeType === 1) {
        $page_link->setAttribute('class', $page_link->getAttribute('class') . ' ' . $page_link_class);
        // Wrap in $page_item
        $page_item = $document->createElement('li');
        $page_item->setAttribute( 'class', $page_item_class . ($page_link->nodeName !== 'a' ? ' active' : '') );
        $page_link->parentNode->insertBefore($page_item, $page_link);
      }
    }
    $links = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $document->saveHTML());
    $navigation = _navigation_markup( $links, 'posts-navigation', $args['screen_reader_text'] );
  }

  echo $navigation;

  return $navigation;
}


function wp_bootstrap_post_navigation($args = array()) {

  $args = wp_parse_args( $args, array(
      'prev_text'          => '%title',
      'next_text'          => '%title',
      // 'in_same_term'       => false,
      // 'excluded_terms'     => '',
      // 'taxonomy'           => 'category',
      // 'screen_reader_text' => __( 'Post navigation' ),
  ) );

  // FIXME: Safe extract
  extract($args);
  extract(wp_bootstrap_options());

  $prev_post = get_previous_post();

  if ( $prev_post ) {
    // Replace %title with post title
    $prev_text = preg_replace("~%title~", $prev_post->post_title, $prev_text);
    $prev_text = apply_filters( 'bootstrap_prev_text', $prev_text, $prev_post);

    // Get Previous Post Link
    $prev_post_link = get_permalink($prev_post);
    $previous = "<$post_nav_item_tag class=\"$post_nav_item_class nav-previous\"><a class=\"$post_nav_link_class\" href=\"$prev_post_link\" rel=\"prev\">";
    $previous.= $prev_text;
    $previous.= "</a></$post_nav_item_tag>";
  }

  $next_post = get_next_post();
  if ( $next_post ) {
    // Replace %title with post title
    $next_text = preg_replace("~%title~", $next_post->post_title, $next_text);
    $next_text = apply_filters( 'bootstrap_next_text', $next_text, $next_post);
    // Get Next Post Link
    $next_post_link = get_permalink($next_post);
    $next = "<$post_nav_item_tag class=\"$post_nav_item_class nav-next\"><a class=\"$post_nav_link_class\" href=\"$next_post_link\" rel=\"next\">";
    $next.= $next_text;
    $next.= "</a></$post_nav_item_tag>";
  }

  // Only add markup if there's somewhere to navigate to.
  if ( $previous || $next ) {
    $output = "<$post_nav_tag class=\"navigation post-navigation $post_nav_class\" role=\"navigation\">";
    if ( $previous ) {
      $output.= $previous;
    }
    if ( $next ) {
      $output.= $next;
    }
    $output.= "</$post_nav_tag>";

    echo $output;
  }

}


// define the wp_link_pages callback 
function wp_bootstrap_link_pages( $output, $args ) {

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
};
add_filter( 'wp_link_pages', 'wp_bootstrap_link_pages', 10, 2 );


// Next posts link class
add_filter('next_posts_link_attributes', function($attrs = '') {
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
add_filter('previous_posts_link_attributes', function($attrs = array()) {
  $options = wp_bootstrap_options();
  $previous_posts_link_class = $options['previous_posts_link_class'];

  if (strpos($attrs, "class=") !== FALSE) {
    $attrs = preg_replace('~class=["\']([^"\']*)~', '$1 ' . $previous_posts_link_class);
  } else {
    $attrs.= ' class="' . $previous_posts_link_class . '"';
  }

  return trim($attrs);
});
