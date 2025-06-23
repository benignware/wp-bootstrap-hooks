<?php

namespace benignware\wp\bootstrap_hooks;

function render_block_custom($content, $block) {
  if (!current_theme_supports('bootstrap')
    || empty(trim($content))) {
    return $content;
  }

  if (strpos($block['blockName'], 'core/') !== 0) {
    return $content;
  }

  // echo $block['blockName'];
  // echo '<br/>';

  return the_content_forms($content);
}

add_filter('render_block', 'benignware\wp\bootstrap_hooks\render_block_custom', 100, 2);