<?php

/**
 * WordPoints_Components class.
 *
 * This class handles the loading, activation/deactivation, etc., of the included
 * components. The file includes the class and a few wrapper functions.
 *
 * @package WordPoints\Components
 * @since 1.0.0
 */

// Instantiate the class.
WordPoints_Components::set_up();

/**
 * Component registration wrapper.
 *
 * @since 1.0.0
 *
 * @uses WordPoints_Components::register()
 *
 * @param array $args The component args.
 *
 * @return bool Whether the component was registered.
 */
function wordpoints_component_register( $args ) {

	return WordPoints_Components::instance()->register( $args );
}

/**
 * Component activation check wrapper.
 *
 * @since 1.0.0
 *
 * @uses WordPoints_Components::is_active()
 *
 * @param string $slug The component slug.
 *
 * @return bool Whether the component is active.
 */
function wordpoints_component_is_active( $slug ) {

	return WordPoints_Components::instance()->is_active( $slug );
}

/**
 * Load, register, activate and deactivate components.
 *
 * This class registers the components, checks if the are active, and performs other
 * similar duties related to them. The included components are ranks, achievements,
 * and of course, points.
 *
 * The class is a singleton, with one instance which must be accessed by calling the
 * instance() member method.
 *
 * @since 1.0.0
 */
final class WordPoints_Components {

	//
	// Private Vars.
	//

	/**
	 * The one-and-only.
	 *
	 * @since 1.0.0
	 *
	 * @type WordPoints_Components $instance
	 */
	private static $instance;

	/**
	 * The registered components.
	 *
	 * @since 1.0.0
	 *
	 * @type array $registered
	 */
	private $registered;

	/**
	 * The activated components.
	 *
	 * @since 1.0.0
	 *
	 * @type array $active
	 */
	private $active;

	//
	// Private Methods.
	//

	/**
	 * Don't construct the class this way.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

	/**
	 * I am 1.
	 *
	 * @since 1.0.0
	 */
	private function __clone() {}

	/**
	 * Reset the $active member var.
	 *
	 * This resets the array of active modules from the database when it is updated.
	 *
	 * @since 1.0.0
	 */
	private function _reset_active() {

		$this->active = wordpoints_get_array_option( 'wordpoints_active_components' );
	}

	//
	// Public Methods.
	//

	/**
	 * Set up the class.
	 *
	 * This function is called at the top of the class file to set up the class.
	 *
	 * You should not call this function directly.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_action() To load the components on 'plugins_loaded'.
	 */
	public static function set_up() {

		if ( isset( self::$instance ) )
			return;

		self::$instance = new WordPoints_Components();

		self::$instance->_reset_active();

		add_action( 'plugins_loaded', array( self::$instance, 'load' ) );
	}

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 *
	 * @return WordPoints_Components The single class instance.
	 */
	public static function instance() {

		return self::$instance;
	}

	/**
	 * Load all components.
	 *
	 * @since 1.0.0
	 *
	 * @action plugins_loaded Before modules are loaded. Added by the init() method.
	 *
	 * @uses wordpoints_dir_include() To include the component's main files.
	 * @uses do_action() To call 'wordpoints_components_register'.
	 * @uses do_action() To call 'wordpoints_components_loaded'.
	 */
	public function load() {

		wordpoints_dir_include( WORDPOINTS_DIR . 'components/' );

		/**
		 * Registration of included components.
		 *
		 * This action is for the included components to hook into. It's not possible
		 * for components that are inside modules to use it, becuase they are
		 * loaded later. Just register your component on the modules registered
		 * action instead.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wordpoints_components_register' );

		/**
		 * Components loaded and registered.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wordpoints_components_loaded' );
	}

	/**
	 * Get all registered components.
	 *
	 * This function cannnot be called until after the {@see
	 * 'wordpoints_components_register'} hook.
	 *
	 * @since 1.0.0
	 *
	 * @return array|bool Will return false if called too early.
	 */
	public function get() {

		if ( ! isset( $this->registered ) )
			return false;

		return $this->registered;
	}

	/**
	 * Get all active components.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_active() {

		return $this->active;
	}

	/**
	 * Check if a component is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The component's slug.
	 *
	 * @return bool True if the component is registered.
	 */
	public function is_registered( $slug ) {

		return isset( $this->registered[ $slug ] );
	}

	/**
	 * Register a component.
	 *
	 * Only the slug and name arguments are required.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The component's data. {
	 *        @type string $slug          The component slug. Must be unique.
	 *        @type string $name          The name of the component.
	 *        @type string $author        The name of the component's author.
	 *        @type string $author_uri    The author's webpage.
	 *        @type string $component_uri The component's webpage.
	 *        @type string $description   A description of what the component does.
	 *        @type string $version       The component's version number.
	 * }
	 *
	 * @return bool False if the component's slug has already been registered.
	 */
	public function register( $args ) {

		$defaults = array(
			'slug'          => '',
			'name'          => '',
			'author'        => '',
			'author_uri'    => '',
			'component_uri' => '',
			'description'   => '',
			'version'       => '',
		);

		$component = wp_parse_args( $args, $defaults );

		$slug = $component['slug'];

		if ( $this->is_registered( $slug ) || empty( $component['name'] ) || empty( $slug ) )
			return false;

		$this->registered[ $slug ] = array_intersect_key( $component, $defaults );

		return true;
	}

	/**
	 * Deregister a component.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The component's slug.
	 *
	 * @return bool True if the component isn't registered.
	 */
	public function deregister( $slug ) {

		if ( isset( $this->registered[ $slug ] ) ) {

			/**
			 * Component being deregistered.
			 *
			 * @since 1.0.0
			 */
			do_action( "wordpoints_component_deregiseter-{$slug}" );

			unset( $this->registered[ $slug ] );
		}

		return true;
	}

	/**
	 * Activate a component.
	 *
	 * The component won't be activated unless it is registered. The return value is
	 * in reference to whether the state of the component is that desired, not
	 * whether the state has actually been changed. It is recommended to check the
	 * state of the componenent before calling this function using is_active() if
	 * that is important to you.
	 *
	 * @since 1.0.0
	 *
	 * @uses do_action() To call the component's activation hook.
	 *
	 * @param string $slug The component's slug.
	 *
	 * @return bool
	 */
	public function activate( $slug ) {

		if ( ! $this->is_registered( $slug ) )
			return false;

		// If this component isn't already active, activate it.
		if ( ! $this->is_active( $slug ) ) {

			$this->active[ $slug ] = 1;

			if ( ! update_option( 'wordpoints_active_components', $this->active ) ) {

				$this->_reset_active();
				return false;
			}

			/**
			 * Component activated.
			 *
			 * Hook into this to perform installation if needed.
			 *
			 * @since 1.0.0
			 */
			do_action( "wordpoints_component_activate-{$slug}" );
		}

		return true;
	}

	/**
	 * Deactivate a component.
	 *
	 * The returned value does not indicate that the action has been performed, but
	 * that the component is currently inactive.
	 *
	 * @since 1.0.0
	 *
	 * @uses do_action() To call the componenet's deactivation hook.
	 *
	 * @param string $slug The comonent's slug.
	 *
	 * @return bool
	 */
	public function deactivate( $slug ) {

		if ( ! $this->is_registered( $slug ) )
			return false;

		if ( $this->is_active( $slug ) ) {

			unset( $this->active[ $slug ] );

			if ( ! update_option( 'wordpoints_active_components', $this->active ) ) {

				$this->_reset_active();
				return false;
			}

			/**
			 * Component deactivated.
			 *
			 * @since 1.0.0
			 */
			do_action( "wordpoints_component_deactivate-{$slug}" );
		}

		return true;
	}

	/**
	 * Check if a component is active.
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'wordpoints_component_active' with the boolean
	 *       activity indicator and the component $slug.
	 *
	 * @param string $slug The component's slug.
	 *
	 * @return bool Whether the component is active.
	 */
	public function is_active( $slug ) {

		$this->_reset_active();

		$is_active = isset( $this->active[ $slug ] );

		/**
		 * Is a component active?
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $is_active Whether the component is currently active.
		 * @param string $slug      The component's slug.
		 */
		return apply_filters( 'wordpoints_component_active', $is_active, $slug );
	}
}

// end of file /includes/class-wordpoints-components.php
