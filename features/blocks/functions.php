<?php

namespace benignware\wp\bootstrap_hooks;

function get_block_nav_walker($doc) {
  $doc_xpath = new \DOMXpath($doc);

  $walker = function($parent, $level = 0) use ($doc_xpath, &$walker) {
    foreach ($parent->childNodes as $item) {
      if ($level === 0) {
        add_class($item, 'nav-item');
      }

      $item_description = find_by_class($parent, 'wp-block-navigation-item__description');

      if ($item_description) {
        $item_description->parentNode->removeChild($item_description);
      }

      if ($item->nodeName === 'li') {
        $link = null;
        $list = null;
        $button = null;

        foreach ($item->childNodes as $item_child) {
          if ($item_child->nodeName === 'a') {
            $link = $item_child;
          }

          if ($item_child->nodeName === 'ul') {
            $list = $item_child;
          }

          if ($item_child->nodeName === 'button') {
            $button = $item_child;
          }
        }

        if ($button) {
          $button->parentNode->removeChild($button);
        }

        remove_class($item, 'open-on-hover-click');
        // remove_class($item, '~^wp-block~', true);
        
        if ($link) {
          if ($level === 0) {
            add_class($link, 'nav-link');
          } else {
            add_class($link, 'dropdown-item');
          }

          if ($list) {
            add_class($list, 'dropdown-menu');
            add_class($link, 'dropdown-toggle');
            add_class($item, 'dropdown');

            $link->setAttribute('role', 'button');
            $link->setAttribute('data-bs-toggle', 'dropdown');
            $link->setAttribute('aria-expanded', 'false');
          }
        }

        if ($list) {
          $walker($list, $level + 1);
        }
      }
    }
  };

  return $walker;
}
