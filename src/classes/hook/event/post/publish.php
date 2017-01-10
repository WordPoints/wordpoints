<?php

/**
 * Post publish hook event class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents a hook event that occurs when a post is published.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Event_Post_Publish
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
				return __( 'Publish Post', 'wordpoints' );

			case 'page':
				return __( 'Publish Page', 'wordpoints' );

			default:
				return sprintf(
					// translators: Singular post type name.
					__( 'Publish %s', 'wordpoints' )
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
				return __( 'When a Post is published.', 'wordpoints' );

			case 'page':
				return __( 'When a Page is published.', 'wordpoints' );

			default:
				return sprintf(
					// translators: Singular post type name.
					__( 'When a %s is published.', 'wordpoints' )
					, $this->get_entity_title()
				);
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function get_reversal_text() {

		$parsed = wordpoints_parse_dynamic_slug( $this->slug );

		switch ( $parsed['dynamic'] ) {

			case 'post':
				return __( 'Post removed.', 'wordpoints' );

			case 'page':
				return __( 'Page removed.', 'wordpoints' );

			default:
				return sprintf(
					// translators: Singular post type name.
					_x( '%s removed.', 'post type', 'wordpoints' )
					, $this->get_entity_title()
				);
		}
	}
}

// EOF
