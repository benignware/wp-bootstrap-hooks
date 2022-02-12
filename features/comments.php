<?php

// http://www.codecheese.com/2013/11/wordpress-comment-form-with-twitter-bootstrap-3-supports/
// http://bassjobsen.weblogs.fm/wordpress-theming-comment_form-call-power-less/

/**
 * Comment Form Default Fields
 */
function wp_bootstrap_comment_form_default_fields( $fields ) {

  $options = wp_bootstrap_options();
  $field_class = $options['field_class'];
  $text_input_class = $options['text_input_class'];
  $comment_label = $options['comment_label'];

  $commenter = wp_get_current_commenter();
  $req = get_option( 'require_name_email' );
  $aria_req = ( $req ? " aria-required='true'" : '' );
  $html5 = current_theme_supports( 'html5', 'comment-form' ) ? 1 : 0;

  $fields = array(
    'author' =>
      '<div class="' . $field_class . ' comment-form-author">' .
        '<label for="author">' . __( 'Name' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
        '<input class="' . $text_input_class . '" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' />' .
      '</div>',

    'email' =>
      '<div class="' . $field_class . ' comment-form-email">' .
        '<label for="email">' . __( 'Email' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
        '<input class="' . $text_input_class . '" id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' />' .
      '</div>',

    'url' =>
      '<div class="' . $field_class . ' comment-form-url">' .
        '<label for="url">' . __( 'Website' ) . '</label> ' .
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

  extract(wp_bootstrap_options());

  $args['comment_field'] =
    '<div class="' . $field_class . ' comment-form-comment">
      <label for="comment">' . $comment_label . '</label>
      <textarea class="' . $text_input_class . '" id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>
    </div>';

  $args['comment_notes_after'] = $args['comment_notes_after'];
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
    'style' => 'div'
  ));

  if (empty($args['walker'])) {
    $args['walker'] = new wp_bootstrap_commentwalker();
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

  return $link = '<div class="' . $field_class . '">' . str_replace("class='comment-reply-", "class='comment-reply-link $reply_link_class", $link) . '</div>';
}, 10, 4);


/**
 * Comment Walker Class
 */
class wp_bootstrap_commentwalker extends Walker_Comment {
    // init classwide variables
    var $tree_type = 'comment';
    var $db_fields = array( 'parent' => 'comment_parent', 'id' => 'comment_ID' );

    /** CONSTRUCTOR
     * You'll have to use this if you plan to get to the top of the comments list, as
     * start_lvl() only goes as high as 1 deep nested comments */
    function __construct() { ?>
         <style>ol.comment-list {padding-left: 0;}</style>
         <ul class="media-list">
    <?php }

    /** START_LVL
     * Starts the list before the CHILD elements are added. */
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $GLOBALS['comment_depth'] = $depth + 1; ?>

                <ul class="media-list">
    <?php }

    /** END_LVL
     * Ends the children list of after the elements are added. */
    function end_lvl( &$output, $depth = 0, $args = array() ) {
        $GLOBALS['comment_depth'] = $depth + 1; ?>

        </ul><!-- /.children -->

    <?php }

    /** START_EL */
    function start_el( &$output, $comment, $depth = 0, $args = array(), $id = 0 ) {
        $depth++;
        $GLOBALS['comment_depth'] = $depth;
        $GLOBALS['comment'] = $comment;
        $parent_class = ( empty( $args['has_children'] ) ? '' : 'parent' ); ?>

        <li <?php comment_class( $parent_class . " media" ); ?> id="comment-<?php comment_ID() ?>">

            <div class="media-left">
                <a>
                  <?php echo ( $args['avatar_size'] != 0 ? str_replace("class='avatar", "class='media-object ", get_avatar( $comment, $args['avatar_size'] ) ) : '' ); ?>
                </a>
            </div><!-- /.comment-author -->

            <div id="comment-body-<?php comment_ID() ?>" class="comment-body media-body">

                <div class="comment-author vcard author media-left">
                    <h3 class="media-heading fn n author-name"><?php echo get_comment_author_link(); ?>
                    </h3>
                    <small class="comment-meta comment-meta-data">
                      <a href="<?php echo htmlspecialchars( get_comment_link( get_comment_ID() ) ) ?>"><?php comment_date(); ?> at <?php comment_time(); ?></a> <?php edit_comment_link( '(Edit)' ); ?>
                    </small><!-- /.comment-meta -->
                </div><!-- /.comment-author -->

                <div id="comment-content-<?php comment_ID(); ?>" class="comment-content">
                    <?php if( !$comment->comment_approved ) : ?>
                    <p><em class="comment-awaiting-moderation">Your comment is awaiting moderation.</em></p>

                    <?php else: comment_text(); ?>
                    <?php endif; ?>
                </div><!-- /.comment-content -->



                <div class="reply">
                    <?php $reply_args = array(
                        //'add_below' => $add_below,
                        'depth' => $depth,
                        'max_depth' => $args['max_depth'] );

                    comment_reply_link( array_merge( $args, $reply_args ) );  ?>
                </div><!-- /.reply -->
            <!--</div> /.comment-body -->

    <?php }

    function end_el(&$output, $comment, $depth = 0, $args = array() ) { ?>
          </div><!-- /.comment-body -->
        </li><!-- /#comment-' . get_comment_ID() . ' -->

    <?php }

    /** DESTRUCTOR
     * I'm just using this since we needed to use the constructor to reach the top
     * of the comments list, just seems to balance out nicely:) */
    function __destruct() { ?>

    </ul><!-- /#comment-list -->

    <?php }

}
