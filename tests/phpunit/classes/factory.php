<?php

/**
 * Factory class for use in the unit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * A registry for factories to be used in the unit tests.
 *
 * @since 2.1.0
 *
 * @property-read WordPoints_PHPUnit_Factory_For_Entity $entity
 * @property-read WordPoints_PHPUnit_Factory_For_Entity_Context $entity_context
 * @property-read WordPoints_PHPUnit_Factory_For_Hook_Action $hook_action
 * @property-read WordPoints_PHPUnit_Factory_For_Hook_Condition $hook_condition
 * @property-read WordPoints_PHPUnit_Factory_For_Hook_Event $hook_event
 * @property-read WordPoints_PHPUnit_Factory_For_Hook_Extension $hook_extension
 * @property-read WordPoints_PHPUnit_Factory_For_Hook_Reaction $hook_reaction
 * @property-read WordPoints_PHPUnit_Factory_For_Hook_Reaction_Store $hook_reaction_store
 * @property-read WordPoints_PHPUnit_Factory_For_Hook_Reactor $hook_reactor
 * @property-read WordPoints_PHPUnit_Factory_For_Points_Log $points_log
 * @property-read WordPoints_PHPUnit_Factory_For_Post_Type $post_type
 * @property-read WordPoints_PHPUnit_Factory_For_Rank $rank
 * @property-read WordPoints_PHPUnit_Factory_For_User_Role $user_role
 */
class WordPoints_PHPUnit_Factory {

	/**
	 * The registered classes, indexed by slug.
	 *
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	protected $classes = array();

	/**
	 * The factory registry.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_PHPUnit_Factory
	 */
	public static $factory;

	/**
	 * Initialize the registry.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_PHPUnit_Factory The factory registry.
	 */
	public static function init() {
		return self::$factory = new WordPoints_PHPUnit_Factory();
	}

	/**
	 * @since 2.1.0
	 */
	public function __get( $var ) {

		if ( $this->is_registered( $var ) && isset( $this->classes[ $var ] ) ) {
			return $this->$var = new $this->classes[ $var ]( $this );
		}

		return null;
	}

	/**
	 * Register a factory.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug  The factory slug.
	 * @param string $class The factory class.
	 */
	public function register( $slug, $class ) {

		$this->classes[ $slug ] = $class;
	}

	/**
	 * Deregister a factory.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The factory slug.
	 */
	public function deregister( $slug ) {

		unset( $this->classes[ $slug ], $this->$slug );
	}

	/**
	 * Check if a factory is registered.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The factory slug.
	 *
	 * @return bool Whether the factory is registered.
	 */
	public function is_registered( $slug ) {

		return isset( $this->classes[ $slug ] );
	}
}

// EOF
