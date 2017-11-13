<?php

/**
 * Points logs viewing restrictions class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Registry for points logs viewing restrictions.
 *
 * @since 2.2.0
 */
class WordPoints_Points_Logs_Viewing_Restrictions
	extends WordPoints_Class_Registry_Children {

	/**
	 * @since 2.2.0
	 */
	protected $settings = array(
		'pass_slugs' => false,
	);

	/**
	 * Get the restrictions for a points log.
	 *
	 * @since 2.2.0
	 *
	 * @param object $log The log object.
	 *
	 * @return WordPoints_Points_Logs_Viewing_RestrictionI Restriction for this log.
	 */
	public function get_restriction( $log ) {

		if ( $log->blog_id && get_current_blog_id() !== (int) $log->blog_id ) {
			$switched = switch_to_blog( $log->blog_id );
		}

		$restrictions = array();

		foreach ( array( 'all', $log->log_type ) as $slug ) {

			if ( isset( $this->classes[ $slug ] ) ) {

				$restrictions = array_merge(
					$restrictions
					, array_values( $this->get_children( $slug, array( $log ) ) )
				);
			}
		}

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Wrapper(
			$log
			, $restrictions
		);

		if ( isset( $switched ) ) {
			restore_current_blog();
		}

		return $restriction;
	}

	/**
	 * Checks if the legacy filters bock a user from viewing a log.
	 *
	 * @since 2.2.0
	 *
	 * @param int    $user_id The ID of the user.
	 * @param object $log     The object for the points log entry.
	 *
	 * @return bool Whether the legacy filters say the user can view the points log.
	 */
	public function apply_legacy_filters( $user_id, $log ) {

		if ( $log->blog_id && get_current_blog_id() !== (int) $log->blog_id ) {
			$switched = switch_to_blog( $log->blog_id );
		}

		$current_user = wp_get_current_user();

		// Back-compat for WordPoints pre-2.1.0, when the below filter assumed that
		// the user in question was the current user.
		if ( $user_id !== $current_user->ID ) {
			wp_set_current_user( $user_id );
		}

		/**
		 * Filter whether a user can view this points log.
		 *
		 * This is a dynamic hook, where the {$log->log_type} portion will
		 * be the type of this log entry. For example, for a registration log
		 * it would be 'wordpoints_user_can_view_points_log-register'.
		 *
		 * @since 1.3.0
		 * @since 2.1.0 The $user_id parameter was added.
		 * @deprecated 2.2.0
		 *
		 * @param bool   $can_view Whether the user can view the log entry
		 *                         (the default is true).
		 * @param object $log      The log entry object.
		 * @param int    $user_id  The ID of the user to check
		 */
		$can_view = apply_filters_deprecated(
			"wordpoints_user_can_view_points_log-{$log->log_type}"
			, array( true, $log, $user_id )
			, '2.2.0'
			, false
			, 'Use the points logs viewing restrictions API instead.'
		);

		// Restore the current user after the temporary override above.
		if ( $user_id !== $current_user->ID ) {
			wp_set_current_user( $current_user->ID );
		}

		/**
		 * Filter whether a user can view a points log.
		 *
		 * @since 2.1.0
		 * @deprecated 2.2.0
		 *
		 * @param bool   $can_view Whether the user can view the points log.
		 * @param int    $user_id  The user's ID.
		 * @param object $log      The points log object.
		 */
		$can_view = apply_filters_deprecated(
			'wordpoints_user_can_view_points_log'
			, array( $can_view, $user_id, $log )
			, '2.2.0'
			, false
			, 'Use the points logs viewing restrictions API instead.'
		);

		if ( isset( $switched ) ) {
			restore_current_blog();
		}

		return $can_view;
	}
}

// EOF
