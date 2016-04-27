<?php

if(!function_exists('wp_bootstrap_the_content')) {
    
  function wp_bootstrap_the_content($content) {
    $html = new DOMDocument();
    @$html->loadHTML('<?xml encoding="utf-8" ?>' . $content );
    $image_nodes = $html->getElementsByTagName( 'img' );
    
    foreach ($image_nodes as $image_node) {
      $class = $image_node->getAttribute('class');
      if (strpos($class, 'alignleft') !== false) {
        $class.= " pull-left";
      }
      if (strpos($class, 'alignright') !== false) {
        $class.= " pull-right";
      }
      if (strpos($class, 'aligncenter') !== false) {
        $class.= " center-block";
      }
      $image_node->setAttribute('class', $class);
    }
    
    return preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $html->saveHTML());
    
  }
  
}



?>