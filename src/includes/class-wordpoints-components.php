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

	/**
	 * The installers for the components.
	 *
	 * @since 1.8.0
	 *
	 * @type array $installers
	 */
	private $installers;

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

		if ( isset( self::$instance ) ) {
			return;
		}

		self::$instance = new WordPoints_Components();

		add_action( 'plugins_loaded', array( self::$instance, 'load' ) );
		add_action( 'admin_notices', array( self::$instance, 'admin_notices' ) );
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
	 * @uses do_action() To call 'wordpoints_components_register'.
	 * @uses do_action() To call 'wordpoints_components_loaded'.
	 */
	public function load() {

		/**
		 * Registration of included components.
		 *
		 * This action is for the included components to hook into. It's not possible
		 * for components that are inside modules to use it, becuase they are
		 * loaded later. Just register your component on the modules registered
		 * action instead.
		 *
		 * @since 1.0.0
		 * @since 1.7.0 The components' code isn't loaded until after this hook.
		 */
		do_action( 'wordpoints_components_register' );

		foreach ( $this->get() as $component ) {

			// Back-compat < 1.7.0
			if ( ! isset( $component['file'] ) ) {
				continue;
			}

			if ( ! $this->is_active( $component['slug'] ) ) {
				continue;
			}

			include_once( $component['file'] );
		}

		/**
		 * Components loaded and registered.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wordpoints_components_loaded' );

		$this->maybe_do_updates();
	}

	/**
	 * Get all registered components.
	 *
	 * This function cannnot be called until after the {@see
	 * 'wordpoints_components_register'} hook.
	 *
	 * @since 1.0.0
	 *
	 * @return array|false The registered components. False if called too early.
	 */
	public function get() {

		if ( ! isset( $this->registered ) ) {
			return false;
		}

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

		$this->active = wordpoints_get_array_option( 'wordpoints_active_components', 'network' );
		return $this->active;
	}

	/**
	 * Check if a component is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The component's slug.
	 *
	 * @return bool True if the component is registered, otherwise false.
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
	 * @since 1.8.0 The un_installer argument added, uninstall_file was deprecated.
	 *
	 * @param array $args The component's data. {
	 *        @type string $slug          The component slug. Must be unique.
	 *        @type string $name          The name of the component.
	 *        @type string $author        The name of the component's author.
	 *        @type string $author_uri    The author's webpage.
	 *        @type string $component_uri The component's webpage.
	 *        @type string $description   A description of what the component does.
	 *        @type string $version       The component's version number.
	 *        @type string $file          The component's main file.
	 *        @type string $uninstall_file A file which will uninstall the component.
	 *        @type string $un_installer  A file that contains an un/installer class.
	 *                                    It should return the name of the class.
	 * }
	 *
	 * @return bool True, or false if the component's slug has already been registered.
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
			'file'          => null,
			'uninstall_file' => null,
			'un_installer'  => null,
		);

		$component = array_merge( $defaults, $args );

		$slug = $component['slug'];

		if ( $this->is_registered( $slug ) || empty( $component['name'] ) || empty( $slug ) ) {
			return false;
		}

		$this->registered[ $slug ] = array_intersect_key( $component, $defaults );

		if ( empty( $this->registered[ $slug ]['file'] ) ) {
			_doing_it_wrong( __METHOD__, 'Components should be registered with the "file" argument, no loaded unconditionally.', '1.7.0' );
		}

		if ( ! empty( $this->registered[ $slug ]['uninstall_file'] ) ) {
			_deprecated_argument( __METHOD__, '1.8.0', 'Use the "un_installer" argument and an un/installer class instead.' );
		}

		return true;
	}

	/**
	 * Deregister a component.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The component's slug.
	 *
	 * @return bool True, even if the component isn't registered.
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
	 * @return bool Whether the component was activated.
	 */
	public function activate( $slug ) {

		if ( ! $this->is_registered( $slug ) ) {
			return false;
		}

		// If this component isn't already active, activate it.
		if ( ! $this->is_active( $slug ) ) {

			$this->active[ $slug ] = 1;

			if ( ! wordpoints_update_network_option( 'wordpoints_active_components', $this->active ) ) {
				return false;
			}

			if ( isset( $this->registered[ $slug ]['file'] ) ) { // Back-compat < 1.7.0
				include_once( $this->registered[ $slug ]['file'] );
			}

			if ( isset( $this->registered[ $slug ]['un_installer'] ) ) { // Back-compat < 1.8.0

				$this->get_installer( $slug )->install(
					is_wordpoints_network_active()
				);
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
	 * @return bool Whether the component was deactivated.
	 */
	public function deactivate( $slug ) {

		if ( ! $this->is_registered( $slug ) ) {
			return false;
		}

		if ( $this->is_active( $slug ) ) {

			unset( $this->active[ $slug ] );

			if ( ! wordpoints_update_network_option( 'wordpoints_active_components', $this->active ) ) {
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

		$this->get_active();

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

	/**
	 * Uninstall a component.
	 *
	 * @since 1.7.0
	 *
	 * @param string $slug The component's slug.
	 */
	public function uninstall( $slug ) {

		if ( ! $this->is_registered( $slug ) ) {
			return;
		}

		/**
		 * Uninstall a component.
		 *
		 * @since 1.0.0
		 */
		do_action( "wordpoints_uninstall_component-{$slug}" );

		if ( isset( $this->registered[ $slug ]['un_installer'] ) ) { // Back-compat < 1.8.0

			$this->get_installer( $slug )->uninstall();

		} elseif ( isset( $this->registered[ $slug ]['uninstall_file'] ) ) { // Back-compat < 1.7.0

			include_once( $this->registered[ $slug ]['uninstall_file'] );
		}
	}

	/**
	 * Check if any of the active components has an update, and run it if so.
	 *
	 * @since 1.8.0
	 */
	public function maybe_do_updates() {

		$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );

		foreach ( array_keys( $this->get_active() ) as $component ) {

			if (
				! isset( $wordpoints_data['components'][ $component ]['version'] )
				|| ! isset( $this->registered[ $component ]['un_installer'] )
			) {
				continue;
			}

			$db_version = $wordpoints_data['components'][ $component ]['version'];

			// If the DB version isn't less than the code version, we don't need to upgrade.
			if ( version_compare( $db_version, WORDPOINTS_VERSION ) !== -1 ) {
				continue;
			}

			$this->get_installer( $component )->update( $db_version, WORDPOINTS_VERSION );

			$wordpoints_data['components'][ $component ]['version'] = WORDPOINTS_VERSION;
		}

		if ( isset( $db_version ) ) {
			wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
		}
	}

	/**
	 * Show the admin a notice if the update/install for a component was skipped.
	 *
	 * @since 1.8.0
	 *
	 * @WordPoints\action admin_notices Added by self::set_up().
	 */
	public function admin_notices() {

		if ( ! current_user_can( 'manage_network_plugins' ) ) {
			return;
		}

		foreach ( array_keys( $this->get_active() ) as $component ) {

			// Show a notice if we've skipped part of the install/update process.
			if ( get_site_option( "wordpoints_{$component}_network_install_skipped" ) ) {
				$message = esc_html( sprintf( __( 'WordPoints detected a large network and has skipped part of the installation process for the &#8220;%s&#8221; component.', 'wordpoints' ), $this->registered[ $component ]['name'] ) );
				$option  = "wordpoints_{$component}_network_install_skipped";
			} elseif ( get_site_option( "wordpoints_{$component}_network_update_skipped" ) ) {
				$message = esc_html( sprintf( __( 'WordPoints detected a large network and has skipped part of the update process for the &#8220;%s&#8221; component for version %s (and possibly later versions).', 'wordpoints' ), $this->registered[ $component ]['name'], get_site_option( 'wordpoints_network_update_skipped' ) ) );
				$option  = "wordpoints_{$component}_network_update_skipped";
			}

			if ( isset( $message ) ) {

				$message .= ' ' . esc_html__( 'The rest of the process needs to be completed manually. If this has not been done already, some parts of the component may not function properly.', 'wordpoints' );
				$message .= ' <a href="http://wordpoints.org/user-guide/multisite/" target="_blank">' . esc_html__( 'Learn more.', 'wordpoints' ) . '</a>';

				$args = array(
					'dismissable' => true,
					'option'      => $option,
				);

				wordpoints_show_admin_error( $message, $args );

				unset( $message );
			}
		}
	}

	/**
	 * Get the installer class for a component.
	 *
	 * @since 1.8.0
	 *
	 * @param string $slug The slug of the component to get the installer for.
	 *
	 * @return WordPoints_Un_Installer_Base|false The installer for the component.
	 */
	public function get_installer( $slug ) {

		if ( ! isset( $this->registered[ $slug ]['un_installer'] ) ) {
			return false;
		}

		if ( ! isset( $this->installers[ $slug ] ) ) {

			/**
			 * Uninstall base class.
			 *
			 * @since 1.8.0
			 */
			require_once( WORDPOINTS_DIR . '/includes/class-un-installer-base.php' );

			$this->installers[ $slug ] = require( $this->registered[ $slug ]['un_installer'] );
		}

		return new $this->installers[ $slug ];
	}
}

// EOF
