<?php

function get_post_content_class( $class = '', $post_id = null ) {
  $post = get_post( $post_id );

  if ( $class ) {
    if ( ! is_array( $class ) ) {
        $class = preg_split( '#\s+#', $class );
    }
    $classes = array_map( 'esc_attr', $class );
  } else {
    // Ensure that we always coerce class to being an array.
    $class = array();
  }

  if ( ! $post ) {
      return $classes;
  }

  $classes[] = 'entry-content';

  $classes = array_map( 'esc_attr', $classes );
  $classes = apply_filters( 'post_content_class', $classes, $class, $post->ID );

  return array_unique( $classes );
}