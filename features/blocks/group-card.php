<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_group_card($content, $block) {
  global $post;

  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/group') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $xpath = new \DOMXPath($doc);
  $container = root_element($doc);

  if (!has_class($container, 'card')) {
    return $content;
  }

  $card = $container;
  
  $card_bodies = find_all_by_class($container, 'card-body');
  $stretched_link = find_by_class($container, 'stretched-link');

  if (!$stretched_link) {
    $links = iterator_to_array($xpath->query('.//a[@href]', $container));
    $links = array_values(array_filter($links, function ($link) {
      $href = $link->getAttribute('href');

      return $href && $href !== '#';
    }));

    // if only one link is found, use it as the stretched link
    if (count($links) === 1) {
      $stretched_link = $links[0];
    } else {
      // Otherwise, priotize links whether the are the main link,
      // e.g. if its class contains read more, or if the href is the current post permalink
      foreach ($links as $link) {
        $href = $link->getAttribute('href');
        $is_current_post = $href === get_permalink($post);
        $has_read_more = strpos($link->getAttribute('class'), 'read-more') !== false;
        
        if ($has_read_more || $is_current_post) {
          $stretched_link = $link;
          break;
        }
      }
    }
  }

  if ($stretched_link) {
    // echo 'stretched link<br/>';
    // echo $stretched_link->getAttribute('href');
    // echo '<br/>';
    // echo $stretched_link->getAttribute('class');
    // print_r($stretched_link);
    // exit;
    add_class($stretched_link, 'stretched-link');

    // If a stretched-link was found, For all other links, we need to apply position-relative and z-3 classes to make them work above the stretched link
    foreach ($links as $link) {
      if ($link !== $stretched_link) {
        add_class($link, 'z-3');
      }
    }
  }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_group_card', 10, 2);
