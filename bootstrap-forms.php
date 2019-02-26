<?php
/**
 * Get Search Form
 * TODO: Template
 */
function wp_bootstrap_get_search_form( $form ) {
  extract(wp_bootstrap_options());

  $form = '<form role="search" method="get" id="searchform" class="searchform ' . $search_form_class . '" action="' . home_url( '/' ) . '" >
  <label class="screen-reader-text" for="s">' . __( 'Search for:' ) . '</label>
  <div class="' . $input_group_class . '">
    <input class="' . $text_input_class . '" type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="'. esc_attr__( 'Search' ) .'..."/>
    <span class="' . $input_group_append_class . '">
      <button class="' . $submit_button_class . '" type="submit" id="searchsubmit" title="' . esc_attr_x( 'Search', 'submit button' ) . '">' . $search_submit_label . '</button>
    </span>
  </div>
</form>';
  return $form;
}
add_filter( 'get_search_form', 'wp_bootstrap_get_search_form' );

/**
 * Password Form
* TODO: Template
 */
function wp_bootstrap_password_form($output = '') {
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
}
add_filter( 'the_password_form', 'wp_bootstrap_password_form', 10, 1 );

?>
