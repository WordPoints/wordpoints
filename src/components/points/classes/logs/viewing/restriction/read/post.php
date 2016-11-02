<?php

/**
 * Read post points logs viewing restriction class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Restricts users from viewing a points log if they cannot view the post it is for.
 *
 * @since 2.2.0
 */
class WordPoints_Points_Logs_Viewing_Restriction_Read_Post
	implements WordPoints_Points_Logs_Viewing_RestrictionI {

	/**
	 * The log object this restriction relates to.
	 *
	 * @since 2.2.0
	 *
	 * @var object
	 */
	protected $log;

	/**
	 * The ID of the post that this restriction relates to.
	 *
	 * @since 2.2.0
	 *
	 * @var int
	 */
	protected $post_id;

	/**
	 * Whether the related post has a public status.
	 *
	 * @since 2.2.0
	 *
	 * @var bool
	 */
	protected $is_public = false;

	/**
	 * @since 2.2.0
	 */
	public function __construct( $log ) {

		$this->log = $log;

		$this->post_id = $this->get_post_id();

		if ( ! $this->post_id ) {

			// We remove any reference to the post from the log when it is deleted,
			// so it is OK to show it publicly.
			$this->is_public = true;

		} else {

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
	public function get_description() {
		return __( 'This log entry is only visible to users who can view the post.', 'wordpoints' );
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
	 * Get the ID of the post that this restriction relates to.
	 *
	 * @since 2.2.0
	 *
	 * @return int The ID of the post.
	 */
	protected function get_post_id() {

		return (int) wordpoints_get_points_log_meta(
			$this->log->id
			, 'post_id'
			, true
		);
	}
}

// EOF
