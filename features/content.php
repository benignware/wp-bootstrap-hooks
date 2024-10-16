<?php

use function benignware\wp\bootstrap_hooks\add_class;
use function benignware\wp\bootstrap_hooks\remove_class;
use function benignware\wp\bootstrap_hooks\has_class;
use function benignware\wp\bootstrap_hooks\find_all_by_class;
use function benignware\wp\bootstrap_hooks\wrap;
use function benignware\wp\bootstrap_hooks\ratio;


/**
 * Add bootstrap classes to content images
 */
if (!function_exists('wp_bootstrap_the_content')) {

  function wp_bootstrap_the_content($content) {

    // return $content;
    $options = wp_bootstrap_options();

    // Extract options
    extract($options);

    // Parse DOM
    $doc = new DOMDocument();
    @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $content );
    $doc_xpath = new DOMXpath($doc);

    // Images
    $image_elements = $doc_xpath->query('//img|//video');

    foreach ($image_elements as $image_element) {
      $classes = explode(' ', $image_element->getAttribute('class'));
      $classes[]= $options['img_class'];
      $classes = array_values(array_unique($classes));

      $image_element->setAttribute('class', implode(' ', $classes));
    }

    // Embeds

    // Get ratio items and sort by their ratios
    $embed_preset_ratios_sorted = array();
    foreach ($embed_preset_ratios as $preset_ratio_string) {
      $preset_dimensions = explode(':', $preset_ratio_string);
      $preset_width = intval($preset_dimensions[0]);
      $preset_height = intval($preset_dimensions[1]);
      $preset_ratio = intval($preset_height / $preset_width * 100);
      $embed_preset_ratios_sorted[] = array(
        'string' => $preset_ratio_string,
        'width' => $preset_width,
        'height' => $preset_height,
        'ratio' => $preset_ratio,
        'orientation' => $preset_width < $preset_height ? 'portrait' : 'landscape'
      );
    }
    usort($embed_preset_ratios_sorted, function($a, $b) {
      return $a["ratio"] > $b["ratio"] ? 1 : ($a["ratio"] < $b["ratio"] ? -1 : 0);
    });

    $iframe_elements = $doc->getElementsByTagName( 'iframe' );
    foreach ($iframe_elements as $iframe_element) {
      // Adjust class
      add_class($iframe_element, $embed_class);

      // Setup container
      $iframe_parent = $iframe_element->parentNode;
      if (!has_class($iframe_element, $embed_container_class)) {

        // Resolve dimensions
        $width = $iframe_element->getAttribute('width');
        $height = $iframe_element->getAttribute('height');

        // Create wrapper
        $iframe_wrapper = wrap($iframe_element, 'div');

        // Set embed container class
        add_class($iframe_wrapper, $embed_container_class);

        $width = is_numeric($width) ? intval($width) : 525;
        $height = is_numeric($height) ? intval($height) : 295;
        $orientation = $width < $height ? 'portrait' : 'landscape';
        $ratio = intval($height / $width * 100);

        // Match against preset ratios
        $matched_preset_item = null;
        foreach($embed_preset_ratios_sorted as $embed_preset_item) {
          if ($orientation === $embed_preset_item['orientation'] && $ratio === $embed_preset_item['ratio']) {
            $matched_preset_item = $embed_preset_item;
            break;
          }
        }

        if ($matched_preset_item) {
          $matched_width = $matched_preset_item['width'];
          $matched_height = $matched_preset_item['height'];
        } else {
          $gcd_ratio = ratio($width, $height);
          $matched_width = $gcd_ratio[0];
          $matched_height = $gcd_ratio[1];
        }

        // Generate embed ratio class
        $embed_ratio_class = $embed_ratio_class_prefix
          . $matched_width
          . $embed_ratio_class_divider
          . $matched_height;

        if (!$matched_preset_item) {
          // For custom sizes, create an inline style element
          $style_element = $doc->createElement('style');
          $style_element->appendChild($doc->createTextNode(".$embed_ratio_class:before { padding: $ratio%; }"));
          $iframe_element->parentNode->insertBefore($style_element, $iframe_element);
        }

        // Apply embed ratio class
        add_class($iframe_wrapper, $embed_ratio_class);
      }
    }

    // Blockquotes
    $blockquote_elements = $doc->getElementsByTagName( 'blockquote' );
    foreach ($blockquote_elements as $blockquote_element) {
      // Add blockquote class
      $classes = preg_split('/\s+/', $blockquote_element->getAttribute('class'));
      $classes[] = $blockquote_class;
      $blockquote_element->setAttribute('class', implode(" ", $classes));
      // Find next element sibing
      $sibling = $blockquote_element;
      $next_element_sibling = null;
      $cite_element = null;
      while ($sibling = $sibling->nextSibling) {
        if ($sibling->nodeType == 1) {
          $next_element_sibling = $sibling;
          break;
        }
      }
      if ($next_element_sibling) {
        // Find cite
        // $cite_element = $next_element_sibling->getElementsByTagName( 'cite' )->item(0);
        // if ($cite_element) {
        //   // Create footer
        //   $blockquote_footer_element = $doc->createElement($blockquote_footer_tag);
        //   // Add blockquote-footer class
        //   $blockquote_footer_element->setAttribute('class', $blockquote_footer_class);
        //   // Copy children
        //   foreach ($next_element_sibling->childNodes as $child) {
        //     $blockquote_footer_element->appendChild($child->cloneNode(true));
        //   }
        //   // Insert before original element
        //   $blockquote_element->appendChild($blockquote_footer_element);
        //   // Remove original element
        //   $next_element_sibling->parentNode->removeChild($next_element_sibling);
        // }
      }
    }

    // Tables
    $table_elements = $doc->getElementsByTagName( 'table' );

    foreach ($table_elements as $table_element) {
      if (has_class($table_element, $options['table_class'])) {
        continue;
      }

      add_class($table_element, $options['table_class']);

      if ($table_container_tag && !has_class($table_element->parentNode, $table_container_class)) {
        $table_container_element = $doc->createElement($table_container_tag);
        $table_container_element->setAttribute("class", $table_container_class);
        $table_element->parentNode->insertBefore($table_container_element, $table_element);
        $table_container_element->appendChild($table_element);
      }
    }

    // Tags
    // $tag_elements = $doc_xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' tag ') or contains(concat(' ', normalize-space(@class), '-'), ' tag-')]");
    // foreach ($tag_elements as $tag_element) {
    //   $classes = preg_split('/\s+/', $tag_element->getAttribute('class'));
    //   $classes = wp_bootstrap_post_tag_class($classes);
    //   $tag_element->setAttribute('class', implode(" ", $classes));
    // }

    // Form inputs
    $input_elements = $doc_xpath->query("//select|//textarea|//input[not(@type='checkbox') and not(@type='radio') and not(@type='submit')]");
    foreach ($input_elements as $input_element) {
      $classes = preg_split('/\s+/', $input_element->getAttribute('class'));
      $classes[]= $text_input_class;
      $input_element->setAttribute('class', implode(" ", $classes));
    }

    // Labels
    $label_elements = $doc_xpath->query("//label");
    foreach ($label_elements as $label_element) {
      add_class($label_element, $options['label_class']);
    }

    // Handle label-wrapped inputs and checkboxes
    $forms = $doc_xpath->query("//form");

    foreach ($forms as $form) {
      $form_id = $form->getAttribute('id');
      $labels = iterator_to_array($doc_xpath->query(".//label", $form));

      foreach ($labels as $label) {
        $for = $label->getAttribute('for');
        $input = null;

        if ($for) {
          $input = $doc_xpath->query(sprintf('.//input[@id="%s"]', $for))->item(0);
        } else {
          $input = $doc_xpath->query(".//input[not(@type='submit' or @type='button' or @type='hidden')]", $label)->item(0);
          
          if ($input) {
            $input_type = $input->getAttribute('type');
            $input_name = $input->getAttribute('name');
            $input_id = $input->hasAttribute('id')
              ? $input->getAttribute('id')
              : (
                $form_id && $input->hasAttribute('name')
                  ? $form_id . '-' . $input->getAttribute('name')
                  : null
              );

            // Make sure to hide inputs if their wrapper was (honeypot)
            if ($label->getAttribute('style') && preg_match('~display:\s*none~', $label->getAttribute('style'))) {
              $style = $input->getAttribute('style') ?: '';
              $input->setAttribute('style', $style . '; display: none !important');
            }

            if ($input_id) {
              $input->setAttribute('id', $input_id);
              $label->setAttribute('for', $input_id);

              if ($label->nextSibling) {
                $label->parentNode->insertBefore($input, $label->nextSibling);
              } else {
                $label->parentNode->appendChild($input);
              }
            }
          }
        }

        if (!$input) {
          continue;
        }

        if (in_array($input->getAttribute('type'), ['checkbox', 'radio'])) {
          if (!has_class($input, $options['checkbox_input_class']) && !has_class($label->parentNode, $options['checkbox_container_class'])) {
            $input->setAttribute('class', $options['checkbox_input_class']);
            $label->setAttribute('class', $options['checkbox_label_class']);
            $wrapper = $doc->createElement('span');
            $wrapper->setAttribute('style', 'display: block');
            $wrapper->setAttribute('class', $options['checkbox_container_class']);
            $wrapper->appendChild($input);
            $label->parentNode->insertBefore($wrapper, $label);
            $wrapper->appendChild($label);
          }
        }
      }
    }

    // Buttons
    $buttons = $doc_xpath->query("//form//button|//form//input[@type='submit']|//*[contains(concat(' ', normalize-space(@class), ' '), ' button ')]");

    // $elements = $doc_xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), button)]");

    // $buttons = $doc_xpath->query("//button|//input[@type='submit']");
    foreach ($buttons as $button) {
      // TODO: Improve how to exclude things here
      // if (!has_class($button, '~^(?:carousel-control|btn-close)~')) {
      //   add_class($button, sprintf($options['button_class'], 'primary'));
      // }
      if (!has_class($button, '~^(?:carousel-control|btn-)~')) {
        add_class($button, sprintf($options['button_class'], 'primary'));
      }
    }

    // Alerts
    $alerts = find_all_by_class($doc->documentElement, 'mu_alert', 'alert');

    foreach($alerts as $alert) {
      if (!has_class($alert, 'alert')) {
        add_class($alert, 'alert-secondary');
      }
      preg_match('~\balert-(\w+)\b~', $alert->getAttribute('class'), $matches);
      $context = count($matches) ? $matches[1] : '';

      $alert_links = $doc_xpath->query('.//a', $alert);

      foreach($alert_links as $alert_link) {
        if (!has_class($alert_link, 'btn')) {
          add_class($alert_link, 'alert-link');
        }
      }

      if ($context) {
        $alert_buttons = find_all_by_class($alert, 'btn');

        foreach($alert_buttons as $alert_button) {
          remove_class($alert_button, '~^btn-~');
          add_class($alert_button, "btn-$context");
        }
      }
    }

    // Navs
    /*
    $nav_elements = $doc_xpath->query('//nav');

    foreach ($nav_elements as $nav_element) {
      $menu_element = $nav_element->tagName === 'UL' ? $nav_element : $doc_xpath->query('.//ul', $nav_element)->item(0);

      if (!$menu_element && !has_class($menu_element, $options['menu_class'])) {
        continue;
      }

      $list_item_elements = $doc_xpath->query('./li', $menu_element);

      if (count($list_item_elements)) {
        foreach ($list_item_elements as $list_item_element) {
          $is_active = has_class($list_item_element, 'is-active');
          $link_element = $doc_xpath->query('./*', $list_item_element)->item(0) ?: null;

          if ($link_element) {
            add_class($link_element, $options['menu_item_link_class']);

            if ($is_active) {
              add_class($link_element, $options['menu_item_link_active_class']);
            }
          }

          add_class($list_item_element, $options['menu_item_class']);
        }

        add_class($menu_element, $options['menu_class']);
        add_class($menu_element, 'flex-column nav-pills');
      }
    }
    */

    $output = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

    return $output;
  }
  add_filter( 'the_content', 'wp_bootstrap_the_content', 10000, 1 );


  
  add_filter('the_content', function($content) {
    // match any iframes
    $pattern = '~<iframe.*</iframe>|<embed.*</embed>~';
    preg_match_all($pattern, $content, $matches);

    foreach ($matches[0] as $match) {
      // wrap matched iframe with div
      $wrappedframe = '<div>' . $match . '</div>';

      //replace original iframe with new in content
      $content = str_replace($match, $wrappedframe, $content);
    }

    return $content;
  });

  /**
   * Img Caption
   * Reference: http://wordpress.stackexchange.com/questions/36772/image-captions-have-a-10px-extra-margin-and-its-not-css
   */
  function wp_bootstrap_img_caption( $empty_string, $attributes, $content ) {

    // Extract options
    extract(wp_bootstrap_options());

    // Extract shortcode attributes
    extract(shortcode_atts(array(
      'id' => '',
      'align' => 'alignnone',
      'width' => '',
      'caption' => '',
      'class' => $img_caption_img_class
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

    // Parse content for actual image
    $doc = new DOMDocument();
    @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $content );
    $doc_xpath = new DOMXpath($doc);

    // Images
    $image_elements = $doc->getElementsByTagName( 'img' );
    foreach ($image_elements as $image_element) {
      // Adjust class
      $image_element_classes = explode(' ', $image_element->getAttribute('class'));
      $image_element_classes[] = $img_caption_img_class;
      $image_element->setAttribute('class', implode(' ', $image_element_classes));
    }

    $content = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

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
    $options = wp_bootstrap_options();
    $img_class = $options['img_class'];

    // Parse DOM
    $doc = new DOMDocument();
    @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html );
    // Handle image
    $elements = $doc->getElementsByTagName( 'img' );

    foreach ($elements as $element) {
      $classes = preg_split('/\s+/', $element->getAttribute('class'));
      $classes[] = $options['img_class'];
      $element->setAttribute('class', implode(" ", array_unique($classes)));
    }
  
    return preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());
  }

  add_filter( 'post_thumbnail_html', 'wp_bootstrap_post_thumbnail_html', 10, 5 );

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
        margin-right: auto;
      }
      .alignright {
        margin-left: auto;
      }
      .figure {
        max-width: 100%;
      }
      .figure.aligncenter {
        display: block;
      }
      /*.list-group-item {
        margin-bottom: 0;
        border-bottom: 0;
      }*/

      /*
      .card-header + :not(.card-body) + .list-group .list-group-item:first-child {
        border-top: 0;
      }
      */
    </style>
EOT;
  }
  add_action('wp_head', 'wp_bootstrap_content_styles', 100);
}

/**
 * Edit Post Link
 */
add_filter('edit_post_link', function($link = '', $post_id = null, $text = '') {
  $options = wp_bootstrap_options();
  $edit_post_link_class = $options['edit_post_link_class'];
  $edit_post_link_container_class = $options['edit_post_link_container_class'];

	// Parse DOM
	$doc = new DOMDocument();
	@$doc->loadHTML('<?xml encoding="utf-8" ?><html><body>' . $link . '</body></html>');

  // Links
	$links = $doc->getElementsByTagName('a');

 	foreach($links as $element) {
		$classes = explode(' ', $element->getAttribute('class'));
		$classes[]= $edit_post_link_class;
		$classes = array_unique($classes);
 		$element->setAttribute('class', implode(' ', $classes));
	}

 	$link = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

 	return $link;
}, 3, 10);

// TODO: Get rid of it in favor of hook
function wp_bootstrap_edit_post_link($link = null, $before = null, $after = null, $id = null, $class = "") {
  // Extract options
  $options = wp_bootstrap_options();
  $edit_post_link_class = $options['edit_post_link_class'];
  $edit_post_link_container_class = $options['edit_post_link_container_class'];
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
}

// FIXME: Get rid of it
// function wp_bootstrap_post_tag_class( $classes ) {
//   $options = wp_bootstrap_options();
//   $post_tag_class = $options['post_tag_class'];
//   foreach ($classes as $index => $class) {
//     $class = preg_replace("~^tag\b~", "$post_tag_class", $class);
//     $classes[$index] = $class;
//   }
//   return $classes;
// }
