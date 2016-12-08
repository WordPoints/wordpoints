<?php

/**
 * Status nonpublic post entity restriction class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Restriction rule for posts with a nonpublic status.
 *
 * @since 2.2.0
 */
class WordPoints_Entity_Restriction_Post_Status_Nonpublic
	implements WordPoints_Entity_RestrictionI {

	/**
	 * The ID of the entity this restriction relates to.
	 *
	 * @since 2.2.0
	 *
	 * @var int|string
	 */
	protected $entity_id;

	/**
	 * The post ID.
	 *
	 * @since 2.2.0
	 *
	 * @var int
	 */
	protected $post_id;

	/**
	 * Whether the post has a public status.
	 *
	 * @since 2.2.0
	 *
	 * @var bool
	 */
	protected $is_public = false;

	/**
	 * @since 2.2.0
	 */
	public function __construct( $entity_id, array $hierarchy ) {

		$this->entity_id = $entity_id;
		$this->post_id   = $this->get_post_id();

		if ( $this->post_id ) {

			$post_status = get_post_status_object(
				get_post_status( $this->post_id )
			);

			if ( $post_status && $post_status->public ) {
				$this->is_public = true;
			}
		}
	}

	/**
	 * @since 2.2.0
	 */
	public function user_can( $user_id ) {

		if ( $this->is_public ) {
			return true;
		}

		// If the post doesn't have a public status, fall back to the caps API.
		return user_can( $user_id, 'read_post', $this->post_id );
	}

	/**
	 * @since 2.2.0
	 */
	public function applies() {
		return ! $this->is_public;
	}

	/**
	 * Get the ID of the post that this restriction is being checked for.
	 *
	 * @since 2.2.0
	 *
	 * @return int The ID of the post to check this restriction for.
	 */
	protected function get_post_id() {
		return $this->entity_id;
	}
}

// EOF
