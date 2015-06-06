<?php

/**
 * Modules related functions.
 *
 * This class loads, registers, activates and deactivates modules.
 *
 * @package WordPoints\Modules
 * @since 1.0.0
 */

/**
 * Checks if a module is active.
 *
 * @since 1.1.0
 *
 * @param string $module The module file.
 *
 * @return bool Whether the module is active.
 */
function is_wordpoints_module_active( $module ) {

	return (
		in_array( $module, wordpoints_get_array_option( 'wordpoints_active_modules' ) )
		|| is_wordpoints_module_active_for_network( $module )
	);
}

/**
 * Check if a module is active for the entire network.
 *
 * @since 1.1.0
 *
 * @param string $module The module to check the activity status of.
 *
 * @return bool Whether the module is network active.
 */
function is_wordpoints_module_active_for_network( $module ) {

	if ( ! is_multisite() || ! is_plugin_active_for_network( plugin_basename( WORDPOINTS_DIR . 'wordpoints.php' ) ) ) {
		return false;
	}

	$modules = wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' );

	if ( isset( $modules[ $module ] ) ) {
		return true;
	}

	return false;
}

/**
 * Check whether a module is only to be network activated.
 *
 * Checks for "Network: true" in the module header to see if it should be activated
 * only as a network-wide module. (The module would also work when Multisite is not
 * enabled.)
 *
 * Note that passing an invalid path will result in errors.
 *
 * @since 1.1.0
 *
 * @param string $module Basename path of the module to check.
 *
 * @return bool True if the module is network only, false otherwise.
 */
function is_network_only_wordpoints_module( $module ) {

	$module_data = wordpoints_get_module_data( wordpoints_modules_dir() . '/' . $module );

	return $module_data['network'];
}

/**
 * Check if a module has an uninstall file.
 *
 * @since 1.1.0
 *
 * @param string $module The module to check.
 *
 * @return bool True if the module has an uninstall script, false otherwise.
 */
function is_uninstallable_wordpoints_module( $module ) {

	return ( file_exists( wordpoints_modules_dir() . '/' . dirname( wordpoints_module_basename( $module ) ) . '/uninstall.php' ) );
}

/**
 * Store and resolve module paths.
 *
 * Used to make symlinked modules work correctly.
 *
 * @since 1.6.0
 */
final class WordPoints_Module_Paths {

	/**
	 * The path to the modules directory.
	 *
	 * @since 1.6.0
	 *
	 * @var string $modules_dir
	 */
	private static $modules_dir;

	/**
	 * The registered module paths.
	 *
	 * Each element has the following keys:
	 * - 'module_path' The path to the module directory.
	 * - 'module_realpath' The path to the module directory, with symlinks resolved.
	 * - 'realpath_length' The string length of the realpath.
	 *
	 * @since 1.6.0
	 *
	 * @var array $paths
	 */
	private static $paths = array();

	/**
	 * Whether the paths have been sorted.
	 *
	 * Saves us from sorting them multiple times.
	 *
	 * @since 1.6.0
	 *
	 * @var bool $paths_sorted
	 */
	private static $paths_sorted = false;

	/**
	 * Register a module's real path.
	 *
	 * The real path is used to resolve symlinked modules.
	 *
	 * Single-file modules symlinks aren't resolved, because they don't include any
	 * assets, so they have no need to get the URL relative to themselves.
	 *
	 * @since 1.6.0
	 *
	 * @param string $file The known path to the module's main file.
	 *
	 * @return bool Whether the path was able to be registered.
	 */
	public static function register( $file ) {

		$file = wp_normalize_path( $file );

		// We store this so that we don't have to keep normalizing a constant value.
		if ( ! isset( self::$modules_dir ) ) {
			self::$modules_dir = wp_normalize_path( wordpoints_modules_dir() );
		}

		$module_path = wp_normalize_path( dirname( $file ) );

		// It was a single-file module.
		if ( $module_path . '/' === self::$modules_dir ) {
			return false;
		}

		$module_realpath = wp_normalize_path( dirname( realpath( $file ) ) );

		if ( $module_path !== $module_realpath ) {

			$realpath_length = strlen( $module_realpath );

			// Use unique keys, but still easy to sort by realpath length.
			self::$paths[ $realpath_length . '-' . $module_path ] = array(
				'module_path'     => $module_path,
				'module_realpath' => $module_realpath,
				'realpath_length' => $realpath_length,
			);

			self::$paths_sorted = false;
		}

		return true;
	}

	/**
	 * Reverse resolve a module symlink path from the realpath.
	 *
	 * @since 1.6.0
	 *
	 * @param string $file The real path of the main module file.
	 *
	 * @return string The path to the symlink in the modules directory.
	 */
	public static function resolve( $file ) {

		$file = wp_normalize_path( $file );

		// Sort the paths by the realpath length, see https://core.trac.wordpress.org/ticket/28441.
		if ( ! self::$paths_sorted ) {
			krsort( self::$paths );
			self::$paths_sorted = true;
		}

		foreach ( self::$paths as $path ) {
			if ( 0 === strpos( $file, $path['module_realpath'] ) ) {
				$file = $path['module_path'] . substr( $file, $path['realpath_length'] );
			}
		}

		return $file;
	}
}

/**
 * Get the path to the modules directory.
 *
 * The default is /wp-content/wordpoints-modules/. To override this, define the
 * WORDPOINTS_MODULES_DIR constant in wp-config.php like this:
 *
 * define( 'WORDPOINTS_MODULES_DIR', '/my/custom/path/' );
 *
 * The value may also be filtered with the 'wordpoints_modules_dir' filter.
 *
 * Note that the value is stored in a static variable, and so is only calculated
 * once, meaning that it will not change within a single page load.
 *
 * @since 1.1.0
 *
 * @return string The full module folder path.
 */
function wordpoints_modules_dir() {

	static $modules_dir;

	if ( ! $modules_dir ) {

		if ( defined( 'WORDPOINTS_MODULES_DIR' ) ) {

			$modules_dir = trailingslashit( WORDPOINTS_MODULES_DIR );

		} else {

			$modules_dir = WP_CONTENT_DIR . '/wordpoints-modules/';
		}

		/**
		 * Filter the path to the modules directory.
		 *
		 * Note that the value is stored in a static variable, and so is only
		 * calculated once, meaning that it wont change during a single page load.
		 *
		 * @since 1.1.0
		 *
		 * @param string $module_dir The full path to the modules folder.
		 */
		$modules_dir = apply_filters( 'wordpoints_modules_dir', $modules_dir );
	}

	return $modules_dir;
}

/**
 * Get the URL for the modules directory or to a specific file in that directory.
 *
 * @since 1.4.0
 *
 * @param string $path   A relative path to a file or folder.
 * @param string $module A module file that the $path should be relative to.
 *
 * @return string The URL for the path passed.
 */
function wordpoints_modules_url( $path = '', $module = '' ) {

	$path   = wp_normalize_path( $path );
	$module = wp_normalize_path( $module );

	if ( defined( 'WORDPOINTS_MODULES_URL' ) ) {
		$url = WORDPOINTS_MODULES_URL;
	} else {
		$url = WP_CONTENT_URL . '/wordpoints-modules';
	}

	$url = set_url_scheme( $url );

	if ( ! empty( $module ) && is_string( $module ) ) {

		$folder = dirname( wordpoints_module_basename( $module ) );

		if ( '.' !== $folder ) {
			$url .= '/' . ltrim( $folder, '/' );
		}
	}

	if ( $path && is_string( $path ) ) {
		$url .= '/' . ltrim( $path, '/' );
	}

	/**
	 * Filter the URL of the modules directory.
	 *
	 * @since 1.4.0
	 *
	 * @param string $url The URL of the modules folder.
	 * @param string $path   A relative path to a file or folder.
	 * @param string $module A module file that the $path should be relative to.
	 */
	return apply_filters( 'wordpoints_modules_url', $url, $path, $module );
}

/**
 * Get the basename of a module.
 *
 * @since 1.1.0
 *
 * @param string $file The path to a module file.
 *
 * @return string The name of the module file.
 */
function wordpoints_module_basename( $file ) {

	// Sanitize, and resolve possible symlink path from what is likely a real path.
	$file = WordPoints_Module_Paths::resolve( $file );

	// Sanitize for Win32 installs and remove any duplicate slashes.
	$modules_dir = wp_normalize_path( wordpoints_modules_dir() );

	// Get the relative path from the modules directory, and trim off the slashes.
	$file = preg_replace( '#^' . preg_quote( $modules_dir, '#' ) . '#', '', $file );
	$file = trim( $file, '/' );

	return $file;
}

/**
 * Parse the module contents to retrieve module's metadata.
 *
 * Module metadata headers are essentially the same as WordPress plugin headers. The
 * main difference is that the module name is "Module Name:" instead of "Plugin
 * Name:".
 *
 * @since 1.1.0
 * @since 1.6.0 The 'update_api' and 'ID' headers are now supported.
 * @since 1.10.0 The 'update_api' header is deprecated in favor of 'channel'.
 *
 * @param string $module_file The file to parse for the headers.
 * @param bool   $markup      Whether to mark up the module data for display (default).
 * @param bool   $translate   Whether to translate the module data. Default is true.
 *
 * @return array {
 *         The module header data.
 *
 *         @type string $name        The Module Name.
 *         @type string $title       The module's title. May be a link if $markup is true.
 *         @type string $module_uri  The URI of the module's home page.
 *         @type string $version     The module's version number.
 *         @type string $description A description of the module.
 *         @type string $author      The module's author. May be a link if $markup is true.
 *         @type string $author      The module author's name.
 *         @type string $author_uri  The URI of the module author's home page.
 *         @type string $text_domain The module's text domain.
 *         @type string $domain_path The folder containing the module's *.mo translation files.
 *         @type bool   $network     Whether the module should only be network activated.
 *         @type string $channel     The URL of the update service to be used for this module.
 *         @type mixed  $ID          A unique identifier for this module, used by the update service.
 * }
 */
function wordpoints_get_module_data( $module_file, $markup = true, $translate = true ) {

	$default_headers = array(
		'name'        => 'Module Name',
		'module_uri'  => 'Module URI',
		'version'     => 'Version',
		'description' => 'Description',
		'author'      => 'Author',
		'author_uri'  => 'Author URI',
		'text_domain' => 'Text Domain',
		'domain_path' => 'Domain Path',
		'network'     => 'Network',
		'update_api'  => 'Update API',
		'channel'     => 'Channel',
		'ID'          => 'ID',
	);

	$module_data = WordPoints_Modules::get_data( $module_file );

	if ( $module_data && wp_normalize_path( $module_file ) === $module_data['raw_file'] ) {
		unset( $module_data['raw'], $module_data['raw_file'] );
	} else {
		$module_data = get_file_data( $module_file, $default_headers, 'wordpoints_module' );
	}

	if ( ! empty( $module_data['update_api'] ) ) {
		_deprecated_argument( __FUNCTION__, '1.10.0', 'The "Update API" module header has been deprecated in favor of "Channel".' );
	}

	$module_data['network'] = ( 'true' === strtolower( $module_data['network'] ) );

	if ( $markup || $translate ) {

		// Sanitize the plugin filename to a WP_module_DIR relative path
		$module_file = wordpoints_module_basename( $module_file );

		// Translate fields
		if ( $translate ) {

			$textdomain = $module_data['text_domain'];

			if ( $textdomain ) {

				if ( $module_data['domain_path'] ) {
					wordpoints_load_module_textdomain( $textdomain, dirname( $module_file ) . $module_data['domain_path'] );
				} else {
					wordpoints_load_module_textdomain( $textdomain, dirname( $module_file ) );
				}

				foreach ( array( 'name', 'module_uri', 'description', 'author', 'author_uri', 'version' ) as $field ) {

					$module_data[ $field ] = translate( $module_data[ $field ], $textdomain );
				}
			}
		}

		// Sanitize fields.
		$allowed_tags = $allowed_tags_in_links = array(
			'abbr'    => array( 'title' => true ),
			'acronym' => array( 'title' => true ),
			'code'    => true,
			'em'      => true,
			'strong'  => true,
		);
		$allowed_tags['a'] = array( 'href' => true, 'title' => true );

		// Name and author ar marked up inside <a> tags. Don't allow these.
		$module_data['name']   = wp_kses( $module_data['name'],   $allowed_tags_in_links );
		$module_data['author'] = wp_kses( $module_data['author'], $allowed_tags_in_links );

		$module_data['description'] = wp_kses( $module_data['description'], $allowed_tags );
		$module_data['version']     = wp_kses( $module_data['version'],     $allowed_tags );

		$module_data['module_uri'] = esc_url( $module_data['module_uri'] );
		$module_data['author_uri'] = esc_url( $module_data['author_uri'] );

		$module_data['title']       = $module_data['name'];
		$module_data['author_name'] = $module_data['author'];

		// Apply markup.
		if ( $markup ) {

			if ( $module_data['module_uri'] && $module_data['name'] ) {
				$module_data['title'] = '<a href="' . $module_data['module_uri']
					. '">' . $module_data['name'] . '</a>';
			}

			if ( $module_data['author_uri'] && $module_data['author'] ) {
				$module_data['author'] = '<a href="' . $module_data['author_uri']
					. '">' . $module_data['author'] . '</a>';
			}

			$module_data['description'] = wptexturize( $module_data['description'] );

			if ( $module_data['author'] ) {
				$module_data['description'] .= ' <cite>'
					. sprintf( __( 'By %s.', 'wordpoints' ), $module_data['author'] )
					. '</cite>';
			}
		}

	} else {

		$module_data['title']       = $module_data['name'];
		$module_data['author_name'] = $module_data['author'];
	}

	return $module_data;
}

/**
 * Load a module's text domain.
 *
 * @since 1.1.0
 *
 * @param string      $domain          The module's text domain.
 * @param string|bool $module_rel_path The module path relative to the modules
 *                                     directory where the .mo files are, or false.
 *
 * @return bool Whether the textdoamin was loaded successfully.
 */
function wordpoints_load_module_textdomain( $domain, $module_rel_path = false ) {

	$locale = get_locale();

	/**
	 * Filter a module's locale.
	 *
	 * @since 1.1.0
	 *
	 * @param string $locale The module's current locale.
	 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
	 */
	$locale = apply_filters( 'wordpoints_module_locale', $locale, $domain );

	if ( false !== $module_rel_path	) {

		$path = wordpoints_modules_dir() . '/' . trim( $module_rel_path, '/' );

	} else {

		$path = wordpoints_modules_dir();
	}

	// Load the textdomain according to the module first.
	$mofile = $domain . '-' . $locale . '.mo';

	if ( $loaded = load_textdomain( $domain, $path . '/'. $mofile ) ) {
		return $loaded;
	}

	// Otherwise, load from the languages directory.
	$mofile = WP_LANG_DIR . '/wordpoints-modules/' . $mofile;

	return load_textdomain( $domain, $mofile );
}

/**
 * Get a list of all main module files.
 *
 * The default usage retrieves a list of all module files in the /wp-content/wordpoints-modules
 * directory. To get only the modules in a specific subfolder of that directory, pass
 * the folder name as the first parameter.
 *
 * @since 1.1.0
 * @since 2.0.0 The $markup and $translate parameters were added.
 *
 * @param string $module_folder A specific subfolder of the modules directory to look
 *                              in. Default is empty (search in all folders).
 * @param bool   $markup        Whether to mark up the module data for display.
 * @param bool   $translate     Whether to translate the module data.
 *
 * @return array A list of the module files found (files with module headers).
 */
function wordpoints_get_modules( $module_folder = '', $markup = false, $translate = false ) {

	if ( ! $cache_modules = wp_cache_get( 'wordpoints_modules', 'wordpoints_modules' ) ) {
		$cache_modules = array();
	}

	if ( isset( $cache_modules[ $module_folder ] ) ) {
		return $cache_modules[ $module_folder ];
	}

	$modules     = array();
	$module_root = wordpoints_modules_dir();

	if ( ! empty( $module_folder ) ) {
		$module_root .= $module_folder;
	}

	// Escape pattern-matching characters in the path.
	$module_escape_root = str_replace( array( '*', '?', '[' ), array( '[*]', '[?]', '[[]' ), $module_root );

	// Get the top level files.
	$module_files = glob( "{$module_escape_root}/*.php" );

	if ( false === $module_files ) {
		return $modules;
	}

	// Get the files of subfolders, if not already searching in a subfolder.
	if ( empty( $module_folder ) ) {

		$subfolder_files = glob( "{$module_escape_root}/*/*.php" );

		if ( false === $subfolder_files ) {
			return $modules;
		}

		$module_files = array_merge( $module_files, $subfolder_files );
	}

	if ( empty( $module_files ) ) {
		return $modules;
	}

	foreach ( $module_files as $module_file ) {

		if ( ! is_readable( $module_file ) ) {
			continue;
		}

		$module_data = wordpoints_get_module_data( $module_file, $markup, $translate );

		if ( empty( $module_data['name'] ) ) {
			continue;
		}

		$module_file = wordpoints_module_basename( $module_file );

		if ( $module_folder ) {
			$module_file = basename( $module_file );
		}

		$modules[ $module_file ] = $module_data;
	}

	uasort( $modules, '_wordpoints_sort_uname_callback' );

	$cache_modules[ $module_folder ] = $modules;
	wp_cache_set( 'wordpoints_modules', $cache_modules, 'wordpoints_modules' );

	return $modules;
}

/**
 * Check that a module exists and has a valid header.
 *
 * @since 1.1.0
 *
 * @param string $module The module's main file.
 *
 * @return true|WP_Error True on success, a WP_Error on failure.
 */
function wordpoints_validate_module( $module ) {

	if ( validate_file( $module ) ) {
		return new WP_Error( 'module_invalid', __( 'Invalid module path.', 'wordpoints' ) );
	}

	if ( ! file_exists( wordpoints_modules_dir() . '/' . $module ) ) {
		return new WP_Error( 'module_not_found', __( 'Module file does not exist.', 'wordpoints' ) );
	}

	$installed_modules = wordpoints_get_modules();

	if ( ! isset( $installed_modules[ $module ] ) ) {
		return new WP_Error( 'no_module_header', __( 'The module does not have a valid header.', 'wordpoints' ) );
	}

	return true;
}

/**
 * Validate active modules.
 *
 * All active modules will be validated, and invalid ones will be deactivated.
 *
 * @since 1.1.0
 *
 * @return void|array Invalid modules, module as key, error as value.
 */
function wordpoints_validate_active_modules() {

	$modules = wordpoints_get_array_option( 'wordpoints_active_modules' );

	if ( is_multisite() && current_user_can( 'manage_network_wordpoints_modules' ) ) {

		$network_modules = wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' );
		$modules = array_merge( $modules, array_keys( $network_modules ) );
	}

	if ( empty( $modules ) ) {
		return;
	}

	$invalid = array();

	foreach ( $modules as $module ) {

		$result = wordpoints_validate_module( $module );

		if ( is_wp_error( $result ) ) {
			$invalid[ $module ] = $result;
			wordpoints_deactivate_modules( $module, true );
		}
	}

	return $invalid;
}

/**
 * Callback to sort an array by 'name' key.
 *
 * @since 1.1.0
 *
 * @param array $a One item.
 * @param array $b Another item.
 *
 * @return int {@see strnatcasecmp()}.
 */
function _wordpoints_sort_uname_callback( $a, $b ) {

	return strnatcasecmp( $a['name'], $b['name'] );
}

/**
 * Activate a module.
 *
 * A module that is already activated will not attempt to be activated again.
 *
 * The way it works is by setting the redirection to the error before trying to
 * include the module file. If the module fails, then the redirection will not be
 * overwritten with the success message. Also, the options will not be updated and
 * the activation hook will not be called on module error.
 *
 * It should be noted that in no way the below code will actually prevent errors
 * within the file. The code should not be used elsewhere to replicate the "sandbox",
 *  which uses redirection to work.
 *
 * If any errors are found or text is outputted, then it will be captured to ensure
 * that the success redirection will update the error redirection.
 *
 * @since 1.1.0
 *
 * @param string $module The basename path to the main file of the module to activate.
 * @param string $redirect The URL to redirect to on failure.
 * @param bool   $network_wide Whether to activate the module network wide. False by
 *                             default. Only applicable on multisite and when the
 *                             plugin is network activated.
 * @param bool   $silent       Whether to suppress the normal actions. False by default.
 *
 * @return WP_Error|void
 */
function wordpoints_activate_module( $module, $redirect = '', $network_wide = false, $silent = false ) {

	$module = wordpoints_module_basename( $module );

	if ( is_multisite() && ( $network_wide || is_network_only_wordpoints_module( $module ) ) ) {

		$network_wide = true;
		$current = array_keys( wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' ) );

	} else {

		$current = wordpoints_get_array_option( 'wordpoints_active_modules' );
	}

	$valid = wordpoints_validate_module( $module );

	if ( is_wp_error( $valid ) ) {
		return $valid;
	}

	// If the module is already active, return.
	if ( in_array( $module, $current ) ) {
		return;
	}

	if ( ! empty( $redirect ) ) {

		/*
		 * Redirect. We'll override this later if the module can be included
		 * without a fatal error.
		 */
		wp_safe_redirect(
			add_query_arg(
				'_error_nonce'
				, wp_create_nonce( 'module-activation-error_' . $module )
				, $redirect
			)
		);
	}

	ob_start();

	include_once wordpoints_modules_dir() . '/' . $module;

	if ( ! $silent ) {

		/**
		 * Fires before a module is activated.
		 *
		 * @since 1.1.0
		 *
		 * @param string $module       Base path to the main module file.
		 * @param bool   $network_wide Whether the module is being activated for
		 *                             all sites in the network or just the
		 *                             current site.
		 */
		do_action( 'wordpoints_module_activate', $module, $network_wide );

		/**
		 * Fires before a module is activated.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $network_wide Whether the module is being activated for
		 *                           all sites in the network or just the current
		 *                           site.
		 */
		do_action( "wordpoints_module_activate-{$module}", $network_wide );

		WordPoints_Installables::install(
			'module'
			, WordPoints_Modules::get_slug( $module )
			, $network_wide
		);
	}

	if ( $network_wide ) {

		$current[ $module ] = time();
		update_site_option( 'wordpoints_sitewide_active_modules', $current );

	} else {

		$current[] = $module;
		sort( $current );
		update_option( 'wordpoints_active_modules', $current );
	}

	if ( ! $silent ) {
		/**
		 * Fires after a module has been activated in activate_plugin() when the $silent parameter is false.
		 *
		 * @since 1.1.0
		 *
		 * @param string $module       Base path to main module file.
		 * @param bool   $network_wide Whether the module is being activated for
		 *                             all sites in the network or just the
		 *                             current site.
		 */
		do_action( 'wordpoints_activated_module', $module, $network_wide );
	}

	if ( ob_get_length() > 0 ) {

		return new WP_Error(
			'unexpected_output'
			, __( 'The module generated unexpected output.', 'wordpoints' )
			, ob_get_contents()
		);
	}

	ob_end_clean();
}

/**
 * Deactivate one or more modules.
 *
 * @since 1.1.0
 *
 * @param array|string $modules      The module(s) to deactivate.
 * @param bool         $silent       Whether to suppress deactivation actions.
 *                                   Default is false.
 * @param bool         $network_wide Whether to apply the change network wide.
 */
function wordpoints_deactivate_modules( $modules, $silent = false, $network_wide = null ) {

	if ( is_multisite() && is_plugin_active_for_network( plugin_basename( WORDPOINTS_DIR . 'wordpoints.php' ) ) ) {
		$network_current = wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' );
	}

	$current = wordpoints_get_array_option( 'wordpoints_active_modules' );
	$do_blog = $do_network = false;

	foreach ( (array) $modules as $module ) {

		$module = wordpoints_module_basename( $module );

		if ( ! is_wordpoints_module_active( $module ) ) {
			continue;
		}

		$network_deactivating = ( false !== $network_wide && is_wordpoints_module_active_for_network( $module ) );

		if ( ! $silent ) {
			/**
			 * Fires for each module being deactivated in wordpoints_deactivate_modules(), before deactivation
			 * and when the $silent parameter is false.
			 *
			 * @since 1.1.0
			 *
			 * @param string $module               Module path to main module file with module data.
			 * @param bool   $network_deactivating Whether the module is deactivated for all sites in the network
			 *                                     or just the current site. Multisite only. Default is false.
			 */
			do_action( 'wordpoints_deactivate_module', $module, $network_deactivating );
		}

		if ( false !== $network_wide ) {

			if ( is_wordpoints_module_active_for_network( $module ) ) {

				$do_network = true;
				unset( $network_current[ $module ] );

			} elseif ( $network_wide ) {

				continue;
			}
		}

		if ( true !== $network_wide ) {

			$key = array_search( $module, $current );

			if ( false !== $key ) {

				$do_blog = true;
				unset( $current[ $key ] );
			}
		}

		if ( ! $silent ) {
			/**
			 * Fires for each module being deactivated in wordpoints_deactivate_module(), after deactivation
			 * and when the $silent parameter is false.
			 *
			 * The action concatenates the 'deactivate_' prefix with the module's basename
			 * to create a dynamically-named action.
			 *
			 * @since 1.1.0
			 *
			 * @param bool $network_deactivating Whether the module is deactivated for all sites in the network
			 *                                   or just the current site. Multisite only. Default is false.
			 */
			do_action( 'wordpoints_deactivate_module-' . $module, $network_deactivating );

			/**
			 * Fires for each module being deactivated in deactivate_plugins(), after deactivation
			 * and when the $silent parameter is false.
			 *
			 * @since 1.1.0
			 *
			 * @param string $module               Module path to main module file with module data.
			 * @param bool   $network_deactivating Whether the module is deactivated for all sites in the network
			 *                                     or just the current site. Multisite only. Default is false.
			 */
			do_action( 'wordpoints_deactivated_module', $module, $network_deactivating );
		}
	}

	if ( $do_blog ) {
		update_option( 'wordpoints_active_modules', $current );
	}

	if ( $do_network ) {
		update_site_option( 'wordpoints_sitewide_active_modules', $network_current );
	}
}

/**
 * Remove directory and files of a module for a single or list of module(s).
 *
 * If the modules parameter list is empty, false will be returned. True when
 * completed.
 *
 * @since 1.1.0
 *
 * @param array $modules A list of modules to delete.
 *
 * @return bool|WP_Error True if all modules deleted successfully, false or WP_Error
 *                       on failure.
 */
function wordpoints_delete_modules( $modules ) {

	global $wp_filesystem;

	if ( empty( $modules ) ) {
		return false;
	}

	$checked = array();

	foreach ( $modules as $module ) {
		$checked[] = 'checked[]=' . $module;
	}

	ob_start();

	$url = wp_nonce_url( 'admin.php?page=wordpoints_modules&action=delete-selected&verify-delete=1&' . implode( '&', $checked ), 'bulk-modules' );

	if ( false === ($credentials = request_filesystem_credentials( $url )) ) {

		$data = ob_get_clean();

		if ( ! empty( $data ) ) {

			include_once ABSPATH . 'wp-admin/admin-header.php';
			echo $data; // XSS OK here, WPCS.
			include ABSPATH . 'wp-admin/admin-footer.php';
			exit;
		}

		return false;
	}

	if ( ! WP_Filesystem( $credentials ) ) {

		// Failed to connect, Error and request again
		request_filesystem_credentials( $url, '', true );

		$data = ob_get_clean();

		if ( ! empty( $data ) ) {

			include_once ABSPATH . 'wp-admin/admin-header.php';
			echo $data; // XSS OK here too, WPCS.
			include ABSPATH . 'wp-admin/admin-footer.php';
			exit;
		}

		return false;
	}

	if ( ! is_object( $wp_filesystem ) ) {
		return new WP_Error( 'fs_unavailable', __( 'Could not access filesystem.', 'wordpoints' ) );
	}

	if ( is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
		return new WP_Error( 'fs_error', __( 'Filesystem error.', 'wordpoints' ), $wp_filesystem->errors );
	}

	// Get the base module folder.
	$modules_dir = $wp_filesystem->find_folder( wordpoints_modules_dir() );

	if ( empty( $modules_dir ) ) {
		return new WP_Error( 'fs_no_modules_dir', __( 'Unable to locate WordPoints Module directory.', 'wordpoints' ) );
	}

	$modules_dir = trailingslashit( $modules_dir );
	$errors = array();

	foreach ( $modules as $module_file ) {

		// Run uninstall hook.
		if ( is_uninstallable_wordpoints_module( $module_file ) ) {
			wordpoints_uninstall_module( $module_file );
		}

		$this_module_dir = trailingslashit( dirname( $modules_dir . $module_file ) );

		// If module is in its own directory, recursively delete the directory.
		if ( strpos( $module_file, '/' ) && $this_module_dir !== $modules_dir ) {
			$deleted = $wp_filesystem->delete( $this_module_dir, true );
		} else {
			$deleted = $wp_filesystem->delete( $modules_dir . $module_file );
		}

		if ( ! $deleted ) {
			$errors[] = $module_file;
		}
	}

	/**
	 * Deleted modules.
	 *
	 * To get a list of the modules deleted successfully, do this:
	 *
	 *    $deleted = array_diff( $modules, $errors );
	 *
	 * @since 1.1.0
	 *
	 * @param array $modules The modules that were to be deleted.
	 * @param array $errors  The modules that failed to delete.
	 */
	do_action( 'wordpoints_deleted_modules', $modules, $errors );

	if ( ! empty( $errors ) ) {
		return new WP_Error(
			'could_not_remove_module'
			, sprintf(
				/* translators: A module or list of modules. */
				_n(
					'Could not fully remove the module %s.'
					, 'Could not fully remove the modules %s.'
					, count( $errors )
					, 'wordpoints'
				)
				, implode( ', ', $errors )
			)
		);
	}

	return true;
}

/**
 * Uninstall a single module.
 *
 * Includes the uninstall.php file, if it is available.
 *
 * @since 1.1.0
 *
 * @param string $module Relative module path from module directory.
 *
 * @return bool Whether the module had an uninstall process.
 */
function wordpoints_uninstall_module( $module ) {

	$file = wordpoints_module_basename( $module );
	$uninstall_file = wordpoints_modules_dir() . '/' . dirname( $file ) . '/uninstall.php';

	if ( file_exists( $uninstall_file ) ) {

		if ( ! defined( 'WORDPOINTS_UNINSTALL_MODULE' ) ) {
			/**
			 * Uninstalling a module.
			 *
			 * You should always check that this constant is set in your uninstall.php
			 * file, before running your uninstall process. And if you have an install
			 * process, you should have an uninstall process too.
			 *
			 * The value of this constant, when set, is boolean true.
			 *
			 * @since 1.1.0
			 *
			 * @const WORDPOINTS_UNINSTALL_MODULE
			 */
			define( 'WORDPOINTS_UNINSTALL_MODULE', true );
		}

		/**
		 * Uninstall base class.
		 *
		 * @since 1.8.0
		 */
		include_once WORDPOINTS_DIR . 'includes/class-un-installer-base.php';

		WordPoints_Module_Paths::register( $uninstall_file );
		include $uninstall_file;

		return true;

	} else {

		return WordPoints_Installables::uninstall(
			'module'
			, WordPoints_Modules::get_slug( $module )
		);
	}
}

/**
 * Load the active and valid modules.
 *
 * All modules active modules will be loaded by the function, provided that the
 * module file is a valid path that actually exists, and it has the '.php' extension.
 *
 * Network active modules are loaded along with regular modules. This only happens
 * when WordPoints is network activated on a multisite install.
 *
 * @since 1.1.0
 *
 * @action plugins_loaded 15 After components.
 *
 * @return void
 */
function wordpoints_load_modules() {

	$active_modules = wordpoints_get_array_option( 'wordpoints_active_modules' );

	if ( ! empty( $active_modules ) ) {

		$modules_dir = wordpoints_modules_dir();

		if ( is_multisite() && is_plugin_active_for_network( plugin_basename( WORDPOINTS_DIR . 'wordpoints.php' ) ) ) {

			$network_active_modules = array_keys(
				wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' )
			);

			// On the network admin screens we only load the sitewide active modules.
			if ( is_network_admin() ) {
				$active_modules = $network_active_modules;
			} else {
				$active_modules = array_merge( $active_modules, $network_active_modules );
			}
		}

		foreach ( $active_modules as $module ) {

			if (
				0 === validate_file( $module )
				&& '.php' === substr( $module, -4 )
				&& file_exists( $modules_dir . '/' . $module )
			) {
				WordPoints_Module_Paths::register( $modules_dir . '/' . $module );
				include $modules_dir . '/' . $module;
			}
		}
	}

	/**
	 * Fires after all active modules are loaded.
	 *
	 * It will always be fired even when no modules are active.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wordpoints_modules_loaded' );
}
add_action( 'plugins_loaded', 'wordpoints_load_modules', 15 );

/**
 * Register a function as the callback on module activation.
 *
 * @since 1.1.0
 *
 * @param string $file     The main file of the module the hook is for.
 * @param string $function The callback function.
 */
function wordpoints_register_module_activation_hook( $file, $function ) {

	$module_file = wordpoints_module_basename( $file );

	add_action( "wordpoints_module_activate-{$module_file}", $function );
}

/**
 * Register a function as the callback for module deactivation.
 *
 * @since 1.1.0
 *
 * @param string $file     The main file of the module the hook is for.
 * @param string $function The callback function.
 */
function wordpoints_register_module_deactivation_hook( $file, $function ) {

	$module_file = wordpoints_module_basename( $file );

	add_action( "wordpoints_deactivate_module-{$module_file}", $function );
}

// EOF
