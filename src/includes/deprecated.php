<?php

/**
 * Deprecated functions and classes.
 *
 * These functions should not be used, and may be removed in future versions of the
 * plugin.
 *
 * @package WordPoints
 * @since 1.1.0
 */

/**
 * Register a module.
 *
 * @since 1.0.0
 * @deprecated 1.1.0
 * @deprecated Use the new modules API instead.
 *
 * @see http://wordpoints.org/developer-guide/update-module-api-1-1-0/
 *
 * @param array $args The module arguments.
 *
 * @return bool Whether the module was registered. Now always false.
 */
function wordpoints_register_module( $args ) {

	_deprecated_function( 'wordpoints_register_module', '1.1.0', 'is_wordpoints_module_active' );
	return false;
}

/**
 * Module activation check wrapper.
 *
 * @since 1.0.0
 * @deprecated 1.1.0
 * @deprecated Upgrade to the new modules API, or use is_wordpoints_module_active().
 *
 * @see http://wordpoints.org/developer-guide/update-module-api-1-1-0/
 *
 * @param string $slug The component slug.
 *
 * @return bool Whether the module is active.
 */
function wordpoints_module_is_active( $slug ) {

	_deprecated_function( 'wordpoints_module_is_active', '1.1.0', 'is_wordpoints_module_active' );
	return true;
}


/**
 * Module handler class.
 *
 * This class handles module related actions, including activating and deactivating.
 * It is a singleton, with only one instance which must be accessed through the
 * instance() method.
 *
 * @since 1.0.0
 * @deprecated 1.1.0
 * @deprecated Use the new modules API instead.
 */
final class WordPoints_Modules {

	//
	// Private Vars.
	//

	/**
	 * The single instance.
	 *
	 * @since 1.0.0
	 *
	 * @type WordPoints_Modules $instance
	 */
	private static $instance;

	//
	// Private Methods.
	//

	/**
	 * Inconstructable.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

	/**
	 * After they made me, they broke the mold.
	 *
	 * @since 1.0.0
	 */
	private function __clone() {}

	//
	// Public Methods.
	//

	/**#@+
	 * @deprecated 1.1.0
	 * @deprecated Use the new modules API.
	 */

	/**
	 * Set up the class.
	 *
	 * You should not call this method directly.
	 *
	 * @since 1.0.0
	 *
	 */
	public static function set_up() {

		_deprecated_function( __METHOD__, '1.1.0', __( 'new modules API' ) );
	}

	/**
	 * Return an instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return WordPoints_Modules
	 */
	public static function instance() {

		_deprecated_function( __METHOD__, '1.1.0', __( 'new modules API' ) );

		if ( ! isset( self::$instnace ) ) {
			self::$instance = new WordPoints_Modules();
		}

		return self::$instance;
	}

	/**
	 * Get all registered modules.
	 *
	 * @since 1.0.0
	 * @deprecated Use wordpoints_get_modules() instead.
	 *
	 * @return array If the modules haven't been registered yet, it will be empty.
	 */
	public function get() {

		_deprecated_function( __METHOD__, '1.1.0', 'wordpoints_get_modules()' );
		return wordpoints_get_modules();
	}

	/**
	 * Get all active modules.
	 *
	 * @since 1.0.0
	 * @deprecated Use get_option( 'wordpoints_active_modules' ) instead.
	 *
	 * @return array
	 */
	public function get_active() {

		_deprecated_function( __METHOD__, '1.1.0', 'get_option( \'wordpoints_active_modules\' )' );
		return wordpoints_get_array_option( 'wordpoints_active_modules' );
	}

	/**
	 * Get the path to the modules directory.
	 *
	 * @since 1.0.0
	 * @deprecated Use wordpoints_modules_dir() instead.
	 *
	 * @return string The path to the modules directory (with trailing slash).
	 */
	public function get_dir() {

		_deprecated_function( __METHOD__, '1.1.0', 'wordpoints_modules_dir()' );
		return wordpoints_modules_dir();
	}

	/**
	 * Include all modules in the modules directory.
	 *
	 * @since 1.0.0
	 * @deprecated Use wordpoints_load_modules() instead.
	 */
	public function load() {

		_deprecated_function( __METHOD__, '1.1.0', 'wordpoints_load_modules()' );
	}

	/**
	 * Check if a module is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The module's slug.
	 *
	 * @return bool True if the module is registered.
	 */
	public function is_registered( $slug ) {

		_deprecated_function( __METHOD__, '1.1.0' );
		return false;
	}

	/**
	 * Register a module.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the module was registered successfully. Always false.
	 */
	public function register( $args ) {

		_deprecated_function( __METHOD__, '1.1.0',  __( 'new modules API' ) );
		return false;
	}

	/**
	 * Deregister a module.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The module's slug.
	 *
	 * @return bool True if the module isn't registered.
	 */
	public function deregister( $slug ) {

		_deprecated_function( __METHOD__, '1.1.0' );
		return false;
	}

	/**
	 * Check if a module is activated.
	 *
	 * @since 1.0.0
	 * @deprecated Use is_wordpoints_module_active() instead.
	 *
	 * @param string $slug The module's slug.
	 *
	 * @return bool True if the module is activated, otherwise, false.
	 */
	public function is_active( $slug ) {

		_deprecated_function( __METHOD__, '1.1.0', 'is_wordpoints_module_active()' );
		return true;
	}

	/**
	 * Activate a module.
	 *
	 * @since 1.0.0
	 * @deprecated Use wordpoints_activate_module() instead.
	 *
	 * @param string $slug The module's slug.
	 *
	 * @return bool
	 */
	public function activate( $slug ) {

		_deprecated_function( __METHOD__, '1.1.0', 'wordpoints_activate_module()' );
		return false;
	}

	/**
	 * Deactivate a module.
	 *
	 * The returned value indicates whether the module is deactivated. It does not
	 * necessarily mean that it was just deactivated. Note that if the module isn't
	 * registered, true will be returned.
	 *
	 * @since 1.0.0
	 * @deprecated Use wordpoints_deactivate_modules() instead.
	 *
	 * @param string $slug The module's slug.
	 *
	 * @return bool Always true.
	 */
	public function deactivate( $slug ) {

		_deprecated_function( __METHOD__, '1.1.0', 'wordpoints_deactivate_modules()' );
		return true;
	}

	/**#@-*/
}

/**
 * Add module related capabilities.
 *
 * Filters a user's capabilities, e.g., when current_user_can() is called. Adds the
 * pseudo-capability 'manage_ntework_wordpoints_modules' which can be checked for as
 * with any other capability:
 *
 * current_user_can( 'manage_ntework_wordpoints_modules' );
 *
 * Override this by adding your own filter with a lower priority (e.g. 15), and
 * manipulating the $all_capabilities array.
 *
 * @since 1.1.0
 * @deprecated 1.3.0
 *
 * @filter user_has_cap Not any more.
 *
 * @see http://codex.wordpress.org/Plugin_API/Filter_Reference/user_has_cap
 *
 * @param array $all_capabilities All of the capabilities of a user.
 *
 * @return array All of the users capabilities.
 */
function wordpoints_modules_user_cap_filter( $all_capabilities ) {

	return $all_capabilities;
}

/**
 * Fix URLs where WordPress doesn't follow symlinks.
 *
 * This allows you to define WORDPOINTS_SYMLINK in wp-config.php and have the plugin
 * symlinked to the plugins directory of your install.
 *
 * @deprecated 1.4.0
 *
 * @filter plugins_url
 */
function wordpoints_symlink_fix( $url, $path, $plugin ) {

	if ( strstr( $plugin, 'wordpoints' ) ) {

		$url = str_replace( WORDPOINTS_SYMLINK, 'wordpoints', $url );
	}

	return $url;
}

if ( defined( 'WORDPOINTS_SYMLINK' ) ) {

	_deprecated_function( 'wordpoints_symlink_fix', '1.4.0' );
	add_filter( 'plugins_url', 'wordpoints_symlink_fix', 10, 3 );
}
