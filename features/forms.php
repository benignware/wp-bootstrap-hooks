<?php
/**
 * Get Search Form
 * TODO: Template
 */

add_filter( 'get_search_form', function( $output ) {
  if (!current_theme_supports('bootstrap')) {
    return $output;
  }

  $options = wp_bootstrap_options();

  extract($options);

  $action_url = home_url( '/' );
  $search_query = get_search_query();
  $search_for_label = __( 'Search for:' );
  $search_placeholder = esc_attr__( 'Search' ) . '...';
  $search_submit_label = isset($options['search_submit_label'])
    ? $search_submit_label
    : esc_attr_x( 'Search', 'submit button' );
  $search_submit_before = isset($input_group_append_class) ? sprintf('<div class="%s">', $input_group_append_class) : '';
  $search_submit_after = isset($input_group_append_class) ? '</div>' : '';
  $output = <<<EOT
<form
  role="search"
  method="get"
  id="searchform"
  class="searchform $search_form_class"
  action="$action_url"
>
  <label class="screen-reader-text" for="s">$search_for_label</label>
  <div class="$input_group_class">
    <input class="$text_input_class" type="text" value="$search_query" name="s" id="s" placeholder="$search_placeholder" />
    $search_submit_before
    <button
      class="$submit_button_class"
      type="submit"
      id="searchsubmit"
      title="$search_submit_label"
    >
      $search_submit_label
    </button>
    $search_submit_after
  </div>
</form>
EOT;

  return $output;
});

/**
 * Password Form
 * TODO: Template
 */

add_filter( 'the_password_form', function($output = '') {
  if (!current_theme_supports('bootstrap')) {
    return $output;
  }

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
