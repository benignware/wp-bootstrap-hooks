<?php

use function benignware\wp\bootstrap_hooks\add_class;

include __DIR__ . '/comments-walker.php' ;

/**
 * Comment Form Default Fields
 */
function wp_bootstrap_comment_form_default_fields( $fields ) {

  $options = wp_bootstrap_options();
  $field_class = $options['field_class'];
  $label_class = $options['label_class'];
  $text_input_class = $options['text_input_class'];
  $comment_label = $options['comment_label'];

  $commenter = wp_get_current_commenter();
  $req = get_option( 'require_name_email' );
  $aria_req = ( $req ? " aria-required='true'" : '' );
  $html5 = current_theme_supports( 'html5', 'comment-form' ) ? 1 : 0;

  $fields = array(
    'author' =>
      '<div class="' . $field_class . ' comment-form-author">' .
        '<label for="author" class="' . $label_class . '">' . __( 'Name' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
        '<input class="' . $text_input_class . '" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' required />' .
      '</div>',

    'email' =>
      '<div class="' . $field_class . ' comment-form-email">' .
        '<label for="email" class="' . $label_class . '">' . __( 'Email' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
        '<input class="' . $text_input_class . '" id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' required />' .
      '</div>',

    'url' =>
      '<div class="' . $field_class . ' comment-form-url">' .
        '<label for="url" class="' . $label_class . '">' . __( 'Website' ) . '</label> ' .
        '<input class="' . $text_input_class . '" id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" />' .
      '</div>',

  );
  return $fields;
}
add_filter( 'comment_form_default_fields', 'wp_bootstrap_comment_form_default_fields' );


/**
 * Comment Form Defaults
 */
function wp_bootstrap_comment_form_defaults( $args ) {
  if (!current_theme_supports('bootstrap')) {
    return $args;
  }

  extract(wp_bootstrap_options());

  $args['comment_field'] =
    '<div class="' . $field_class . ' comment-form-comment">
      <label for="comment" class="form-label">' . $comment_label . ' <span class="required">*</span></label>
      <textarea class="' . $text_input_class . '" id="comment" name="comment" cols="45" rows="8" aria-required="true" required></textarea>
    </div>';

  $args['comment_notes_after'] = $args['comment_notes_after'];

  $args['class_submit'] = $submit_class;
  
  return $args;
}
add_filter( 'comment_form_defaults', 'wp_bootstrap_comment_form_defaults' );

/**
 * List Comment Args
 */
add_action('wp_list_comments_args', function($args) {
  if (!current_theme_supports('bootstrap')) {
    return $args;
  }

  $args = array_merge($args, array(
    'style' => 'div',
    'max_depth' => 2,
    'format' => 'html5',
    'avatar_size' => 42
  ));

  if (empty($args['walker'])) {
    $args['walker'] = new Bootstrap_Walker_Comment();
  }
  return $args;
});


/**
 * Add bootstrap classes to the comment reply link
 */
add_filter('comment_reply_link', function($link, $args, $comment, $post) {
  if (!current_theme_supports('bootstrap')) {
    return $link;
  }

  $options = wp_bootstrap_options();
  $field_class = $options['field_class'];
  $reply_link_class = $options['reply_link_class'];

  return str_replace("class='comment-reply-link", "class='comment-reply-link $reply_link_class", $link);
}, 10, 4);

add_filter( 'get_avatar', function($avatar) {
  if (!current_theme_supports('bootstrap')) {
    return $avatar;
  }

  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $avatar );

  $img = $doc->getElementsByTagName('img')->item(0);

  if ($img) {
    add_class($img, 'rounded-circle');
  }

  $avatar = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $avatar;
}, 10, 2 );


add_action('comment_form_after', function() {
  echo <<<EOT
  <script>
  // Example starter JavaScript for disabling form submissions if there are invalid fields
  (() => {
    'use strict'
  
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    const forms = document.querySelectorAll('.comment-form')
  
    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        console.log('SUBMIT');
        event.preventDefault()
        event.stopPropagation()
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
  
        form.classList.add('was-validated')
      }, false)
    });

    
  })()
  </script>
EOT;
});
