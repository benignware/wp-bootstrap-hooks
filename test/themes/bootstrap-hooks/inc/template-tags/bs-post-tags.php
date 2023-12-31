<?php

function bs_post_tags() {
  $tags = get_the_tags();

  if (empty($tags)) {
    return;
  }

  $before = '<div class="tags mb-3">';
  $tags_html = '';

  foreach ( $tags as $tag ) {
    $tags_html.= '<a href="'
      . esc_url(get_tag_link($tag->term_id))
      . '" title="'. esc_attr( $tag->name )
      . '"><span class="badge bg-secondary text-wrap">'
      . esc_html( $tag->name )
      . '</span></a> ';
  }

  $after = '</div>';

  echo $tags_html;
}
