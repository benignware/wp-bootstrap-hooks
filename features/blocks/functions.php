<?php

namespace benignware\wp\bootstrap_hooks;

function get_block_nav_walker($doc, $menu = [], $attrs = []) {
  $menu = empty($menu) ? [
    'children' => []
  ] : $menu;
  $doc_xpath = new \DOMXpath($doc);

  $walker = function($parent, $level = 0, $current_menu = null) use ($doc_xpath, &$walker, $doc, $menu) {
    $current_menu = $current_menu ?? $menu;
    $item_description = find_by_class($parent, 'wp-block-navigation-item__description');

    if ($item_description) {
      $item_description->parentNode->removeChild($item_description);
    }

    if ($level === 0) {
      add_class($parent, 'nav');
      add_class($parent, 'navbar-nav');
    } else {
      add_class($parent, 'dropdown-menu');
    }

    $items = $doc_xpath->query("./li", $parent);

    if ($items->length === 0) {
      return;
    }

    foreach ($items as $index => $item) {
      $menu_item = $menu['children'][$index] ?? null;
      $menu_item_attrs = $menu_item['attrs'] ?? [];
      $menu_item_url = $menu_item_attrs['url'] ?? null;

      $is_active = has_class($item, 'current-menu-item');
      $is_active = $is_active || has_class($item, 'current-menu-parent');

      if ($level === 0) {
        add_class($item, 'nav-item');
      }

      $list = $doc_xpath->query("./ul", $item)->item(0);
      $link = $doc_xpath->query("./a|./button", $item)->item(0);

      if ($list) {
        add_class($item, 'dropdown');
        add_class($list, 'dropdown-menu');
        // add_class($list, 'dropdown-menu-end');
      }

      if ($link) {
        if ($menu_item_url) {
          $link->setAttribute('data-href', $menu_item_url);
        }

        if ($link->tagName === 'button') {
          $link = replace_tag($link, 'a');
        }

        if ($level === 0) {
          add_class($link, 'nav-link');
        } else {
          add_class($link, 'dropdown-item');
        }

       if ($is_active) {
          add_class($link, 'active');
        }
        
        if ($list) {
          add_class($link, 'dropdown-toggle');

          $link->setAttribute('tabindex', '0');
          $link->setAttribute('role', 'button');
          $link->setAttribute('aria-expanded', 'false');
          $link->setAttribute('data-bs-toggle', 'dropdown');
          $link->setAttribute('aria-expanded', 'false');
        }
      }

      if ($list) {
        remove_all_children($item);

        if ($link) {
          $item->appendChild($link);
          $item->appendChild($list);

          $spacer = $doc->createElement('span');
          $spacer->setAttribute('aria-hidden', 'true');
          $spacer->setAttribute('class', 'dropdown-menu dropdown-spacer');
          // $spacer->setAttribute('style', 'visibility: hidden');
          $item->appendChild($spacer);
        }
      }
      
      
      if ($list) {
        $walker($list, $level + 1, $menu_item);
      }
    }
    
    // exit;
    
    // foreach ($parent->childNodes as $item) {
      
    //   if ($item->nodeType !== 1) {
    //     continue;
    //   }
    
    //   $is_active = has_class($item, 'current-menu-item');

    //   // echo $level;
    //   // echo '<br/>';

    //   // if ($level === 0) {
    //   //   add_class($item, 'nav-item');
    //   // } else {
    //   //   add_class($item, 'dropdown-item');
    //   // }

    //   // echo '<textarea>';
    //   // echo $doc->saveHTML($item);
    //   // echo '</textarea>'; 

    //   // if ($item->nodeName === 'li') {
    //     $link = null;
    //     $list = null;
    //     $button = null;

    //     $children = iterator_to_array($item->childNodes);

    //     foreach ($children as $item_child) {
          
    //       if ($item_child->nodeName === 'a') {
    //         $link = $item_child;
    //       }

    //       if ($item_child->nodeName === 'ul') {
    //         $list = $item_child;
    //       }

    //       $class = $item_child->nodeType === 1 ? $item_child->getAttribute('class') : '';

    //       echo 'WALK NAV: ' . $item_child->nodeName . ' - ' . $class . ' - ' . $item_child->nodeType;
    //       echo '<br/>';
          
    //       if ($class && preg_match('~wp-block-navigation-submenu__toggle~', $class)) {
    //         $item_child->parentNode->removeChild($item_child);
    //       }
    //     }

    //     // if ($button) {
    //     //   $button->parentNode->removeChild($button);
    //     // }

    //     remove_class($item, 'open-on-hover-click');
    //     // remove_class($item, '~^wp-block~', true);
        
    //     if ($link) {
    //       if ($is_active) {
    //         add_class($link, 'active');
    //       }

    //       if ($level === 0) {
    //         add_class($link, 'nav-link');
    //       } else {
    //         add_class($link, 'dropdown-item');
    //       }

    //       if ($list) {
    //         add_class($list, 'dropdown-menu');
    //         add_class($link, 'dropdown-toggle');
    //         add_class($item, 'dropdown');

    //         $link->setAttribute('role', 'button');
    //         $link->setAttribute('data-bs-toggle', 'dropdown');
    //         $link->setAttribute('aria-expanded', 'false');
    //       }
    //     }

    //     if ($list) {
    //       $walker($item, $level + 1);
    //     }
    //   // }
    // }
  };

  return $walker;
}


function get_block_menu_by_id($menu_id) {
  global $wpdb;

  // Get the menu name from the ID
  $menu = $wpdb->get_row(
      $wpdb->prepare(
          "SELECT * FROM $wpdb->posts WHERE ID = %s AND post_type = 'wp_navigation' LIMIT 1",
          $menu_id
      )
  );

  if (!$menu) {
    return null; // No menu found with the given ID
  }
  // Cast the menu object to an array for easier manipulation
  
  $menu = (array) $menu;

  // echo 'MENU: ' . $menu['post_title'] . '<br/>';
  // echo 'MENU ID: ' . $menu_id . '<br/>';
  // echo 'MENU OBJECT: <br/>';
  // echo '<pre>';
  // print_r($menu);
  // echo '</pre>';

  $content = $menu['post_content'] ?? '';

  if (empty($content)) {
    // If post_content is empty, use the _wp_navigation_html meta key
    return $menu;
  }

  // echo '<textarea style="height: 400px; width: 400px;">' . $content . '</textarea><br/>';

  $blocks = parse_menu_blocks($content);

  $menu['children'] = $blocks;

  return $menu;
}

function parse_menu_blocks($block_content) {
    $blocks = parse_blocks($block_content);
    return process_blocks_recursively($blocks);
}

function process_blocks_recursively(array $blocks) {
    $result = [];

    foreach ($blocks as $block) {
        // Skip non-navigation blocks if needed
        if (!in_array($block['blockName'], ['core/navigation-link', 'core/navigation-submenu'])) {
            continue;
        }

        $item = [
            'blockName' => $block['blockName'],
            'attrs' => $block['attrs'] ?? [],
            'children' => [],
        ];

        if (!empty($block['innerBlocks'])) {
            $item['children'] = process_blocks_recursively($block['innerBlocks']);
        }

        $result[] = $item;
    }

    return $result;
}
