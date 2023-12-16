<?php

use function benignware\bootstrap_hooks\util\dom\add_class;
use function benignware\bootstrap_hooks\util\dom\add_style;
use function benignware\bootstrap_hooks\util\dom\remove_class;
use function benignware\bootstrap_hooks\util\dom\remove_style;
use function benignware\bootstrap_hooks\util\dom\has_class;
use function benignware\bootstrap_hooks\util\dom\find_by_class;
use function benignware\bootstrap_hooks\util\domm\find_all_by_class;
use function benignware\bootstrap_hooks\util\dom\replace_tag;
use function benignware\bootstrap_hooks\util\dom\get_common_ancestor;
use function benignware\bootstrap_hooks\util\dom\contains_node;

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

  $background_color_name = null;

  if (isset($attrs['backgroundColor'])) {
    if ($palette) {
      $background_color_name = array_values(array_map(
        function($item) {
          return $item['slug'];
        },
        array_filter($palette, function($item) use ($attrs) {
          return $item['color'] === $attrs['backgroundColor'];
        })
      ))[0];
      $background_color_name = isset($background_color_name) ? $background_color_name : null;
    }

    // TODO: Custom colors
    // if (!isset($background_color_name)) {
    //   $background_color_name = str_replace('#', 'hex-', $attrs['backgroundColor']);
    // }

    // if (!$background_color_name) {
    //   $background_color_name = 'primary';
    // }
  }

  $options = wp_bootstrap_options();

  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $content);
  $doc_xpath = new DOMXpath($doc);

  list($container) = $doc_xpath->query("//body/*[1]");

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

  // Figures
  // $figures = $doc_xpath->query("//figure");

  // foreach ($figures as $figure) {
  //   add_class($figure, $options['img_caption_class']);

    // list($figcaption) = $doc_xpath->query("//figcaption", $figure);
    // if ($figcaption) {
    //   add_class($figcaption, $options['img_caption_text_class']);
    // }

    // list($figimg) = $doc_xpath->query("//img", $figure);
    // if ($figimg) {
    //   add_class($figimg, $options['img_caption_img_class']);
    // }
  // }

  // Inputs
  $inputs = $doc_xpath->query("//textarea|//select|//input[not(@type='checkbox') and not(@type='radio') and not(@type='submit')]");
  foreach ($inputs as $input) {
    add_class($input, $options['text_input_class']);
  }

  // Buttons
  // $buttons = $doc_xpath->query("//form//button|//form//input[@type='submit']");

  // foreach ($buttons as $button) {
  //   $class = sprintf($options['button_class'], 'primary');
  //   add_class($button, $class);
  // }

  // Image
  if ($name === 'core/image') {
    if ($container->nodeName === 'figure') {
      add_class($container, $options['img_caption_class']);
      remove_class($container, '~^wp-block~', true);

      $caption = $doc_xpath->query("//figcaption", $container)->item(0);
      
      if ($caption) {
        add_class($caption, $options['img_caption_text_class']);
        remove_class($container, 'wp-element-caption', true);
      }

      $img = $doc_xpath->query("//img", $container)->item(0);

      if ($img) {
        add_class($img, $options['img_caption_img_class']);

        if (!$caption) {
          add_class($img, 'mb-0');
        }
      }

      if (isset($attrs['align'])) {
        if ($attrs['align'] === 'center') {
          add_class($container, 'mx-auto');
        }

        add_style($container, 'width', 'fit-content !important');
      }
    }
  }

  if ($name === 'core/buttons') {
    // print_r($block);
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

    $bg_name = isset($attrs['backgroundColor']) ? $attrs['backgroundColor'] : '';
    $is_outline = isset($attrs['className']) && in_array('is-style-outline', preg_split('/\s+/', $attrs['className']));
    
    $class = sprintf(
      $is_outline ? $options['button_outline_class'] : $options['button_class'],
      $bg_name ?: 'primary'
    );

    $color_name = isset($attrs['textColor']) ? $attrs['textColor'] : '';
    
    if ($color_name) {
      $class.= ' text-' . $color_name;
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

    // $class = sprintf(
    //   isset($attrs['className']) && in_array('is-style-outline', preg_split('/\s+/', $attrs['className']))
    //     ? $options['button_outline_class']
    //     : $options['button_class'],
    //     isset($attrs['backgroundColor']) ? $attrs['backgroundColor'] : 'primary'
    // );

    // echo $background_color_name;

    // add_class($button, $class);

    // add_style()

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
    remove_class($container, '~^wp-block~');
    remove_class($container, 'is-layout-flex');
    remove_class($container, 'is-not-stacked-on-mobile');
    remove_class($container, '~wp-container-core-columns-layout-\d~');

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

        if (!preg_match('/col-md/', $class)) {
          add_class($child, 'col-md');
        }
      }
    }

    // $classes[] = $isStackedOnMobile ? 'row-cols-1' : sprintf(
    //   'row-cols-%s',
    //   $columns
    // );

    // $classes[] = sprintf(
    //   'row-cols-%s-%s',
    //   $breakpoint, $columns
    // );

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
      // echo $width;
      preg_match('~([\d]+(?:\.\d+)?)(%|[a-z]+)~', $block['attrs']['width'], $matches);
      $value = floatval($matches[1]);
      $unit = $matches[2];

      // echo $value / (100 / 12) - floor($value / (100 / 12));

      if ($unit === '%') {
        if ($value / (100 / 12) - floor($value / (100 / 12)) < 1) {
          // echo 'DO IT';
          $size = round($width / 100 * 12);
          $breakpoint = 'md'; // TODO: Make breakpoint configurable
          $class = sprintf($options['column_class'], $breakpoint, $size);
          add_class($container, $class);
          remove_style($container, 'flex-basis');
        }
      }
      // $width = '11.34';
      // echo $value . '<br/>';
      // echo 'UNIT: ' . $unit;
      // echo '<br/>';
      // echo $value / (100 / 12);
      // echo '<br/>';
      // echo floor($value / (100 / 12));
      // echo '<br/>';
      // // $size = $width / 100 * 12;
      // // $size_rounded = round($width / 100 * 12);
      // echo $value % (100 / 12) . ' = ' . fmod($value, 100 / 12);
      // echo '<br/>';
      // echo '<br/>';
    }

    // $breakpoint = 'md'; // TODO: Make breakpoint configurable

    // if ($size) {
    //   $class = sprintf($options['column_class'], $breakpoint, $size);
    // } else {
    //   // $class = sprintf('col-12 col-%s', $breakpoint);
    // }

    
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
  if ($name === 'core/page-list') {
    // print_r($attrs);
    // remove_class($container, '~^wp-block~');
    add_class($container, $options['menu_class']);

    $items = find_all_by_class($container, 'wp-block-pages-list__item');

    foreach ($items as $item) {
      // remove_class($item, '~^wp-block~');
      add_class($item, $options['menu_item_class']);

      $link = find_by_class($item, 'wp-block-pages-list__item__link');

      if ($link) {
        // remove_class($link, '~^wp-block~');
        add_class($link, $options['menu_item_link_class']);
      }
    }
  }

  // if ($name === 'core/navigation') {
  //   echo 'NAVIGATION';
  //   print_r($block);
  // }

  // if ($name === 'core/group') {
  //   echo 'GROUP';
  //   print_r($block);
  // }

  // echo $name;
  // echo '<br/>';

  if ($name === 'core/separator') {
    remove_class($container, '~^wp-block~', true);
  }

  if ($name === 'core/search') {
    $form = $doc_xpath->query('//form')->item(0);
    $input = $doc_xpath->query('//input[@name="s"]')->item(0);
    $submit = $doc_xpath->query('//input[@type="submit"]|//button')->item(0);
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
    $nav = $container->cloneNode();
    $nav = replace_tag($nav, 'nav');

    $list = $doc->createElement('ul');
    $list->setAttribute('class', 'pagination');

    $nav->appendChild($list);

    $items = $doc_xpath->query('./li', $container);

    foreach ($items as $item) {
      $list->appendChild($item);
    }

    $container->parentNode->insertBefore($nav, $container);
    $container->parentNode->removeChild($container);
  }

  if ($name === 'core/query-pagination-numbers') {
    $links = find_all_by_class($container, 'page-numbers');

    foreach ($links as $link) {
      if ($link->nodeType === 1) {
        add_class($link, 'page-link');
        $item = $doc->createElement('li');
        $item->setAttribute('class', 'page-item');
        $item->appendChild($link->cloneNode(true));
        $container->parentNode->appendChild($item);
      }
    }

    $container->parentNode->removeChild($container);
  }

  if ($name === 'core/query-pagination-next' || $name === 'core/query-pagination-previous') {
    add_class($container, 'page-link');
    remove_class($container, 'has-small-font-size');

    $item = $doc->createElement('li');
    $item->setAttribute('class', 'page-item');
    $item->appendChild($container->cloneNode(true));
    remove_class($container, 'has-small-font-size');
  
    $container->parentNode->appendChild($item);
    $container->parentNode->removeChild($container);
  }

  $result = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  // return $name . ' - ' . var_dump($block) . ' - ' . $result;
  return $result;
}, 10, 2);


//Remove Gutenberg Block Library CSS from loading on the frontend
// function smartwp_remove_wp_block_library_css(){
//   wp_dequeue_style( 'wp-block-library' );
//   wp_dequeue_style( 'wp-block-library-theme' );
//   wp_dequeue_style( 'wc-blocks-style' ); // Remove WooCommerce block CSS
// } 
// add_action( 'wp_enqueue_scripts', 'smartwp_remove_wp_block_library_css', 100 );