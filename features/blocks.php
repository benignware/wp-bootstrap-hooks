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
use function benignware\bootstrap_hooks\util\colors\is_color;
use function benignware\bootstrap_hooks\util\colors\contrast_color;

use function benignware\bootstrap_hooks\util\object\query_object;

use function benignware\bootstrap_hooks\util\theme\get_palette_color;
use function benignware\bootstrap_hooks\util\theme\get_theme_css_var;
use function benignware\bootstrap_hooks\util\theme\parse_color_name;
use function benignware\bootstrap_hooks\util\theme\get_theme_json;


add_filter('render_block', function($content, $block)  {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  // $palette = get_theme_support('editor-color-palette');
	// $palette = $palette ? $palette[0] : null;

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
    // echo 'Button';
    // print_r($attrs);
    // echo '<br/>';
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

    $color = isset($attrs['textColor']) ? $attrs['textColor'] : '';
    $bg = isset($attrs['backgroundColor']) ? $attrs['backgroundColor'] : '';

    if (isset($attrs['style'])) {
      if (isset($attrs['style']['color'])) {
        if (!$color) {
          $color = isset($attrs['style']['color']['text']) ? $attrs['style']['color']['text'] : '';
        }

        if (!$bg) {
          $bg = isset($attrs['style']['color']['background']) ? $attrs['style']['color']['background'] : '';
        }
      }
    }

    $button_color = $is_outline ? $color : $bg;

    if (!$button_color) {
      $theme_json = get_theme_json();

      if ($is_outline) {
        $color = query_object($theme_json, 'styles.blocks.core/button.variations.outline.color.text');
        $bg = query_object($theme_json, 'styles.blocks.core/button.variations.outline.color.background');
      } else {
        $color = query_object($theme_json, 'styles.blocks.core/button.color.text');
        $bg = query_object($theme_json, 'styles.blocks.core/button.color.background');
      }

      $button_color = $is_outline ? $color : $bg;
    }

    $theme_color_def = get_palette_color($button_color);

    remove_class($button, 'has-link-color');
    remove_class($button, 'has-style-fill');
    remove_class($button, '~^wp-~');
    add_class($button, 'btn');

    if ($theme_color_def) {
      $theme_color = $theme_color_def['slug'];

      if ($is_outline) {
        remove_class($button, 'has-text-color');
        remove_class($button, "has-$theme_color-color");
      } else {
        remove_class($button, 'has-background');
        remove_class($button, 'has-background-color');
        remove_class($button, "has-$theme_color-background-color");
      }
      add_class($button, $is_outline ? 'btn-outline-' . $theme_color : 'btn-' . $theme_color);

    } else {
      if ($bg && is_color($bg)) {
        
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

      $color = $color ?: ($bg ? contrast_color($bg) : null);
      
      if ($color && is_color($color)) {
        add_style($button, '--bs-btn-color', $color);
        remove_style($button, 'color');

        $hover_color = shade($color, 0.9);
        $hover_color = $is_outline ? ($bg ?: 'initial') : shade($color, 0.9);

        add_style($button, '--bs-btn-hover-color', $hover_color);
        add_style($button, '--bs-btn-active-color', $color);
      }
    }

    if (isset($attrs['style'])) {
      if (isset($attrs['style']['typography'])) {
        if (isset($attrs['style']['typography']['fontSize'])) {
          $font_size = $attrs['style']['typography']['fontSize'];
          
          add_style($button, '--bs-btn-font-size', $font_size);
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
    $columns = isset($block['innerBlocks']) ? $block['innerBlocks'] : [];
    $column_count = count($columns);

    $row = $doc->createElement('div');

    // $classes = explode(' ', $options['columns_class']);
    // $class = implode(' ', $classes);

    $isStackedOnMobile = 1;
    $breakpoint = 'md';

    if (isset($attrs['isStackedOnMobile'])) {
      $isStackedOnMobile = $attrs['isStackedOnMobile'] ? 1 : 0;
    }

    $style = isset($column_attrs['style']) ? $attrs['style'] : [];
    $spacing = isset($column_attrs['spacing']) ? $attrs['spacing'] : [];
    $blockGap = isset($column_attrs['blockGap']) ? $attrs['blockGap'] : null;
    $blockGapValue = null;

    if ($blockGap !== null) {
      $blockGapValue = get_theme_css_var($blockGap);
      
      add_style($row, '--bs-gutter-y', $blockGapValue);
      add_style($row, '--bs-gutter-x', $blockGapValue);
    } else {
      add_class($row, 'g-4');
    }

    add_class($row, 'row');

    $i = 0;

    foreach ($container->childNodes as $child) {
      if ($child->nodeType === 1) {
        $column = isset($columns[$i]) ? $columns[$i] : null;
        $column_attrs = $column ? $column['attrs'] : [];

        $class = $child->getAttribute('class');

        if ($isStackedOnMobile) {
          remove_class($child, 'col');
          add_class($child, 'col-12');
        }

        $width = isset($column_attrs['width']) ? $column_attrs['width'] : '';

        if ($width) {
          [$value, $unit] = preg_split('/(?<=[0-9])(?=[a-z%])/', $width);

          if ($unit === '%') {
            $size = $width / 100 * 12;
            $grid_size = round($width / 100 * 12);

            if (abs($grid_size - $size)) {
              $class = sprintf($options['column_class'], $grid_size, $breakpoint);
              add_class($child, $class);
              remove_style($child, 'flex-basis');
            }
          } else {
            $abs_width = "calc($width + var(--bs-gutter-x))";

            add_style($child, 'flex-basis', $abs_width);
            add_class($child, 'flex-grow-0');
          }
        }

        if (!preg_match('/col-md/', $class)) {
          add_class($child, 'col-md');
        }

        
        $i++;
      }
    }

    if (isset($block['attrs']['verticalAlignment'])) {
      $classes[] = sprintf('align-items-%s', $block['attrs']['verticalAlignment']);
    }


    while ($container->hasChildNodes()) {
      $row->appendChild($container->firstChild);
    }

    $container->appendChild($row);
  }

  if ($name === 'core/column') {
    $size = '';
    $class = '';

    // if (isset($block['attrs']['width'])) {
    //   $width = $block['attrs']['width'];
    //   preg_match('~([\d]+(?:\.\d+)?)(%|[a-z]+)~', $block['attrs']['width'], $matches);
    //   $width_value = floatval($matches[1]);
    //   $unit = $matches[2];

    //   if ($unit === '%') {
    //     $size = $width_value / 100 * 12;
    //     $grid_size = round($width_value / 100 * 12);
    //     $delta = abs($grid_size - $size);
    //     echo abs($grid_size - $size);

    //     if ($delta < 0.2) {
    //       $class = sprintf($options['column_class'], $breakpoint, $grid_size);
    //       add_class($container, $class);
    //       $breakpoint = 'md'; // TODO: Make breakpoint configurable
    //       $class = sprintf($options['column_class'], $breakpoint, $grid_size);
    //       add_class($container, $class);
    //       remove_style($container, 'flex-basis');
    //     }
    //   }
    // }
  
    // remove_style($container, 'flex-basis');
    // remove_class($container, '~^wp-block~');
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

    $nav_content = find_by_class($container, $content_class);

    if ($nav_content) {
      $collapse_id = $nav_content->getAttribute('id');
      $close = find_by_class($nav_content, $close_class);

      if ($close) {
        $close->parentNode->removeChild($close);
      }

      add_class($nav_content, 'collapse navbar-collapse');
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

    remove_class($container, '~^wp-block-navigation~', true);
  }

  if ($name !== 'core/navigation' && has_class($container, 'navbar')) {
    $nested_navbars = find_all_by_class($container, 'navbar');
    $toggler = null;
    
    $collapse = find_by_class($container, 'navbar-collapse');

    if (!$collapse) {
      $collapse = $doc->createElement('div');
    }

    add_class($collapse, 'collapse navbar-collapse');
    remove_class($collapse, 'is-layout-flex');

    foreach ($nested_navbars as $index => $nested_navbar) {
      $nested_navbar_classes = array_values(array_filter(array_map('trim', explode(' ', $nested_navbar->getAttribute('class'))), function($class) {
        return strpos($class, 'navbar') === 0;
      }));

      add_class($container, $nested_navbar_classes);

      foreach ($nested_navbar_classes as $class) {
        remove_class($nested_navbar, $class, true);
      }

      $nested_toggler = find_by_class($nested_navbar, 'navbar-toggler');

      if ($nested_toggler) {
        if (!$toggler) {
          $toggler = $nested_toggler;
          $target = $toggler->getAttribute('data-bs-target');
          $collapse->setAttribute('id', preg_replace('/^#/', '', $target));

          $collapse->parentNode->insertBefore($toggler, $collapse);
        } else {
          $nested_toggler->parentNode->removeChild($nested_toggler);
        }
      }

      $nested_collapse = find_by_class($nested_navbar, 'navbar-collapse');

      if ($nested_collapse) {
        if ($nested_collapse->getAttribute('id') !== $collapse->getAttribute('id')) {
          $nested_collapse->removeAttribute('id');

          remove_class($nested_collapse, 'navbar-collapse', true);
          remove_class($nested_collapse, 'collapse', true);
        }
        

        if (!$collapse->parentNode) {
          $nested_navbar->parentNode->insertBefore($collapse, $nested_navbar);
        }
      }

      remove_class($nested_navbar, 'is-layout-flex');
      
      try {
        $collapse->appendchild($nested_navbar);
      } catch (Exception $e) {
        // ignore
      }
      
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
      return '';
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

    $submit->setAttribute('class', $submit->getAttribute('class') . ' ' . $options['submit_class']);
  }

  if ($name === 'core/query-pagination') {
    add_class($container, 'pagination gap-0');
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
  
  if ($name === 'core/comments') {
    $input = $doc_xpath->query('//input[@type="submit"]')->item(0);

    if ($input) {
      add_class($input, $options['submit_class']);
    }
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

  if ($name === 'core/post-title') {
    if (has_class($container, 'card-title')) {
      replace_tag($container, 'h5');
    }
  }

   if ($name === 'core/post-template') {
    $list = $container;

    remove_class($list, "~^wp-container-core-post-template~");
    remove_class($list, "~^wp-block-post-template-is-layout~");
    
    remove_class($list, 'is-layout-grid');
    
    remove_class($list, 'columns-3');
    add_class($list, 'row');
    remove_class($list, 'wp-block-post-template');


    $style = isset($attrs['style']) ? $attrs['style'] : [];
    $spacing = isset($style['spacing']) ? $style['spacing'] : [];
    $blockGap = isset($spacing['blockGap']) ? $spacing['blockGap'] : null;

    if ($blockGap !== null) {
      $blockGapValue = get_theme_css_var($blockGap);
      
      add_style($list, '--bs-gutter-y', $blockGapValue);
      add_style($list, '--bs-gutter-x', $blockGapValue);
    } else {
      add_class($list, 'g-4');
    }
 
    if ($list) {
      $list_items = $doc_xpath->query('./li', $list);

      foreach ($list_items as $list_item) {
        add_class($list_item, 'col-12 col-md-4');
        replace_tag($list_item, 'div');
        
        // $card = find_by_class($list_item, 'card');

        // if ($card) {
          // remove_class($card, 'is-layout-flex');

          // $list_item->parentNode->insertBefore($card, $list_item);
          // $list_item->parentNode->removeChild($list_item);
        // }
      }

      replace_tag($list, 'div');
      add_class($list, 'row');
    }
  }

  $cards = find_all_by_class($container, 'card');

  foreach ($cards as $card) {
    remove_class($card, 'is-layout-flex');
    remove_class($card, "~^wp-container-core-group-is-layout~");
    
    $card_bodies = find_all_by_class($container, 'card-body');

    foreach ($card_bodies as $card_body) {
      remove_class($card_body, 'is-layout-flex');
      remove_class($card_body, "~^wp-container-core-group-is-layout~");
    }
  }

  $result = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $result;
}, 11, 2);
