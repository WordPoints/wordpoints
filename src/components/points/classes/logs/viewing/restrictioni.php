<?php

/**
 * Points logs viewing restriction interface.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Defines the API for points logs viewing restrictions.
 *
 * @since 2.2.0
 */
interface WordPoints_Points_Logs_Viewing_RestrictionI {

	/**
	 * @since 2.2.0
	 *
	 * @param object $log The object for the points log the restriction is for.
	 */
	public function __construct( $log );

	/**
	 * Get a description of this restriction.
	 *
	 * @since 2.2.0
	 *
	 * @return string The description of this restriction.
	 */
	public function get_description();

	/**
	 * Check whether this restriction applies to this points log.
	 *
	 * @since 2.2.0
	 *
	 * @return bool Whether the restriction applies to this points log.
	 */
	public function applies();

	/**
	 * Check if this restriction bars a particular user from viewing this log.
	 *
	 * @since 2.2.0
	 *
	 * @param int $user_id The ID of the user to check against.
	 *
	 * @return bool Whether the user can view the points log.
	 */
	public function user_can( $user_id );
}

// EOF
