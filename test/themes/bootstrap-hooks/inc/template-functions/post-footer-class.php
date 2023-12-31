<?php

require_once 'get-post-footer-class.php';

if (!function_exists('post_footer_class')) {
  function post_footer_class( $class = '', $post_id = null ) {
    echo 'class="' . esc_attr( implode( ' ', get_post_footer_class( $class, $post_id ) ) ) . '"';
  }
}
