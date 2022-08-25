<?php

use function util\dom\add_class;
use function util\dom\remove_class;
use function util\dom\remove_style;
use function util\dom\has_class;
use function util\dom\find_by_class;
use function util\dom\find_all_by_class;
use function util\dom\replace_tag;
use function util\dom\get_common_ancestor;
use function util\dom\contains_node;

add_filter('render_block', function($content, $block)  {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  // list($palette) = has_theme_support('editor-color-palette')
  //   ? get_theme_support('editor-color-palette')
  //   : [[]];
  $palette = get_theme_support('editor-color-palette');
	$palette = $palette ? $palette[0] : null;

  if (!trim($content)) {
    return $content;
  }

  $name = $block['blockName'];
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

    if (!$background_color_name) {
      $background_color_name = 'primary';
    }
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
  $figures = $doc_xpath->query("//figure");

  foreach ($figures as $figure) {
    add_class($figure, $options['img_caption_class']);

    list($figcaption) = $doc_xpath->query("//figcaption", $figure);
    if ($figcaption) {
      add_class($figcaption, $options['img_caption_text_class']);
    }

    list($figimg) = $doc_xpath->query("//img", $figure);
    if ($figimg) {
      add_class($figimg, $options['img_caption_img_class']);
    }
  }

  // Inputs
  $inputs = $doc_xpath->query("//textarea|//select|//input[not(@type='checkbox') and not(@type='radio') and not(@type='submit')]");
  foreach ($inputs as $input) {
    add_class($input, $options['text_input_class']);
  }

  // Buttons
  $buttons = $doc_xpath->query("//form//button|//form//input[@type='submit']");

  foreach ($buttons as $button) {
    $class = sprintf($options['button_class'], 'primary');
    add_class($button, $class);
  }

  // Blocks
  if ($name === 'core/button') {
    list($button) = $doc_xpath->query("//a|//button");

    $class = sprintf(
      isset($attrs['className']) && in_array('is-style-outline', preg_split('/\s+/', $attrs['className']))
        ? $options['button_outline_class']
        : $options['button_class'],
      $background_color_name ?: 'primary'
    );

    add_class($button, $class);

    $button->setAttribute('role', 'button');

    if ($button->nodeName === 'a') {
      $button->setAttribute('href', $button->getAttribute('href') ?? '#');
    }
  }

  if ($name === 'core/buttons') {
  }

  if ($name === 'core/columns') {
    // remove_class($container, '~^wp-block~');
    add_class($container, $options['columns_class']);
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

  if ($name === 'core/column') {
    $width = $block['attrs']['width'];
    $cell = intval(floatval($width) / 100 * 12);
    $breakpoint = 'lg'; // TODO: Make configurable
    $class = sprintf($options['column_class'], $cell, $breakpoint);

    add_class($container, $class);
    remove_style($container, 'flex-basis');
    // remove_class($container, '~^wp-block~');
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