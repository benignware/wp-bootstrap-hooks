<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_post_template($content, $block) {
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  if ($block['blockName'] !== 'core/post-comments') {
    return $content;
  }

  $options = wp_bootstrap_options();
  $attrs = $block['attrs'];
  $doc = parse_html($content);
  $container = root_element($doc);

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

  return serialize_html($doc);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_post_template', 10, 2);
