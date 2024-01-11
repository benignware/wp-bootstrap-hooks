<?php

use function benignware\bootstrap_hooks\util\dom\add_class;
use function benignware\bootstrap_hooks\util\dom\add_style;
use function benignware\bootstrap_hooks\util\dom\remove_class;
use function benignware\bootstrap_hooks\util\dom\remove_style;
use function benignware\bootstrap_hooks\util\dom\has_class;
use function benignware\bootstrap_hooks\util\dom\find_by_class;
use function benignware\bootstrap_hooks\util\dom\find_all_by_class;
use function benignware\bootstrap_hooks\util\dom\replace_tag;
use function benignware\bootstrap_hooks\util\dom\get_common_ancestor;
use function benignware\bootstrap_hooks\util\dom\contains_node;
use function benignware\bootstrap_hooks\util\dom\append_html;

use function benignware\bootstrap_hooks\util\colors\shade;

add_filter('render_block', function($content, $block)  {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  $palette = get_theme_support('editor-color-palette');
	$palette = $palette ? $palette[0] : null;

  if (!trim($content)) {
    return $content;
  }

  $name = $block['blockName'];

  if (!$name) {
    return $content;
  }

  if ($name === 'core/paragraph') {
    return $content;
  }

  $attrs = $block['attrs'];

  $options = wp_bootstrap_options();

  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $content);
  $doc_xpath = new DOMXpath($doc);

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

  list($container) = $doc_xpath->query("//body/*[1]");

  if (!$container) {
    return $content;
  }

  // Images
  $imgs = $doc_xpath->query("//img");

  foreach ($imgs as $img) {
    add_class($img, $options['img_class']);
  }

  // Blockquotes
  $blockquotes = $doc_xpath->query("//blockquote");

  foreach ($blockquotes as $blockquote) {
    add_class($blockquote, $options['blockquote_class']);

    list($cite) = $doc_xpath->query("//cite", $blockquote);

    if ($cite) {
      list($figure) = $doc_xpath->query("..//figure", $blockquote);

      if (!$figure) {
        $figure = $doc->createElement('figure');
        $blockquote->parentNode->insertBefore($figure, $blockquote->nextSibling);
        $figure->appendChild($blockquote);
      }

      $figcaption = $doc->createElement('figcaption');
      $figcaption->setAttribute('class', 'blockquote-footer');

      $blockquote->parentNode->insertBefore($figcaption, $blockquote->nextSibling);
      $figcaption->appendChild($cite);
    }
  }

  // Inputs
  $inputs = $doc_xpath->query("//textarea|//select|//input[not(@type='checkbox') and not(@type='radio') and not(@type='submit')]");
  foreach ($inputs as $input) {
    add_class($input, $options['text_input_class']);
  }

  // Image
  if ($name === 'core/image') {

    add_class($container, 'clearfix');
  
    $figure = $container->nodeName === 'figure' ? $container : $doc_xpath->query(".//figure", $container)->item(0);
    if ($figure) {

      add_class($figure, $options['img_caption_class']);
      
      // add_style($container, 'display', 'block');
      // remove_class($container, '~^wp-block~', true);

      $caption = $doc_xpath->query(".//figcaption", $figure)->item(0);
      
      if ($caption) {
        add_class($caption, $options['img_caption_text_class']);
        remove_class($container, 'wp-element-caption', true);
      }

      $img = $doc_xpath->query(".//img", $figure)->item(0);

      if ($img) {
        add_class($img, $options['img_caption_img_class']);

        if (!$caption) {
          add_class($img, 'mb-0');
        }
      }

      // if (isset($attrs['align'])) {
      //   if ($attrs['align'] === 'center') {
      //     add_class($container, 'mx-auto');
      //   } else if ($attrs['align'] === 'right') {
      //     add_class($container, 'ms-auto');
      //   } else {
      //     add_class($container, 'me-auto');
      //   }
      //   // add_style($container, 'width', 'fit-content !important');
      // }
    }
  }

  if ($name === 'core/buttons') {
    // add_class($container, 'my-4');
  }

  if ($name === 'core/button') {
    list($button) = $doc_xpath->query("//a|//button");

    if (isset($attrs['width'])) {
      add_class($container, sprintf('w-%s', $attrs['width']));
      add_class($button, 'd-block');
    }

    if (isset($attrs['fontSize'])) {
      $class_size = $attrs['fontSize'] === 'small'
        ? 'btn-sm'
        : (
          $attrs['fontSize'] === 'large'
            ? 'btn-lg'
            : ''
        );

      add_class($button, $class_size);
    }

    $is_outline = isset($attrs['className']) && in_array('is-style-outline', preg_split('/\s+/', $attrs['className']));

    $color_name = isset($attrs['textColor']) ? $attrs['textColor'] : '';
    $bg_name = isset($attrs['backgroundColor']) ? $attrs['backgroundColor'] : '';

    $theme_color = $is_outline ? $color_name : $bg_name;
    
    $class = sprintf(
      $is_outline ? $options['button_outline_class'] : $options['button_class'],
      $theme_color ?: 'primary'
    );
    
    if (!$color_name) {
      $class.= ' text-' . $color_name;
    }

    if ($theme_color) {
      if ($is_outline) {
        remove_class($button, 'has-text-color');
        remove_class($button, "has-$theme_color-color");
      } else {
        remove_class($button, 'has-background-color');
        remove_class($button, "has-$theme_color-background-color");
      }

      remove_class($button, 'has-link-color');
    }
    
    if (isset($attrs['style'])) {
      if (isset($attrs['style']['typography'])) {
        if (isset($attrs['style']['typography']['fontSize'])) {
          $font_size = $attrs['style']['typography']['fontSize'];
          
          add_style($button, '--bs-btn-font-size', $font_size);
        }
      }
      
      if (isset($attrs['style']['color'])) {
        $color = isset($attrs['style']['color']['text']) ? $attrs['style']['color']['text'] : '';
        $bg = isset($attrs['style']['color']['background']) ? $attrs['style']['color']['background'] : '';

        if ($color) {
          $color = $attrs['style']['color']['text'];

          add_style($button, '--bs-btn-color', $color);
          remove_style($button, 'color');

          $hover_color = shade($color, 0.9);
          $hover_color = $is_outline ? ($bg ?: 'initial') : shade($color, 0.9);

          add_style($button, '--bs-btn-hover-color', $hover_color);
          add_style($button, '--bs-btn-active-color', $color);
        }

        if ($bg) {
          add_style($button, '--bs-btn-bg', $bg);
          remove_style($button, 'background-color');

          add_style($button, '--bs-btn-border-color', $is_outline ? ($color ?: 'initial') : $bg);
          remove_style($button, 'border-color');

          $hover_bg = $is_outline ? ($color ?: 'initial') : shade($bg, 0.9);

          add_style($button, '--bs-btn-hover-bg', $hover_bg);
          add_style($button, '--bs-btn-hover-border-color', $hover_bg);

          add_style($button, '--bs-btn-active-bg', $bg);
          add_style($button, '--bs-btn-active-border-color', $bg);
        }
      }

      if (isset($attrs['style']['border'])) {
        if (isset($attrs['style']['border']['radius'])) {
          $radius = $attrs['style']['border']['radius'];
          
          add_style($button, '--bs-btn-border-radius', $radius);
          remove_style($button, 'border-radius');
        }
      }
    }

    add_class($button, $class);

    $button->setAttribute('role', 'button');

    if ($button->nodeName === 'a') {
      $button->setAttribute('href', $button->getAttribute('href') ?? '#');
    }

    remove_class($container, 'is-style-outline', true);
    remove_class($container, '~^wp-block~', true);
  }

  if ($name === 'core/buttons') {
  }

  if ($name === 'core/table') {
    add_class($container, $options['table_container_class']);
    remove_class($container, 'figure', true);

    $table = $container->getElementsByTagName('table')->item(0);

    add_class($table, $options['table_class']);

    if (isset($attrs['className']) && in_array('is-style-stripes', explode(' ', $attrs['className']))) {
      add_class($table, $options['table_striped_class']);
    };

    remove_class($container, '~^wp-block-table~', true);
  }

  if ($name === 'core/columns') {
    remove_class($container, 'is-layout-flex');
    remove_class($container, 'is-not-stacked-on-mobile');

    $columns = isset($block['innerBlocks']) ? count($block['innerBlocks']) : 1;
    $classes = explode(' ', $options['columns_class']);

    $isStackedOnMobile = 1;
    $breakpoint = 'md';

    if (isset($block['attrs']['isStackedOnMobile'])) {
      $isStackedOnMobile = $block['attrs']['isStackedOnMobile'] ? 1 : 0;
    }

    foreach ($container->childNodes as $child) {
      if ($child->nodeType === 1) {
        $class = $child->getAttribute('class');

        if ($isStackedOnMobile) {
          remove_class($child, 'col');
          add_class($child, 'col-12');
        }

        if (!preg_match('/col-/', $class)) {
          add_class($child, 'col-md');
        }
      }
    }

    if (isset($block['attrs']['verticalAlignment'])) {
      $classes[] = sprintf('align-items-%s', $block['attrs']['verticalAlignment']);
    }

    $class = implode(' ', $classes);

    add_class($container, $class);
  }

  if ($name === 'core/column') {
    $size = '';
    $class = '';

    if (isset($block['attrs']['width'])) {
      $width = $block['attrs']['width'];
      preg_match('~([\d]+(?:\.\d+)?)(%|[a-z]+)~', $block['attrs']['width'], $matches);
      $value = floatval($matches[1]);
      $unit = $matches[2];

      if ($unit === '%') {
        if ($value / (100 / 12) - floor($value / (100 / 12)) < 1) {
          $size = round($width / 100 * 12);
          $breakpoint = 'md'; // TODO: Make breakpoint configurable
          $class = sprintf($options['column_class'], $breakpoint, $size);
          add_class($container, $class);
          remove_style($container, 'flex-basis');
        }
      }
    }
  
    // remove_style($container, 'flex-basis');
    remove_class($container, '~^wp-block~');
  }

  if ($name === 'core/image') {
    // remove_class($container, '~^wp-block~');
  }

  if ($name === 'core/pullquote' || $name === 'core/quote') {
    // remove_class($container, '~^wp-block~');
  }

  // Block Navigation
  // if ($name === 'core/page-list') {
  //   // remove_class($container, '~^wp-block~');
  //   add_class($container, $options['menu_class']);

  //   $items = find_all_by_class($container, 'wp-block-pages-list__item');

  //   foreach ($items as $item) {
  //     // remove_class($item, '~^wp-block~');
  //     add_class($item, $options['menu_item_class']);

  //     $link = find_by_class($item, 'wp-block-pages-list__item__link');

  //     if ($link) {
  //       // remove_class($link, '~^wp-block~');
  //       add_class($link, $options['menu_item_link_class']);
  //     }
  //   }
  // }

  if ($name === 'core/navigation') {
    // remove_class($container, '~^wp-block~', true);
    add_class($container, 'navbar'); 

    $content_class = 'wp-block-navigation__responsive-container';
    $close_class = 'wp-block-navigation__responsive-container-close';

    $button = $doc_xpath->query("./button", $container)->item(0);

    $content = find_by_class($container, $content_class);

    if ($content) {
      $collapse_id = $content->getAttribute('id');
      $close = find_by_class($content, $close_class);

      if ($close) {
        $close->parentNode->removeChild($close);
      }

      add_class($content, 'collapse navbar-collapse');
      // remove_class($content, '~^wp-block~', true);
    }

    if ($button) {
      $new_button = $doc->createElement('button');
      $container->insertBefore($new_button, $container->firstChild);
      $button->parentNode->removeChild($button);

      $button = $new_button;

      add_class($button, 'navbar-toggler');

      $button->setAttribute('data-bs-toggle', 'collapse');
      $button->setAttribute('data-bs-target', '#' . $collapse_id);

      $button->textContent = '';

      append_html($button, '<span class="navbar-toggler-icon"></span>');

    }

    $overlayMenu = isset($attrs['overlayMenu']) ? $attrs['overlayMenu'] : 'mobile';

    if ($overlayMenu !== 'always') {
      add_class($container,
        $overlayMenu === 'never'
          ? 'navbar-expand'
          : 'navbar-expand-md'
      );
    }


    $list = $doc_xpath->query(".//ul", $container)->item(0);

    if ($list) {
      // remove_class($list, '~^wp-block-navigation~', true);
      add_class($list, 'nav');
      $walker($list);
    }

    $navs = find_all_by_class($container, 'nav');

    foreach ($navs as $nav) {
      add_class($nav, 'navbar-nav');
    }

    // remove_class($container, '~^wp-block-navigation~');

    remove_class($container, '~^wp-block-navigation~', true);
  }

  if (has_class($container, 'navbar')) {
    $nested_navbar = find_by_class($container, 'navbar');

    if ($nested_navbar) {
      $nested_navbar_classes = array_values(array_filter(array_map('trim', explode(' ', $nested_navbar->getAttribute('class'))), function($class) {
        return strpos($class, 'navbar') === 0;
      }));

      add_class($container, $nested_navbar_classes);

      $toggler = find_by_class($nested_navbar, 'navbar-toggler');

      if ($toggler) {
        $nested_navbar->parentNode->insertBefore($toggler, $nested_navbar);
      }

      // add_style($nested, 'display', 'contents');

      remove_class($container->childNodes, 'navbar', true);
      remove_class($container->childNodes, '~^navbar-expand~', true);
    }
  }

  // if (has_class($container, 'alignwide')) {
  //   add_class($container, 'container');
  // }

  // if (has_class($container, 'alignfull')) {
  //   add_class($container, 'container-fluid');
  // }

  if (has_class($container, 'navbar-brand')) {
    $links = $container->getElementsByTagName('a');

    foreach ($links as $link) {
      add_style($link, 'text-decoration', 'inherit');
    }
  }

  if ($name === 'core/navigation-submenu') {

  }

  // if ($name === 'core/navigation-link') {
  //   $item = $container->getElementsByTagName('li')->item(0);

  //   if ($item) {
  //     add_class($item, 'nav-item');
  //   }

  //   $link = $container->getElementsByTagName('a')->item(0);

  //   if ($link) {
  //     add_class($link, 'nav-link');
  //   }

  //   if ($link && $item && has_class($item, 'current-menu-item')) {
  //     add_class($link, 'active');
  //   }
  // }

  if ($name === 'core/page-list') {
    add_class($container, 'nav');
    $walker($container);
    // remove_class($container, '~^wp-block~', true);
  }

  if ($name === 'core/separator') {
    remove_class($container, '~^wp-block~', true);
  }

  if ($name === 'core/search') {
    $form = $doc_xpath->query('//form')->item(0);
    $input = $doc_xpath->query('//input[@name="s"]')->item(0);
    $submit = $doc_xpath->query('//input[@type="submit"]|//button')->item(0);

    if (!$form || !$input || !$submit) {
      return null;
    }

    $common_ancestor = get_common_ancestor($input, $submit);

    if ($common_ancestor === $form) {
      $wrapper = $doc->createElement('div');
      $children = [];
  
      foreach ($common_ancestor->childNodes as $child) {
        if (
          $child === $submit
          || contains_node($child, $submit)
          || $child === $input
          || contains_node($child, $input)
        ) {
          $children[] = $child;
        }
      }
  
      if (count($children) > 0) {
        $common_ancestor->insertBefore($wrapper, $children[0]);
  
        foreach ($children as $child) {
          $wrapper->appendChild($child);
        }
      }
    } else {
      $wrapper = $common_ancestor;
    }
  
    $wrapper->setAttribute('class', $options['input_group_class']);

    $submit->setAttribute('class', $submit->getAttribute('class') . ' ' . $options['submit_button_class']);
  }

  if ($name === 'core/query-pagination') {
    add_class($container, 'pagination');
    add_style($container, 'gap', '0px');
  }

  if ($name === 'core/query-pagination-numbers') {
    $links = find_all_by_class($container, 'page-numbers');

    foreach ($links as $link) {
      if ($link->nodeType === 1) {
        add_class($link, 'page-link');
        
        $item = $doc->createElement('div');
        $item->setAttribute('class', 'page-item');
        
        $link->parentNode->insertBefore($item);
        $item->appendChild($link);
        
        $container->parentNode->insertBefore($item->cloneNode(true), $container);
      }
    }

    $container->parentNode->removeChild($container);
  }

  if ($name === 'core/query-pagination-next' || $name === 'core/query-pagination-previous') {
    add_class($container, 'page-link');
    remove_class($container, 'has-small-font-size');

    $item = $doc->createElement('div');
    $item->setAttribute('class', 'page-item');
    $item->appendChild($container->cloneNode(true));
    remove_class($container, 'has-small-font-size');
  
    $container->parentNode->appendChild($item);
    $container->parentNode->removeChild($container);
  }

  if ($name === 'core/post-comments') {
    $list = $doc_xpath->query('.//ol|.//ul', $container)->item(0);

    if ($list) {
      $list_items = $doc_xpath->query('./li', $list);

      if (!count($list_items)) {
        replace_tag($list, 'div');
      }
    }
  }

  $result = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $result;
}, 10, 2);
