<?php
/**
 * Get Bootstrap Search Form Options
 */
function wp_bootstrap_get_pagination_options() {
  return apply_filters( 'bootstrap_pagination_options', array(
    'pagination_class' => 'pagination',
    'page_item_class' => 'page-item',
    'page_item_active_class' => 'active',
    'page_link_class' => 'page-link',
    'post_nav_class' => 'nav',
    'post_nav_tag' => 'ul',
    'post_nav_item_class' => 'nav-item',
    'post_nav_item_tag' => 'li',
    'post_nav_link_class' => 'nav-link'
  ));
}

/**
 * Posts Pagination
 */
function wp_bootstrap_posts_pagination( $args = array() ) {
  $navigation = '';
  
  extract(wp_bootstrap_get_pagination_options());
 
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
  
  extract(wp_bootstrap_get_pagination_options());
  extract($args);
  
  $output = "<$post_nav_tag class=\"navigation post-navigation $post_nav_class\" role=\"navigation\">";
  
  $prev_post = get_next_post();
  if ($prev_post) {
    $prev_post_link = get_permalink($prev_post);
    $output.= "<$post_nav_item_tag class=\"$post_nav_item_class nav-previous\">";
    $output.= "<a class=\"$post_nav_link_class\" href=\"$prev_post_link\" rel=\"prev\">";
    // Replace %title with post title
    // TODO: A more general, sprintf-like solution
    $output.= preg_replace("~%title~", $prev_post->post_title, $prev_text);
    $output.= "</a>";
    $output.= "</$post_nav_item_tag>";
  }
  
  $next_post = get_next_post();
  if ($next_post) {
    $next_post_link = get_permalink($next_post);
    $output.= "<$post_nav_item_tag class=\"$post_nav_item_class nav-next\">";
    $output.= "<a class=\"$post_nav_link_class\" href=\"$next_post_link\" rel=\"next\">";
    // Replace %title with post title
    // TODO: A more general, sprintf-like solution
    $output.= preg_replace("~%title~", $next_post->post_title, $next_text);
    $output.= "</a>";
    $output.= "</$post_nav_item_tag>";
  }
  
  
  $output.= "</$post_nav_tag>";
  echo $output;
}
