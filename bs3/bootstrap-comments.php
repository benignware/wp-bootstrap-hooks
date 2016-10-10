<?php

// http://www.codecheese.com/2013/11/wordpress-comment-form-with-twitter-bootstrap-3-supports/
// http://bassjobsen.weblogs.fm/wordpress-theming-comment_form-call-power-less/


/**
 * Comment Form Default Fields
 */
function wp_bootstrap_comment_form_default_fields( $fields ) {
    
  $commenter = wp_get_current_commenter();
  $req = get_option( 'require_name_email' );
  $aria_req = ( $req ? " aria-required='true'" : '' );
  $html5 = current_theme_supports( 'html5', 'comment-form' ) ? 1 : 0;
  
  $fields = array(
    'author' => 
      '<div class="form-group comment-form-author">' .
        '<label for="author">' . __( 'Name' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
        '<div class="input-group">' .  
          '<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>' . 
          '<input class="form-control" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' />' .
        '</div>' .
      '</div>',
        
    'email' => 
      '<div class="form-group comment-form-email">' . 
        '<label for="email">' . __( 'Email' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
        '<div class="input-group">' .  
          '<span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>' . 
          '<input class="form-control" id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' />' .
        '</div>' . 
      '</div>',
       
    'url' => 
      '<div class="form-group comment-form-url">' .
        '<label for="url">' . __( 'Website' ) . '</label> ' .
        '<div class="input-group">' .
          '<span class="input-group-addon"><i class="glyphicon glyphicon-link"></i></span>' . 
          '<input class="form-control" id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" />' .
         '</div>' .
      '</div>',
      
  );
  return $fields;
}
add_filter( 'comment_form_default_fields', 'wp_bootstrap_comment_form_default_fields' );


/**
 * Comment Form Defaults
 */
function wp_bootstrap_comment_form_defaults( $args ) {
  $args['comment_field'] = 
    '<div class="form-group comment-form-comment">
      <label for="comment">' . _x( 'Comment', 'noun' ) . '</label>
      <textarea class="form-control" id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>
    </div>';
    
  $args['comment_notes_after'] = $args['comment_notes_after'];
  return $args;
}
add_filter( 'comment_form_defaults', 'wp_bootstrap_comment_form_defaults' );


/**
 * Comment Form After
 */
function wp_bootstrap_comment_form_after() {
  echo 
    "<script>\n" .
    "  (function($) {\n" . 
    "    $('#commentform input#submit').addClass('btn btn-primary');\n" . 
    "  })(jQuery)\n" . 
    "</script>\n";
}
add_action( 'comment_form_after', 'wp_bootstrap_comment_form_after' );


/**
 * List Comments Args
 */
function wp_bootstrap_list_comments_args($args) {
  $args = array_merge($args, array(
    'style' => 'div'
  ));
  if (empty($args['walker'])) {
    $args['walker'] = new wp_bootstrap_commentwalker();
  }
  return $args;
}
add_action( 'wp_list_comments_args', 'wp_bootstrap_list_comments_args' );


/**
 * Add bootstrap classes to the comment reply link
 */
if (!function_exists('wp_bootstrap_comment_reply_link')) {
  function wp_bootstrap_comment_reply_link($link, $args, $comment, $post) {
    return $link = '<div class="form-group">' . str_replace("class='comment-reply-", "class='comment-reply- btn btn-default btn-xs ", $link) . '</div>';
  }
}


/**
 * Comment Walker
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
                    <h4 class="media-heading fn n author-name"><?php echo get_comment_author_link(); ?></h4>
                    <div class="comment-meta comment-meta-data">
                      <i class="glyphicon glyphicon-time"> </i>
                      <a href="<?php echo htmlspecialchars( get_comment_link( get_comment_ID() ) ) ?>"><?php comment_date(); ?> at <?php comment_time(); ?></a> <?php edit_comment_link( '(Edit)' ); ?>
                    </div><!-- /.comment-meta -->
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