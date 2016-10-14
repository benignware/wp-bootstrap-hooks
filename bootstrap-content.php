<?php
/**
 * Get Bootstrap Content Options
 */
function wp_bootstrap_get_content_options() {
  return apply_filters( 'bootstrap_content_options', array(
    'image_class' => 'img-fluid img-responsive',
    'align_left_class' => 'pull-left',
    'align_right_class' => 'pull-right',
    'align_center_class' => 'center-block m-x-auto',
    'img_caption_tag' => 'figure',
    'img_caption_class' => 'figure',
    'img_caption_text_tag' => 'figcaption',
    'img_caption_text_class' => 'figure-caption',
    'img_caption_img_class' => 'figure-img',
    'table_class' => 'table',
    'blockquote_class' => 'blockquote',
    'blockquote_footer_tag' => 'footer',
    'blockquote_footer_class' => 'blockquote-footer',
    'edit_post_link_class' => 'btn btn-secondary',
    'edit_post_link_container_class' => 'form-group btn-group btn-group-sm'
  ));
}

/**
 * Add bootstrap classes to content images
 */
if(!function_exists('wp_bootstrap_the_content')) {
  
  function wp_bootstrap_the_content($content) {
    $options = wp_bootstrap_get_content_options();

    // Extract options
    extract($options);
    
    // Parse DOM
    $doc = new DOMDocument();
    @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $content );
    //$doc_xpath = new DOMXpath($doc);
    
    // Images
    $image_elements = $doc->getElementsByTagName( 'img' );
    foreach ($image_elements as $image_element) {
      // Adjust class
      $image_element_class = $image_element->getAttribute('class');
      $image_element_class = trim($image_element_class . " " . $image_class);
      if (preg_match('/\balignleft\b/i', $image_element_class)) {
        $image_element_class.= " $align_left_class";
      } else if (preg_match('/\balignright\b/i', $image_element_class)) {
        $image_element_class.= " $align_right_class";
      } else if (preg_match('/\baligncenter\b/i', $image_element_class)) {
        $image_element_class.= " $align_center_class";
      }
      $image_element->setAttribute('class', $image_element_class);
    }
    
    // Blockquotes
    $blockquote_elements = $doc->getElementsByTagName( 'blockquote' );
    foreach ($blockquote_elements as $blockquote_element) {
      // Add blockquote class
      $blockquote_element_class = $blockquote_element->getAttribute('class');
      $blockquote_element_class = trim($blockquote_element_class . " " . $blockquote_class);
      $blockquote_element->setAttribute('class', $blockquote_element_class);
      // Find next element sibing
      $sibling = $blockquote_element;
      $next_element_sibling = null;
      while ($sibling = $sibling->nextSibling) {
        if ($sibling->nodeType == 1) {
          $next_element_sibling = $sibling;
          break;
        }
      }
      if ($next_element_sibling) {
        // Find cite
        $cite_element = $doc->getElementsByTagName( 'cite' )->item(0);
        if ($cite_element) {
          // Create footer
          $blockquote_footer_element = $doc->createElement($blockquote_footer_tag);
          // Add blockquote-footer class
          $blockquote_footer_element->setAttribute('class', $blockquote_footer_class);
          // Copy children
          foreach ($next_element_sibling->childNodes as $child) {
            $blockquote_footer_element->appendChild($child->cloneNode(true));
          }
        }
        // Insert before original element
        $blockquote_element->appendChild($blockquote_footer_element);
        // Remove original element
        $next_element_sibling->parentNode->removeChild($next_element_sibling);
      }
    }
    
    // Tables
    $table_nodes = $doc->getElementsByTagName( 'table' );
    foreach ($table_nodes as $table_node) {
      $class = $table_node->getAttribute('class');
      $class.= ' table';
      $table_node->setAttribute('class', $class);
    }
    
    return preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());
  }
  add_filter( 'the_content', 'wp_bootstrap_the_content', 11 );
  
  
  /**
   * Img Caption
   * Reference: http://wordpress.stackexchange.com/questions/36772/image-captions-have-a-10px-extra-margin-and-its-not-css
   */
  function wp_bootstrap_img_caption( $empty_string, $attributes, $content ) {

    // Extract options
    extract(wp_bootstrap_get_content_options());
    
    // Extract shortcode attributes
    extract(shortcode_atts(array(
      'id' => '',
      'align' => 'alignnone',
      'width' => '',
      'caption' => ''
    ), $attributes));
    
    // Skip if caption text is empty
    if ( empty($caption) ) {
      return $content;
    }
    
    $id_attr = isset($id) ? '"id="' . esc_attr($id) . '"' : "";
    
    $style_string = "";
    if ($width) {
      $styles[] = "width: " . $width . "px";
    }
    $style_string = implode("; ", $styles);
    $style_attr = strlen($style_string) ? "style=\"$style_string\"" : "";
    
    $attr_string = " " . implode(" ", array($id_attr, $style_attr));

    // Add default classes
    $caption_element_class = "wp-caption " . esc_attr($align);
    
    // Add caption class
    $caption_element_class.= " $img_caption_class";
    
    // Add align class
    if (preg_match('/\balignleft\b/i', $caption_element_class)) {
      $caption_element_class.= " $align_left_class";
    } else if (preg_match('/\balignright\b/i', $caption_element_class)) {
      $caption_element_class.= " $align_right_class";
    } else if (preg_match('/\baligncenter\b/i', $caption_element_class)) {
      $caption_element_class.= " $align_center_class";
    }
    
    // Get content
    $content = do_shortcode( $content );
    
    return
    
<<<EOT
    <$img_caption_tag$attr_string class="$caption_element_class">
      $content
      <$img_caption_text_tag class="wp-caption-text $img_caption_text_class">$caption</$img_caption_text_tag>
    </$img_caption_tag>
EOT;
  }
  
  add_filter( 'img_caption_shortcode', 'wp_bootstrap_img_caption', 10, 3 );
  
  
  /**
   * Add responsive image class to thumbnail
   */
  function wp_bootstrap_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr) {
    // Extract options
    extract(wp_bootstrap_get_content_options());
    // Parse DOM
    $doc = new DOMDocument();
    @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html );
    // Handle image
    $image_elements = $doc->getElementsByTagName( 'img' );
    foreach ($image_elements as $image_element) {
      // Add configured image class
      $image_element_class = $image_element->getAttribute('class');
      $image_element_class = trim($image_element_class . " " . $image_class);
      $image_element->setAttribute('class', $image_element_class);
    }
    return preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());
  }
  
  add_filter( 'post_thumbnail_html', 'wp_bootstrap_post_thumbnail_html', 100, 5 );
  
  /**
   * Custom Styles
   */
  function wp_bootstrap_content_styles() {
    echo <<<EOT
    <style type="text/css">
      [class*='wp-image'] {
        max-width: 100%;
        height: auto;
      }
      .alignleft {
        margin-right: 1rem;
      }
      .alignright {
        margin-left: 1rem;
      }
      .figure {
        max-width: 100%;
      }
      .figure.aligncenter {
        display: block;
      }
    </style>
EOT;
  }
  add_action('wp_head', 'wp_bootstrap_content_styles', 100);
}

/**
 * Edit Post Link
 */
function wp_bootstrap_edit_post_link($link = null, $before = null, $after = null, $id = null, $class = "") {
  // Extract options
  extract(wp_bootstrap_get_content_options());
  
  // Capture edit post link html
  ob_start();
  edit_post_link($link, $before, $after, $id, $class);
  $html = ob_get_contents();
  ob_end_clean();
  
  // Parse DOM
  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html );
  
  $doc_xpath = new DOMXpath($doc);
  
  // Container Element
  //$body_element = $doc->getElementByTagName( 'body' );
  $container_element = $doc_xpath->query('body/*[1]')->item(0);
  
  if ($container_element) {
    $container_element_class = $container_element->getAttribute('class');
    $container_element_class.= " $edit_post_link_container_class";
    $container_element->setAttribute('class', trim($container_element_class));
  }
    
  
  // Links
  $link_elements = $doc->getElementsByTagName( 'a' );
  foreach ($link_elements as $link_element) {
    $link_element_class = $link_element->getAttribute('class');
    $link_element_class.= " $edit_post_link_class";
    $link_element->setAttribute('class', trim($link_element_class));
  }
  
  echo preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());
  
  return;
  
  
  
  //edit_post_link($link, $before, $after, $id, $class);
  //return;
  // Extract options
  extract(wp_bootstrap_get_content_options());
  // Set the default class
  if (!$class) {
    $class = 'edit-post-link';
  }
  
  // Container start
  if ($edit_post_link_container_tag) {
    echo "<$edit_post_link_container_tag class=\"edit-link $edit_post_link_container_class\">";
  }
  
  // Add class to link
  $class = $class . " " . $edit_post_link_class;
  
  // Render Link
  ob_start();
   
  
  // Container End
  if ($edit_post_link_container_tag) {
    echo "</$edit_post_link_container_tag>";
  }
}
?>