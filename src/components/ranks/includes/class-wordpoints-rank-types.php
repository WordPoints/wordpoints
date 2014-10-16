<?php

/**
 * Rank types class.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Static container for rank types.
 *
 * WordPoints allows for different types of ranks. Each rank type is represented by
 * a WordPoints_Rank_Type class child, which is registered with this class.
 *
 * @since 1.7.0
 */
final class WordPoints_Rank_Types {

	/**
	 * The registered rank types.
	 *
	 * @since 1.7.0
	 *
	 * @var WordPoints_Rank_Type[]
	 */
	private static $types;

	/**
	 * Check if a type of rank is registered.
	 *
	 * @since 1.7.0
	 *
	 * @param string $type The slug for a rank type.
	 *
	 * @return bool Whether this rank type is registered.
	 */
	public static function is_type_registered( $type ) {

		return isset( self::$types[ $type ] );
	}

	/**
	 * Register a new type of rank.
	 *
	 * Each type of rank is handled by a child of the WordPoints_Rank_Type class.
	 * Each has different metadata, and is triggered by different things.
	 *
	 * @since 1.7.0
	 *
	 * @param string $type  The unique slug for this rank type.
	 * @param string $class The name of the class to handle this rank type.
	 * @param array  $args  Arguments to pass to the new rank type.
	 *
	 * @return bool Whether the rank type was registered.
	 */
	public static function register_type( $type, $class, array $args = array() ) {

		if ( self::is_type_registered( $type ) ) {
			return false;
		}

		$args['slug'] = $type;

		self::$types[ $type ] = new $class( $args );

		return true;
	}

	/**
	 * Deregister a registered type of rank.
	 *
	 * Note that the 'base' rank type cannot be deregistered.
	 *
	 * @since 1.7.0
	 *
	 * @param string $type The unique slug for the rank type to deregister.
	 *
	 * @return bool Whether the rank type was deregistered.
	 */
	public static function deregister_type( $type ) {

		if ( 'base' === $type ) {
			return false;
		}

		if ( ! self::is_type_registered( $type ) ) {
			return false;
		}

		self::$types[ $type ]->destruct();

		foreach ( WordPoints_Rank_Groups::get() as $group ) {
			$group->remove_type( $type );
		}

		unset( self::$types[ $type ] );

		return true;
	}

	/**
	 * Get all of the registered rank types.
	 *
	 * @since 1.7.0
	 *
	 * @return WordPoints_Rank_Type[] The registered types of ranks.
	 */
	public static function get() {

		if ( ! did_action( 'wordpoints_ranks_register' ) ) {
			_doing_it_wrong(
				__METHOD__
				, 'Ranks should not be retreived until after they are all registered.'
				, '1.7.0'
			);
		}

		return self::$types;
	}

	/**
	 * Get the handler object for a registered rank type.
	 *
	 * @since 1.7.0
	 *
	 * @param string $type The slug for the type of rank to get the handler of.
	 *
	 * @return WordPoints_Rank_Type|false The hander object, or false.
	 */
	public static function get_type( $type ) {

		if ( ! self::is_type_registered( $type ) ) {
			return false;
		}

		return self::$types[ $type ];
	}

	/**
	 * Initialize the ranks API.
	 *
	 * @since 1.7.0
	 *
	 * @WordPress\action wordpoints_modules_loaded
	 */
	public static function init() {

		self::register_type( 'base', 'WordPoints_Base_Rank_Type' );

		/**
		 * Ranks should not register later than this action.
		 *
		 * @since 1.7.0
		 */
		do_action( 'wordpoints_ranks_register' );

		/**
		 * All ranks are registered.
		 *
		 * @since 1.7.0
		 */
		do_action( 'wordpoints_ranks_registered' );
	}
}
add_action( 'wordpoints_modules_loaded', 'WordPoints_Rank_Types::init' );

// EOF
