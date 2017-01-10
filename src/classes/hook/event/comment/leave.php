<?php

/**
 * Comment leave hook event class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents a hook event that occurs when a comment is left.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Event_Comment_Leave
	extends WordPoints_Hook_Event_Dynamic
	implements WordPoints_Hook_Event_ReversingI {

	/**
	 * @since 2.1.0
	 */
	protected $generic_entity_slug = 'post';

	/**
	 * @since 2.1.0
	 */
	public function get_title() {

		$parsed = wordpoints_parse_dynamic_slug( $this->slug );

		switch ( $parsed['dynamic'] ) {

			case 'post':
				return __( 'Comment on a Post', 'wordpoints' );

			case 'page':
				return __( 'Comment on a Page', 'wordpoints' );

			case 'attachment':
				return __( 'Comment on a Media Upload', 'wordpoints' );

			default:
				return sprintf(
					// translators: Singular post type name.
					__( 'Comment on a %s', 'wordpoints' )
					, $this->get_entity_title()
				);
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function get_description() {

		$parsed = wordpoints_parse_dynamic_slug( $this->slug );

		switch ( $parsed['dynamic'] ) {

			case 'post':
				return __( 'When a user leaves a comment on a Post.', 'wordpoints' );

			case 'page':
				return __( 'When a user leaves a comment on a Page.', 'wordpoints' );

			case 'attachment':
				return __( 'When a user leaves a comment on a file uploaded to the Media Library.', 'wordpoints' );

			default:
				return sprintf(
					// translators: Singular post type name.
					__( 'When a user leaves a comment on a %s.', 'wordpoints' )
					, $this->get_entity_title()
				);
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function get_reversal_text() {
		return __( 'Comment removed.', 'wordpoints' );
	}
}

// EOF
