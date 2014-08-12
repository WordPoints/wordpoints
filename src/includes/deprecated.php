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

/**
 * Include once all .php files in a directory and subdirectories.
 *
 * Gets the paths of all files in $dir and in any subdirectories of $dir. Paths of
 * files in subdirectories are filtered out unless the filename matches the name of
 * the subdirectory.
 *
 * Used to include modules and components.
 *
 * @since 1.0.0
 * @deprecated 1.5.0
 *
 * @uses trailingslashit() To ensure $dir has a trailing slash.
 *
 * @param string $dir The directory to include the files from.
 */
function wordpoints_dir_include( $dir ) {

	_deprecated_function( 'wordpoints_dir_include', '1.5.0' );

	$dir = trailingslashit( $dir );

	foreach ( glob( $dir . '*.php' ) as $file ) {

		include_once $file;
	}

	foreach ( glob( $dir . '*/*.php' ) as $file ) {

		if ( preg_match( '~/([^/]+)/\1.php$~', $file ) ) {
			include_once $file;
		}
	}
}

/**
 * Check if debugging is on.
 *
 * @since 1.0.0
 * @deprecated 1.5.0
 * @deprecated Use WP_DEBUG instead.
 *
 * @return bool
 */
function wordpoints_debug() {

	_deprecated_function( 'wordpoints_debug', '1.5.0', 'WP_DEBUG' );

	if ( defined( 'WORDPOINTS_DEBUG' ) ) {

		$debug = WORDPOINTS_DEBUG;

	} else {

		$debug = WP_DEBUG;
	}

	return $debug;
}

if ( defined( 'WORDPOINTS_DEBUG' ) ) {
	_doing_it_wrong( 'wordpoints_debug', 'The WORDPOINTS_DEBUG constant is deprecated', '1.5.0' );
}

/**
 * Issue a debug message that may be logged or displayed.
 *
 * We call do_action( 'wordpoints_debug_message' ) so folks can generate a stack
 * trace if they want.
 *
 * @since 1.0.0
 * @deprecated 1.5.0
 * @deprecated Use _doing_it_wrong() insstead.
 *
 * @param string $message  The message.
 * @param string $function The function in which the message was issued.
 * @param string $file     The file in which the message was issued.
 * @param int    $line     The line on which the message was issued.
 */
function wordpoints_debug_message( $message, $function, $file, $line ) {

	_deprecated_function( 'wordpoints_debug_message', '1.5.0', '_doing_it_wrong()' );

	_doing_it_wrong( $function, "WordPoints Debug Error: {$message}" );
}

/**
 * Enqueue scripts for datatables.
 *
 * It is recommended that you use this function rather than calling the enqueue
 * functions directly, for forward compatibility.
 *
 * @since 1.0.0
 * @since 1.0.1 'oLanguage' datatables argument may now be overridden.
 * @deprecated 1.6.0 No longer used.
 *
 * @param string $for  The selector for the the HTML elements to apply the JS to.
 * @param array  $args Arguments for the datatables constructor.
 */
function wordpoints_enqueue_datatables( $for = null, array $args = array() ) {

	global $wp_locale;

	_deprecated_function( __FUNCTION__, '1.6.0' );

	wp_enqueue_style( 'wordpoints-datatables' );
	wp_enqueue_script( 'wordpoints-datatables' );
	wp_enqueue_script( 'wordpoints-datatables-init' );

	if ( $for ) {

		if ( ! $args ) {

			$args = array(
				'sPaginationType' => 'full_numbers',
				'bStateSave'      => false,
				'bSort'           => false,
				'aoColumns'       => array(
					array(),
					array(),
					array(),
					array( 'bSearchable' => false ),
				),
			);
		}

		$lang_defaults = array(
			'sEmptyTable'     => _x( 'No data available in table', 'datatable', 'wordpoints' ),
			/* translators: _START_, _END_, and _TOTAL_ will be replaced with the correct values. */
			'sInfo'           => _x( 'Showing _START_ to _END_ of _TOTAL_ entries', 'datatable', 'wordpoints' ),
			'sInfoEmpty'      => _x( 'Showing 0 to 0 of 0 entries', 'datatable', 'wordpoints' ),
			/* translators: _MAX_ will be replaced with the total. */
			'sInfoFiltered'   => _x( '(filtered from _MAX_ total entries)', 'datatable', 'wordpoints' ),
			'sInfoPostFix'    => '',
			'sInfoThousands'  => $wp_locale->number_format['thousands_sep'],
			/* translators: _MENU_ will be replaced with a dropdown menu. */
			'sLengthMenu'     => _x( 'Show _MENU_ entries', 'datatable', 'wordpoints' ),
			'sLoadingRecords' => _x( 'Loading...', 'datatable', 'wordpoints' ),
			'sProcessing'     => _x( 'Processing...', 'datatable', 'wordpoints' ),
			'sSearch'         => _x( 'Search:', 'datatable', 'wordpoints' ),
			'sZeroRecords'    => _x( 'No matching records found', 'datatable', 'wordpoints' ),
			'oPaginate' => array(
				'sFirst'    => _x( 'First', 'datatable', 'wordpoints' ),
				'sLast'     => _x( 'Last', 'datatable', 'wordpoints' ),
				'sNext'     => _x( 'Next', 'datatable', 'wordpoints' ),
				'sPrevious' => _x( 'Previous', 'datatable', 'wordpoints' ),
			),
		);

		if ( isset( $args['oLanguage'] ) ) {

			$args['oLanguage'] = array_merge( $lang_defaults, $args['oLanguage'] );
			$args['oLanguage']['oPaginate'] = array_merge( $lang_defaults['oPaginate'], $args['oLanguage']['oPaginate'] );

		} else {

			$args['oLanguage'] = $lang_defaults;
		}

		wp_localize_script( 'wordpoints-datatables-init', 'WordPointsDataTable', array( 'selector' => $for, 'args' => $args ) );
	}
}

// EOF
