<?php

/**
 * Comment post type action class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Bootstrap for comment-related actions that occur across multiple post types.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Action_Post_Type_Comment
	extends WordPoints_Hook_Action_Post_Type {

	/**
	 * @since 2.1.0
	 */
	protected $post_hierarchy = array( 'comment\\post', 'post\\post', 'post\\post' );

	/**
	 * The type of comment that this action should fire for.
	 *
	 * All other types of comment will be ignored.
	 *
	 * Setting this to an empty value will cause the action to fire for all comment
	 * types.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	protected $comment_type = 'comment';

	/**
	 * @since 2.3.0
	 */
	public function __construct( $slug, array $action_args, array $args = array() ) {

		if ( isset( $args['comment_type'] ) ) {
			$this->comment_type = $args['comment_type'];
		}

		parent::__construct( $slug, $action_args, $args );
	}

	/**
	 * @since 2.3.0
	 */
	public function should_fire() {

		if ( $this->comment_type ) {

			$parts = wordpoints_parse_dynamic_slug( $this->slug );

			if ( ! $parts['dynamic'] ) {
				return false;
			}

			$arg_slug = str_replace(
				'\\post'
				, '\\' . $parts['dynamic']
				, 'comment\\post'
			);

			$comment = $this->get_arg_value( $arg_slug );

			if ( ! $comment ) {
				return false;
			}

			$comment_type = $comment->comment_type;

			// Empty means that it is a regular comment.
			if ( '' === $comment_type ) {
				$comment_type = 'comment';
			}

			if ( $comment_type !== $this->comment_type ) {
				return false;
			}
		}

		return parent::should_fire();
	}
}

// EOF
