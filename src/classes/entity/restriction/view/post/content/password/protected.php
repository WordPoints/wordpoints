<?php

/**
 * Password protected post content entity viewing restriction class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Viewing restriction rule for contents of password protected posts.
 *
 * @since 2.2.0
 */
class WordPoints_Entity_Restriction_View_Post_Content_Password_Protected
	implements WordPoints_Entity_RestrictionI {

	/**
	 * Whether the post has a password.
	 *
	 * @since 2.2.0
	 *
	 * @var bool
	 */
	protected $has_password = false;

	/**
	 * @since 2.2.0
	 */
	public function __construct( $entity_id, array $hierarchy ) {

		if ( $entity_id ) {

			$post = get_post( $entity_id );

			if ( $post && $post->post_password ) {
				$this->has_password = true;
			}
		}
	}

	/**
	 * @since 2.2.0
	 */
	public function user_can( $user_id ) {
		return ! $this->has_password;
	}

	/**
	 * @since 2.2.0
	 */
	public function applies() {
		return $this->has_password;
	}
}

// EOF
