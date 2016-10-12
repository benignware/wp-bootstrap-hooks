<?php
/**
 * Get Bootstrap Content Options
 */
function wp_bootstrap_get_content_options() {
  return apply_filters( 'bootstrap_content_options', array(
    'image_align_left_class' => 'pull-left',
    'image_align_right_class' => 'pull-right',
    'image_align_center_class' => 'center-block'
  ));
}

/**
 * Add bootstrap classes to content images
 */
if(!function_exists('wp_bootstrap_the_content')) {
  function wp_bootstrap_the_content($content) {
    $options = wp_bootstrap_get_content_options();
    $image_align_left_class = $options['image_align_left_class'];
    $image_align_right_class = $options['image_align_right_class'];
    $image_align_center_class = $options['image_align_center_class'];
    $html = new DOMDocument();
    @$html->loadHTML('<?xml encoding="utf-8" ?>' . $content );
    $image_nodes = $html->getElementsByTagName( 'img' );
    foreach ($image_nodes as $image_node) {
      $class = $image_node->getAttribute('class');
      if (strpos($class, 'alignleft') !== false) {
        $class.= " $image_align_left_class";
      }
      if (strpos($class, 'alignright') !== false) {
        $class.= " $image_align_right_class";
      }
      if (strpos($class, 'aligncenter') !== false) {
        $class.= " $image_align_center_class";
      }
      $image_node->setAttribute('class', $class);
    }
    return preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $html->saveHTML());
  }
  add_filter( 'the_content', 'wp_bootstrap_the_content', 11 );  
}
?>