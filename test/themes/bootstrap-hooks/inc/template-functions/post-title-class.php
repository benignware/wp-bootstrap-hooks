<?php

require_once 'get-post-title-class.php';

if (!function_exists('post_title_class')) {
  function post_title_class( $class = '', $post_id = null ) {
    echo 'class="' . esc_attr( implode( ' ', get_post_title_class( $class, $post_id ) ) ) . '"';
  }
}
