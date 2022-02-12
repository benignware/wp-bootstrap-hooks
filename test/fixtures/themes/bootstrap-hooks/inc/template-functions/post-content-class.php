<?php

require_once 'get-post-content-class.php';

if (!function_exists('post_content_class')) {
  function post_content_class( $class = '', $post_id = null ) {
    echo 'class="' . esc_attr( implode( ' ', get_post_content_class( $class, $post_id ) ) ) . '"';
  }
}
