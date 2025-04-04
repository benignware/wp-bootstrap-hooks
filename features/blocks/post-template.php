<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_post_template($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/post-template') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];

  $column_count = get_option('posts_grid_columns', $attrs['layout']['columnCount'] ?? 4);

  $doc = parse_html($content);
  $doc_xpath = new \DOMXPath($doc);
  $container = find_by_class($doc, '~^wp-block-post-template~');

  if (!$container) {
    return $content;
  }

  remove_class($container, "~^wp-container-core-post-template~");
  remove_class($container, "~^wp-block-post-template-is-layout~");

  remove_class($container, '~^columns-~');
  add_class($container, 'row');

  remove_class($container, 'wp-block-post-template');
  remove_class($container, '~^is-layout-~');
  add_class($container, 'gap-0');

  $style = isset($attrs['style']) ? $attrs['style'] : [];
  $spacing = isset($style['spacing']) ? $style['spacing'] : [];
  $blockGap = isset($spacing['blockGap']) ? $spacing['blockGap'] : null;

  if ($blockGap !== null) {
    $blockGapValue = get_theme_css_var($blockGap);
    
    add_style($container, '--bs-gutter-y', $blockGapValue);
    add_style($container, '--bs-gutter-x', $blockGapValue);
    
  } else {
    add_class($list, 'g-4');
  }

  $list = $this->query('./ul', $container)->item(0) ?: $container;

  if ($list) {
    $list_items = iterator_to_array($doc_xpath->query('./li', $list));

    foreach ($list_items as $list_item) {
      if (has_class($list_item, '~col-~')) {
        continue;
      }

      $column = $doc->createElement('div');

      add_class($column, sprintf('col-12 col-sm-6 col-md-3 col-lg-%s', 12 / $column_count));
      $list_item = replace_tag($list_item, 'div');
      $list_item->parentNode->insertBefore($column, $list_item);
      $column->appendChild($list_item);
    }

    $list = replace_tag($list, 'div');
    
    add_class($list, 'row d-flex');
    remove_class($list, '~^is-layout-~');
    remove_class($list, 'is-layout-grid');
    add_clasS($list, 'd-flex');
  }

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_post_template', 10, 2);
