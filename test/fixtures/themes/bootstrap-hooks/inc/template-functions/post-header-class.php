<?php

require_once 'get-post-header-class.php';

if (!function_exists('post_header_class')) {
  function post_header_class( $class = '', $post_id = null ) {
    echo 'class="' . esc_attr( implode( ' ', get_post_header_class( $class, $post_id ) ) ) . '"';
  }
}
