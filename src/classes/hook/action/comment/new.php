<?php

/**
 * New comment action class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents the new Comment action.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Action_Comment_New
	extends WordPoints_Hook_Action_Post_Type_Comment {

	/**
	 * @since 2.1.0
	 */
	protected $post_hierarchy = array( 'comment\\post', 'post\\post', 'post\\post' );

	/**
	 * @since 2.1.0
	 */
	public function should_fire() {

		if ( ! isset( $this->args[1]->comment_approved ) ) {
			return false;
		}

		if ( 1 !== (int) $this->args[1]->comment_approved ) {
			return false;
		}

		return parent::should_fire();
	}
}

// EOF
