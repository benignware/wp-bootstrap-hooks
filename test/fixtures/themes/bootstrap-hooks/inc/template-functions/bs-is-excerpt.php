<?php

function bs_is_excerpt() {
  return (is_search() || ! is_singular() && 'summary' === get_theme_mod( 'blog_content', 'full' ));
}
