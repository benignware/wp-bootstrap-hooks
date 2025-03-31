<?php

namespace benignware\wp\bootstrap_hooks;

function the_content_content($content) {
  // echo 'content';
  // echo '<textarea>';
  // echo $content;
  // echo '</textarea>';
  if (!current_theme_supports('bootstrap')) {
    return $content;
  }

  // Remove empty paragraphs
  $content = preg_replace('/<p><\/p>/', '', $content);

  return $content;
}

add_filter('the_content', 'benignware\wp\bootstrap_hooks\the_content_content', 1000000000);
