<?php

/**
 * Static rank group container class.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Static rank group container.
 *
 * @since 1.7.0
 */
final class WordPoints_Rank_Groups {

	/**
	 * A list of registered groups.
	 *
	 * @since 1.7.0
	 *
	 * @type WordPoints_Rank_Group[] $groups
	 */
	private static $groups;

	/**
	 * Check if a group is registered.
	 *
	 * @since 1.7.0
	 *
	 * @param string $slug The slug of the group to check for.
	 *
	 * @return bool Whether the group is registered.
	 */
	public static function is_group_registered( $slug ) {

		return isset( self::$groups[ $slug ] );
	}

	/**
	 * Register a rank group.
	 *
	 * @since 1.7.0
	 *
	 * @param string $slug A unique identifier for this group.
	 * @param array  $data {@see WordPoints_Rank_Group::__construct()}
	 *
	 * @return bool True if registered, false if already registered.
	 */
	public static function register_group( $slug, $data ) {

		if ( self::is_group_registered( $slug ) ) {
			return false;
		}

		self::$groups[ $slug ] = new WordPoints_Rank_Group( $slug, $data );

		// If this is a brand new group, create the base rank.
		if ( ! self::$groups[ $slug ]->get_base_rank() ) {
			wordpoints_add_rank( '', 'base', $slug, 0 );
		}

		return true;
	}

	/**
	 * Deregister a rank group.
	 *
	 * @since 1.7.0
	 *
	 * @param string $slug The slug of the group to deregister.
	 *
	 * @return bool True if deregistered, false if not registered.
	 */
	public static function deregister_group( $slug ) {

		if ( ! self::is_group_registered( $slug ) ) {
			return false;
		}

		unset( self::$groups[ $slug ] );

		return true;
	}

	/**
	 * Get all of the registered rank groups.
	 *
	 * @since 1.7.0
	 *
	 * @return WordPoints_Rank_Group[] The registered groups for ranks.
	 */
	public static function get() {

		if ( ! did_action( 'wordpoints_ranks_register' ) ) {
			_doing_it_wrong(
				__METHOD__
				, 'Rank groups should not be retreived until after they are all registered.'
				, '1.7.0'
			);
		}

		if ( ! self::$groups ) {
			self::$groups = array();
		}

		return self::$groups;
	}

	/**
	 * Get the handler object for a registered rank group.
	 *
	 * @since 1.7.0
	 *
	 * @param string $group The slug for the group of rank to get the handler of.
	 *
	 * @return WordPoints_Rank_Type|false The hander object, or false.
	 */
	public static function get_group( $group ) {

		if ( ! self::is_group_registered( $group ) ) {
			return false;
		}

		return self::$groups[ $group ];
	}

	/**
	 * Check if a rank type is registered for a group.
	 *
	 * Note that in the case of a false return value, it is possible that the group
	 * is not registered either. You can use self::is_group_registered() to check
	 * that if it is important.
	 *
	 * @since 1.7.0
	 *
	 * @param string $type  The slug of the rank to to check for.
	 * @param string $group The slug of the group to check in.
	 *
	 * @return bool Whether the rank type is registered for the group.
	 */
	public static function is_type_registered_for_group( $type, $group ) {

		if ( ! self::is_group_registered( $group ) ) {
			return false;
		}

		return self::$groups[ $group ]->has_type( $type );
	}

	/**
	 * Register a rank type for a rank group.
	 *
	 * Rank types must be registered for a rank group to be used in that rank group.
	 *
	 * @since 1.7.0
	 *
	 * @param string $type  The slug of the rank type.
	 * @param string $group The slug of the group to register this rank for.
	 *
	 * @return bool True on success. False if the group is invalid.
	 */
	public static function register_type_for_group( $type, $group ) {

		if ( ! self::is_group_registered( $group ) ) {
			return false;
		}

		return self::$groups[ $group ]->add_type( $type );
	}

	/**
	 * Deregister a rank type for a group.
	 *
	 * @since 1.7.0
	 *
	 * @param string $type  The slug of the rank type.
	 * @param string $group The slug of the group to deregister this rank for.
	 *
	 * @return bool True on sucess. False if the group is invalid, or the rank type
	 *              isn't registered for it.
	 */
	public static function deregister_type_for_group( $type, $group ) {

		if ( ! self::is_group_registered( $group ) ) {
			return false;
		}

		if ( ! self::is_type_registered_for_group( $type, $group ) ) {
			return false;
		}

		return self::$groups[ $group ]->remove_type( $type );
	}
}

// EOF
