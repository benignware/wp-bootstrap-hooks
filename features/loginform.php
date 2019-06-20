<?php


/**
 * Password Form
* TODO: Template
 */

add_filter( 'the_password_form', function($output = '') {
  global $post;

  extract(wp_bootstrap_options());
  $post = get_post();
  $label = 'pwbox-' . ( empty($post->ID) ? rand() : $post->ID );
  $form = '<form class="protected-post-form" action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post">
  <div class="form-group">
    <p>' . __( 'This content is password protected. To view it please enter your password below:' ) . '</p>
    <div class="input-group">
    <input class="' . $text_input_class . '" name="post_password" id="' . $label . '" type="password"/>
    <span class="input-group-btn"><input type="submit" name="Submit" class="' . $submit_button_class . '" value="' . esc_attr_x( 'Enter', 'post password form' ) . '" /></span>
    </div>
  </div>
  </form>';
  return $form;
}, 10, 1 );

?>
