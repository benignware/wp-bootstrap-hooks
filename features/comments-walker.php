<?php
/**
 * Comment API: Walker_Comment class
 *
 * @package WordPress
 * @subpackage Comments
 * @since 4.4.0
 */

/**
 * Core walker class used to create an HTML list of comments.
 *
 * @since 2.7.0
 *
 * @see Walker
 */
class Bootstrap_Walker_Comment extends Walker_Comment {

	/**
	 * Outputs a single comment.
	 *
	 * @since 3.6.0
	 *
	 * @see wp_list_comments()
	 *
	 * @param WP_Comment $comment Comment to display.
	 * @param int        $depth   Depth of the current comment.
	 * @param array      $args    An array of arguments.
	 */
	protected function comment( $comment, $depth, $args ) {
		if ( 'div' === $args['style'] ) {
			$tag       = 'div';
			$add_below = 'comment';
		} else {
			$tag       = 'li';
			$add_below = 'div-comment';
		}

		$commenter          = wp_get_current_commenter();
		$show_pending_links = isset( $commenter['comment_author'] ) && $commenter['comment_author'];

		if ( $commenter['comment_author_email'] ) {
			$moderation_note = __( 'Your comment is awaiting moderation.' );
		} else {
			$moderation_note = __( 'Your comment is awaiting moderation. This is a preview; your comment will be visible after it has been approved.' );
		}
		?>
		<<?php echo $tag; ?>
      <?php comment_class( $this->has_children ? 'parent' : '', $comment ); ?>
      id="comment-<?php comment_ID(); ?>"
      <?php if ( 0 != $args['avatar_size'] ) : ?>style="margin-left: <?= $args['avatar_size']?>px"<?php endif ?>
    >
		<?php if ( 'div' !== $args['style'] ) : ?>
		<div id="div-comment-<?php comment_ID(); ?>" class="comment-body">
		<?php endif; ?>
    <div class="d-flex">
      <div class="col-auto">
        <?php
          if ( 0 != $args['avatar_size'] ) {
            echo get_avatar( $comment, $args['avatar_size'] );
          }
        ?>
      </div>
      <div class="col ps-2">
        
        <?php
        $comment_author = get_comment_author_link( $comment );

        if ( '0' == $comment->comment_approved && ! $show_pending_links ) {
          $comment_author = get_comment_author( $comment );
        }

        printf(
          /* translators: %s: Comment author link. */
          __( '%s <span class="says">says:</span>' ),
          sprintf( '<cite class="fn">%s</cite>', $comment_author )
        );
        ?>

        <div class="comment-meta commentmetadata">
          <?php
          printf(
            '<a href="%s">%s</a>',
            esc_url( get_comment_link( $comment, $args ) ),
            sprintf(
              /* translators: 1: Comment date, 2: Comment time. */
              __( '%1$s at %2$s' ),
              get_comment_date( '', $comment ),
              get_comment_time()
            )
          );

          edit_comment_link( __( '(Edit)' ), ' &nbsp;&nbsp;', '' );
          ?>
        </div>
      
		<?php if ( '0' == $comment->comment_approved ) : ?>
		<em class="comment-awaiting-moderation"><?php echo $moderation_note; ?></em>
		<br />
		<?php endif; ?>

		<div class="comment-meta commentmetadata">
			<?php
			  edit_comment_link( __( '(Edit)' ), ' &nbsp;&nbsp;', '' );
			?>
		</div>

    <?php
		comment_text(
			$comment,
			array_merge(
				$args,
				array(
					'add_below' => $add_below,
					'depth'     => $depth,
					'max_depth' => $args['max_depth'],
				)
			)
		);
		?>

		<?php
		comment_reply_link(
			array_merge(
				$args,
				array(
					'add_below' => $add_below,
					'depth'     => $depth,
					'max_depth' => $args['max_depth'],
					'before'    => '<div class="reply">',
					'after'     => '</div>',
				)
			)
		);
		?>

      </div>
    </div>

		<?php if ( 'div' !== $args['style'] ) : ?>
		</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Outputs a comment in the HTML5 format.
	 *
	 * @since 3.6.0
	 *
	 * @see wp_list_comments()
	 *
	 * @param WP_Comment $comment Comment to display.
	 * @param int        $depth   Depth of the current comment.
	 * @param array      $args    An array of arguments.
	 */
	protected function html5_comment( $comment, $depth, $args ) {
		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';

		$commenter          = wp_get_current_commenter();
		$show_pending_links = ! empty( $commenter['comment_author'] );

    $avatar_size = $depth > 1 ? round($args['avatar_size'] * 0.8) : $args['avatar_size'];

    $comment_class = implode(' ', array_values(array_filter([
      $depth > 1 ? 'ms-2' : '',
      $this->has_children ? 'parent' : '',
      0 != $avatar_size ? 'position-relative' : ''
    ])));

		if ( $commenter['comment_author_email'] ) {
			$moderation_note = __( 'Your comment is awaiting moderation.' );
		} else {
			$moderation_note = __( 'Your comment is awaiting moderation. This is a preview; your comment will be visible after it has been approved.' );
		}
		?>
		<<?php echo $tag; ?>
      id="comment-<?php comment_ID(); ?>"
      <?php comment_class( $comment_class, $comment ); ?>
      <?php if ( 0 != $avatar_size ) : ?>style="padding-left: <?= $avatar_size ?>px"<?php endif ?>
    >
        <style>
          #div-comment-<?= comment_ID(); ?> {
            /*
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 10;
            overflow: hidden;
            */
          }
        </style>
			<article id="div-comment-<?php comment_ID(); ?>" class="comment-body ms-2">
				<footer class="comment-meta d-md-flex small mb-md-1">
					<div class="comment-author vcard col">
						<?php
						if ( 0 != $avatar_size ) {
							echo get_avatar( $comment, $avatar_size, '', '', [
                'class' => 'position-absolute start-0 top-0',
                // 'style' => 'position: absolute; left: 0; top: 0'
              ] );
						}
						?>
						<?php
						$comment_author = get_comment_author_link( $comment );

						if ( '0' == $comment->comment_approved && ! $show_pending_links ) {
							$comment_author = get_comment_author( $comment );
						}

						printf(
							/* translators: %s: Comment author link. */
							__( '%s <span class="says opacity-75">says:</span>' ),
							sprintf( '<b class="fn">%s</b>', $comment_author )
						);
						?>
					</div><!-- .comment-author -->

					<div class="comment-metadata">
						<?php
						printf(
							'<a href="%s" class="opacity-50 text-decoration-none text-muted"><time datetime="%s">%s</time></a>',
							esc_url( get_comment_link( $comment, $args ) ),
							get_comment_time( 'c' ),
							sprintf(
								/* translators: 1: Comment date, 2: Comment time. */
								__( '%1$s at %2$s' ),
								get_comment_date( '', $comment ),
								get_comment_time()
							)
						);

						edit_comment_link( __( 'Edit' ), ' <span class="edit-link">', '</span>' );
						?>
					</div><!-- .comment-metadata -->

					<?php if ( '0' == $comment->comment_approved ) : ?>
					<em class="comment-awaiting-moderation"><?php echo $moderation_note; ?></em>
					<?php endif; ?>
				</footer><!-- .comment-meta -->

				<div class="comment-content small">
					<?php comment_text(); ?>
				</div><!-- .comment-content -->

				<?php
				if ( '1' == $comment->comment_approved || $show_pending_links ) {
					comment_reply_link(
						array_merge(
							$args,
							array(
								'add_below' => 'div-comment',
								'depth'     => $depth,
								'max_depth' => $args['max_depth'],
								'before'    => '<div class="reply">',
								'after'     => '</div>',
							)
						)
					);
				}
				?>
			</article><!-- .comment-body -->
		<?php
	}
}