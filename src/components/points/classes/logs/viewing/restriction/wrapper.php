<?php

/**
 * Points logs viewing restriction wrapper class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Wraps a group of points logs restrictions to simplify handling.
 *
 * Instead of having to manually loop through a group of restrictions, they can be
 * wrapped in an instance of this wrapper, and then it will loop through them
 * internally as needed.
 *
 * @since 2.2.0
 */
class WordPoints_Points_Logs_Viewing_Restriction_Wrapper
	implements WordPoints_Points_Logs_Viewing_RestrictionI {

	/**
	 * The restrictions being wrapped.
	 *
	 * @since 2.2.0
	 *
	 * @var WordPoints_Points_Logs_Viewing_RestrictionI[]
	 */
	protected $restrictions = array();

	/**
	 * The points log.
	 *
	 * @since 2.2.0
	 *
	 * @var object
	 */
	protected $log;

	/**
	 * @since 2.2.0
	 *
	 * @param object                                        $log          Points log.
	 * @param WordPoints_Points_Logs_Viewing_RestrictionI[] $restrictions Restrictions.
	 */
	public function __construct( $log, array $restrictions = array() ) {

		$this->log = $log;

		foreach ( $restrictions as $restriction ) {

			if ( ! $restriction instanceof WordPoints_Points_Logs_Viewing_RestrictionI ) {
				continue;
			}

			if ( ! $restriction->applies() ) {
				continue;
			}

			$this->restrictions[] = $restriction;
		}
	}

	/**
	 * @since 2.2.0
	 */
	public function get_description() {

		$descriptions = array();

		foreach ( $this->restrictions as $restriction ) {

			$descriptions = array_merge(
				$descriptions
				, (array) $restriction->get_description()
			);
		}

		return $descriptions;
	}

	/**
	 * @since 2.2.0
	 */
	public function applies() {
		return ! empty( $this->restrictions );
	}

	/**
	 * @since 2.2.0
	 */
	public function user_can( $user_id ) {

		if ( $this->log->blog_id && get_current_blog_id() !== (int) $this->log->blog_id ) {
			$switched = switch_to_blog( $this->log->blog_id );
		}

		$user_can = true;

		foreach ( $this->restrictions as $restriction ) {
			if ( ! $restriction->user_can( $user_id ) ) {
				$user_can = false;
				break;
			}
		}

		if ( isset( $switched ) ) {
			restore_current_blog();
		}

		return $user_can;
	}
}

// EOF
