<?php

/**
 * Legacy post publish hook event class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents a hook event that occurs when a post is published.
 *
 * This legacy version reverses when a post is deleted, instead of when it is just
 * depublished.
 *
 * @since 2.1.0
 */
class WordPoints_Points_Hook_Event_Post_Publish_Legacy
	extends WordPoints_Hook_Event_Post_Publish {

	/**
	 * @since 2.1.0
	 */
	public function get_title() {

		// translators: Hook event title.
		return sprintf( __( '%s (Legacy)', 'wordpoints' ), parent::get_title() );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_description() {

		return parent::get_description()
			. ' '
			. __(
				'This legacy version only reverts the transaction when the post is permanently deleted. The regular version does so each time the post status is changed from Published to another status.'
				, 'wordpoints'
			);
	}

	/**
	 * @since 2.1.0
	 */
	public function get_reversal_text() {

		$parsed = wordpoints_parse_dynamic_slug( $this->slug );

		switch ( $parsed['dynamic'] ) {

			case 'post':
				return __( 'Post deleted.', 'wordpoints' );

			case 'page':
				return __( 'Page deleted.', 'wordpoints' );

			default:
				return sprintf(
					// translators: Singular post type name.
					_x( '%s deleted.', 'post type', 'wordpoints' )
					, $this->get_entity_title()
				);
		}
	}
}

// EOF
