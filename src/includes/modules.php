<?php

/**
 * Extensions related functions.
 *
 * This class loads, registers, activates and deactivates extensions.
 *
 * The name of this file and many of the functions reflects the fact that extensions
 * used to be called "modules". Over time the term "module" will be completely
 * dropped from all APIs in favor of "extension", but it was decided that this would
 * be done partly as the APIs organically evolve, rather than renaming them all at
 * once.
 *
 * @package WordPoints\Extensions
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

	$is_active = (
		in_array(
			$module
			, wordpoints_get_array_option( 'wordpoints_active_modules' )
			, true
		)
		|| is_wordpoints_module_active_for_network( $module )
	);

	/**
	 * Filters whether a WordPoints extension is active.
	 *
	 * @since 2.4.0
	 *
	 * @param bool $is_active Whether the extension is active.
	 */
	return (bool) apply_filters( 'is_wordpoints_extension_active', $is_active );
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

	if ( ! is_wordpoints_network_active() ) {
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

	$module_data = wordpoints_get_module_data( wordpoints_extensions_dir() . '/' . $module );

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

	return ( file_exists( wordpoints_extensions_dir() . '/' . dirname( wordpoints_module_basename( $module ) ) . '/uninstall.php' ) );
}

/**
 * Get the path to the extensions directory.
 *
 * @since 1.1.0
 * @deprecated 2.4.0 Use wordpoints_extensions_dir() instead.
 *
 * @return string The full extension folder path.
 */
function wordpoints_modules_dir() {

	_deprecated_function( __FUNCTION__, '2.4.0', 'wordpoints_extensions_dir' );

	return wordpoints_extensions_dir();
}

/**
 * Get the path to the extensions directory.
 *
 * The default is /wp-content/wordpoints-extensions/. To override this, define the
 * WORDPOINTS_EXTENSIONS_DIR constant in wp-config.php like this:
 *
 * define( 'WORDPOINTS_EXTENSIONS_DIR', '/my/custom/path/' );
 *
 * The value may also be filtered with the 'wordpoints_extensions_dir' filter.
 *
 * @since 2.4.0
 *
 * @return string The full module folder path.
 */
function wordpoints_extensions_dir() {

	static $extensions_dir;

	if ( ! $extensions_dir ) {

		if ( defined( 'WORDPOINTS_EXTENSIONS_DIR' ) ) {

			$extensions_dir = trailingslashit( WORDPOINTS_EXTENSIONS_DIR );

		} elseif ( defined( 'WORDPOINTS_MODULES_DIR' ) ) {

			_deprecated_argument(
				__FUNCTION__
				, '2.4.0'
				, 'The WORDPOINTS_MODULES_DIR constant is deprecated in favor of WORDPOINTS_EXTENSIONS_DIR.'
			);

			$extensions_dir = trailingslashit( WORDPOINTS_MODULES_DIR );

		} else {

			$extensions_dir = WP_CONTENT_DIR . '/wordpoints-extensions/';
		}
	}

	/**
	 * Filter the path to the extensions directory.
	 *
	 * @since 1.1.0
	 * @since 2.2.0 The filter is no longer called only once per page load.
	 *
	 * @param string $extensions_dir The full path to the extensions folder.
	 */
	$dir = apply_filters_deprecated(
		'wordpoints_modules_dir'
		, array( $extensions_dir )
		, '2.4.0'
		, 'wordpoints_extensions_dir'
	);

	/**
	 * Filter the path to the extensions directory.
	 *
	 * @since 2.4.0
	 *
	 * @param string $extensions_dir The full path to the extensions folder.
	 */
	return apply_filters( 'wordpoints_extensions_dir', $dir );
}

/**
 * Replaces the new extensions directory path with the legacy path as needed.
 *
 * @since 2.4.0
 *
 * @WordPress\filter wordpoints_extensions_dir
 * @WordPress\filter wordpoints_extensions_url
 *
 * @param string $path The path.
 *
 * @return string The filtered path.
 */
function wordpoints_legacy_modules_path( $path ) {

	if ( is_wordpoints_network_active() ) {
		$wordpoints_data = get_site_option( 'wordpoints_data' );
	} else {
		$wordpoints_data = get_option( 'wordpoints_data' );
	}

	// If the legacy directory could not be moved, or we haven't done the update yet.
	if (
		get_site_option( 'wordpoints_legacy_extensions_dir' )
		|| (
			isset( $wordpoints_data['version'] )
			&& version_compare( $wordpoints_data['version'], '2.4.0-alpha-3', '<' )
		)
	) {
		$path = str_replace(
			'/wordpoints-extensions'
			, '/wordpoints-modules'
			, $path
		);
	}

	return $path;
}

/**
 * Get the URL for the extensions directory or to a specific file in that directory.
 *
 * @since 1.4.0
 * @deprecated 2.4.0 Use wordpoints_extensions_url() instead.
 *
 * @param string $path   A relative path to a file or folder.
 * @param string $module An extension file that the $path should be relative to.
 *
 * @return string The URL for the path passed.
 */
function wordpoints_modules_url( $path = '', $module = '' ) {

	_deprecated_function( __FUNCTION__, '2.4.0', 'wordpoints_extensions_url' );

	return wordpoints_extensions_url( $path, $module );
}

/**
 * Get the URL for the extensions directory or to a specific file in that directory.
 *
 * @since 2.4.0
 *
 * @param string $path      A relative path to a file or folder.
 * @param string $extension An extension file that the $path should be relative to.
 *
 * @return string The URL for the path passed.
 */
function wordpoints_extensions_url( $path = '', $extension = '' ) {

	$path      = wp_normalize_path( $path );
	$extension = wp_normalize_path( $extension );

	if ( defined( 'WORDPOINTS_EXTENSIONS_URL' ) ) {

		$url = WORDPOINTS_EXTENSIONS_URL;

	} elseif ( defined( 'WORDPOINTS_MODULES_URL' ) ) {

		$url = WORDPOINTS_MODULES_URL;

		_deprecated_argument(
			__FUNCTION__
			, '2.4.0'
			, 'The WORDPOINTS_MODULES_URL constant is deprecated in favor of WORDPOINTS_EXTENSIONS_URL.'
		);

	} else {

		$url = WP_CONTENT_URL . '/wordpoints-extensions';
	}

	$url = set_url_scheme( $url );

	if ( ! empty( $extension ) && is_string( $extension ) ) {

		$folder = dirname( wordpoints_module_basename( $extension ) );

		if ( '.' !== $folder ) {
			$url .= '/' . ltrim( $folder, '/' );
		}
	}

	if ( $path && is_string( $path ) ) {
		$url .= '/' . ltrim( $path, '/' );
	}

	/**
	 * Filters the URL of a file or folder in the extensions directory.
	 *
	 * @since 1.4.0
	 * @deprecated 2.4.0 Use 'wordpoints_extensions_url' instead.
	 *
	 * @param string $url       The URL of the file or folder.
	 * @param string $path      A relative path to a file or folder.
	 * @param string $extension An extension that the $path should be relative to.
	 */
	$url = apply_filters_deprecated(
		'wordpoints_modules_url'
		, array( $url, $path, $extension )
		, '2.4.0'
		, 'wordpoints_extensions_url'
	);

	/**
	 * Filters the URL of a file or folder in the extensions directory.
	 *
	 * @since 2.4.0
	 *
	 * @param string $url       The URL of the file or folder.
	 * @param string $path      A relative path to a file or folder.
	 * @param string $extension An extension that the $path should be relative to.
	 */
	return apply_filters( 'wordpoints_extensions_url', $url, $path, $extension );
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
	$modules_dir = wp_normalize_path( wordpoints_extensions_dir() );

	// Get the relative path from the modules directory, and trim off the slashes.
	$file = preg_replace( '#^' . preg_quote( $modules_dir, '#' ) . '#', '', $file );
	$file = trim( $file, '/' );

	return $file;
}

/**
 * Registers an extension.
 *
 * @since 2.4.0
 *
 * @param string $data The header data for the extension.
 * @param string $file The full path to the main file of the extension.
 *
 * @return bool True, or false if the extension has already been registered.
 */
function wordpoints_register_extension( $data, $file ) {
	return WordPoints_Modules::register( $data, $file );
}

/**
 * Gets the installed version of an extension.
 *
 * @since 2.4.0
 *
 * @param string $extension The full path to one of the extension's files.
 *
 * @return string|false The extension version, or false on failure.
 */
function wordpoints_get_extension_version( $extension ) {
	return WordPoints_Modules::get_data( $extension, 'version' );
}

/**
 * Parse the module contents to retrieve module's metadata.
 *
 * Module metadata headers are essentially the same as WordPress plugin headers. The
 * main difference is that the module name is "Extension Name:" instead of "Plugin
 * Name:".
 *
 * @since 1.1.0
 * @since 1.6.0 The 'update_api' and 'ID' headers are now supported.
 * @since 1.10.0 The 'update_api' header is deprecated in favor of 'channel'.
 * @since 2.2.0 The 'namespace' header is now supported.
 * @since 2.4.0 - The 'channel' header is deprecated in favor of 'server'.
 *              - The 'module_uri' header is deprecated in favor of 'uri'.
 *
 * @param string $module_file The file to parse for the headers.
 * @param bool   $markup      Whether to mark up the module data for display (default).
 * @param bool   $translate   Whether to translate the module data. Default is true.
 *
 * @return array {
 *         The module header data.
 *
 *         @type string $name        The Extension Name.
 *         @type string $title       The module's title. May be a link if $markup is true.
 *         @type string $uri         The URI of the extension's home page.
 *         @type string $version     The module's version number.
 *         @type string $description A description of the module.
 *         @type string $author      The module's author. May be a link if $markup is true.
 *         @type string $author      The module author's name.
 *         @type string $author_uri  The URI of the module author's home page.
 *         @type string $text_domain The module's text domain.
 *         @type string $domain_path The folder containing the module's *.mo translation files.
 *         @type bool   $network     Whether the module should only be network activated.
 *         @type string $server      The slug of the remote server for this module (like for updates, etc.).
 *         @type mixed  $ID          A unique identifier for this module, used by the update service.
 *         @type string $namespace   The namespace for this module. Should be Title_Case, and omit "WordPoints" prefix.
 * }
 */
function wordpoints_get_module_data( $module_file, $markup = true, $translate = true ) {

	$default_headers = array(
		'name'        => 'Extension Name',
		'uri'         => 'Extension URI',
		'module_name' => 'Module Name',
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
		'server'      => 'Server',
		'ID'          => 'ID',
		'namespace'   => 'Namespace',
	);

	$module_data = WordPoints_Modules::get_data( $module_file );

	if ( $module_data && wp_normalize_path( $module_file ) === $module_data['raw_file'] ) {
		unset( $module_data['raw'], $module_data['raw_file'] );
	} else {
		$module_data = get_file_data( $module_file, $default_headers, 'wordpoints_module' );
	}

	if ( ! empty( $module_data['update_api'] ) ) {
		_deprecated_argument( __FUNCTION__, '1.10.0', 'The "Update API" module header has been deprecated in favor of "Server".' );
	}

	if ( ! empty( $module_data['channel'] ) ) {
		_deprecated_argument( __FUNCTION__, '2.4.0', 'The "Channel" extension header has been deprecated in favor of "Server".' );
		$module_data['server'] = $module_data['channel'];
	}

	if ( ! empty( $module_data['module_name'] ) ) {
		_deprecated_argument( __FUNCTION__, '2.4.0', 'The "Module Name" extension header has been deprecated in favor of "Extension Name".' );
		$module_data['name'] = $module_data['module_name'];
	}

	unset( $module_data['module_name'] );

	if ( ! empty( $module_data['module_uri'] ) ) {
		_deprecated_argument( __FUNCTION__, '2.4.0', 'The "Module URI" extension header has been deprecated in favor of "Extension URI".' );
		$module_data['uri'] = $module_data['module_uri'];
	} else {
		$module_data['module_uri'] = $module_data['uri'];
	}

	$module_data['network'] = ( 'true' === strtolower( $module_data['network'] ) );

	/**
	 * Filters the header data for an extension.
	 *
	 * @since 2.4.0
	 *
	 * @param array  $extension_data The extension header data.
	 * @param string $extension_file The full path of the main extension file.
	 */
	$module_data = apply_filters( 'wordpoints_extension_data', $module_data, $module_file );

	if ( $markup || $translate ) {

		// Sanitize the plugin filename to a WP_module_DIR relative path
		$module_file = wordpoints_module_basename( $module_file );

		// Translate fields
		if ( $translate ) {

			$textdomain = $module_data['text_domain'];

			if ( $textdomain ) {

				if ( ! is_textdomain_loaded( $textdomain ) ) {

					$domain_path = dirname( $module_file );

					if ( $module_data['domain_path'] ) {
						$domain_path .= $module_data['domain_path'];
					}

					wordpoints_load_module_textdomain( $textdomain, $domain_path );
				}

				foreach ( array( 'name', 'uri', 'module_uri', 'description', 'author', 'author_uri', 'version' ) as $field ) {

					$module_data[ $field ] = translate( $module_data[ $field ], $textdomain ); // @codingStandardsIgnoreLine
				}
			}
		}

		// Sanitize fields.
		$allowed_tags_in_links = array(
			'abbr'    => array( 'title' => true ),
			'acronym' => array( 'title' => true ),
			'code'    => true,
			'em'      => true,
			'strong'  => true,
		);

		$allowed_tags      = $allowed_tags_in_links;
		$allowed_tags['a'] = array( 'href' => true, 'title' => true );

		// Name and author ar marked up inside <a> tags. Don't allow these.
		$module_data['name']   = wp_kses( $module_data['name']  , $allowed_tags_in_links );
		$module_data['author'] = wp_kses( $module_data['author'], $allowed_tags_in_links );

		$module_data['description'] = wp_kses( $module_data['description'], $allowed_tags );
		$module_data['version']     = wp_kses( $module_data['version']    , $allowed_tags );

		$module_data['uri']        = esc_url( $module_data['uri'] );
		$module_data['module_uri'] = esc_url( $module_data['module_uri'] );
		$module_data['author_uri'] = esc_url( $module_data['author_uri'] );

		$module_data['title']       = $module_data['name'];
		$module_data['author_name'] = $module_data['author'];

		// Apply markup.
		if ( $markup ) {

			if ( $module_data['uri'] && $module_data['name'] ) {
				$module_data['title'] = '<a href="' . $module_data['uri']
					. '">' . $module_data['name'] . '</a>';
			}

			if ( $module_data['author_uri'] && $module_data['author'] ) {
				$module_data['author'] = '<a href="' . $module_data['author_uri']
					. '">' . $module_data['author'] . '</a>';
			}

			$module_data['description'] = wptexturize( $module_data['description'] );

			if ( $module_data['author'] ) {
				$module_data['description'] .= ' <cite>'
					// translators: Author name.
					. sprintf( __( 'By %s.', 'wordpoints' ), $module_data['author'] )
					. '</cite>';
			}
		}

	} else {

		$module_data['title']       = $module_data['name'];
		$module_data['author_name'] = $module_data['author'];

	} // End if ( $markup || $translate ) else.

	return $module_data;
}

/**
 * Get the server for an extension.
 *
 * @since 2.4.0
 *
 * @param array $extension The extension to get the server for.
 *
 * @return WordPoints_Extension_ServerI|false The object for the server to use for
 *                                            this extension, or false.
 */
function wordpoints_get_server_for_extension( $extension ) {

	$server = false;

	if ( isset( $extension['server'] ) ) {
		$server = $extension['server'];
	}

	/**
	 * Filter the server to use for an extension.
	 *
	 * @since 1.0.0
	 *
	 * @param string|false $server    The slug of the server to use, or false for none.
	 * @param array        $extension The extension's header data.
	 */
	$server = apply_filters( 'wordpoints_server_for_extension', $server, $extension );

	if ( ! $server ) {
		return false;
	}

	$server = new WordPoints_Extension_Server( $server );

	/**
	 * Filter the server object to use for an extension.
	 *
	 * @since 1.0.0
	 *
	 * @param WordPoints_Extension_ServerI $server    The server object to use.
	 * @param array                        $extension The extension's header data.
	 */
	$server = apply_filters( 'wordpoints_server_object_for_extension', $server, $extension );

	if ( ! $server instanceof WordPoints_Extension_ServerI ) {
		return false;
	}

	return $server;
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

	if ( false !== $module_rel_path ) {

		$path = wordpoints_extensions_dir() . '/' . trim( $module_rel_path, '/' );

	} else {

		$path = wordpoints_extensions_dir();
	}

	// Load the textdomain according to the module first.
	$mofile = $domain . '-' . $locale . '.mo';

	if ( load_textdomain( $domain, $path . '/' . $mofile ) ) {
		return true;
	}

	// Otherwise, load from the languages directory.
	$mofile = WP_LANG_DIR . '/wordpoints-extensions/' . $mofile;

	return load_textdomain( $domain, $mofile );
}

/**
 * Get a list of all main module files.
 *
 * The default usage retrieves a list of all module files in the /wp-content/wordpoints-extensions
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

	$cache_modules = wp_cache_get( 'wordpoints_modules', 'wordpoints_modules' );

	if ( ! $cache_modules ) {
		$cache_modules = array();
	}

	if ( isset( $cache_modules[ $module_folder ] ) ) {
		return $cache_modules[ $module_folder ];
	}

	$modules     = array();
	$module_root = wordpoints_extensions_dir();

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
		return new WP_Error( 'module_invalid', __( 'Invalid extension path.', 'wordpoints' ) );
	}

	if ( ! file_exists( wordpoints_extensions_dir() . '/' . $module ) ) {
		return new WP_Error( 'module_not_found', __( 'Extension file does not exist.', 'wordpoints' ) );
	}

	$installed_modules = wordpoints_get_modules();

	if ( ! isset( $installed_modules[ $module ] ) ) {
		return new WP_Error( 'no_module_header', __( 'The extension does not have a valid header.', 'wordpoints' ) );
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
 * @return null|WP_Error[] Invalid modules, module as key, error as value.
 */
function wordpoints_validate_active_modules() {

	$modules = wordpoints_get_array_option( 'wordpoints_active_modules' );

	if ( is_multisite() && current_user_can( 'manage_network_wordpoints_extensions' ) ) {

		$network_modules = wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' );
		$modules         = array_merge( $modules, array_keys( $network_modules ) );
	}

	if ( empty( $modules ) ) {
		return null;
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
 * which uses redirection to work.
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
 * @return WP_Error|null An error object on failure, or null on success.
 */
function wordpoints_activate_module( $module, $redirect = '', $network_wide = false, $silent = false ) {

	$module = wordpoints_module_basename( $module );

	$valid = wordpoints_validate_module( $module );

	if ( is_wp_error( $valid ) ) {
		return $valid;
	}

	if ( is_network_only_wordpoints_module( $module ) ) {
		$network_wide = true;
	}

	if ( $network_wide && ! is_wordpoints_network_active() ) {
		$network_wide = false;
	}

	if ( $network_wide ) {

		$network_current = wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' );
		$current         = array_keys( $network_current );

	} else {

		$current = wordpoints_get_array_option( 'wordpoints_active_modules' );
	}

	// If the module is already active, return.
	if ( in_array( $module, $current, true ) ) {
		return null;
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

	$module_file = wordpoints_extensions_dir() . '/' . $module;
	WordPoints_Module_Paths::register( $module_file );

	require_once $module_file;

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

		WordPoints_Modules::install(
			WordPoints_Modules::get_slug( $module )
			, $network_wide
		);
	}

	if ( $network_wide ) {

		$network_current[ $module ] = time();
		update_site_option( 'wordpoints_sitewide_active_modules', $network_current );

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
			, __( 'The extension generated unexpected output.', 'wordpoints' )
			, ob_get_contents()
		);
	}

	ob_end_clean();

	return null;
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

	$network_current = array();
	if ( is_wordpoints_network_active() ) {
		$network_current = wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' );
	}

	$current = wordpoints_get_array_option( 'wordpoints_active_modules' );

	$do_network = false;
	$do_blog    = false;

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

			$key = array_search( $module, $current, true );

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
			do_action( "wordpoints_deactivate_module-{$module}", $network_deactivating );

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

	} // End foreach ( $modules ).

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

	$url = wp_nonce_url( self_admin_url( 'admin.php?page=wordpoints_extensions&action=delete-selected&verify-delete=1&' . implode( '&', $checked ) ), 'bulk-modules' );

	$credentials = request_filesystem_credentials( $url );

	if ( false === $credentials ) {

		$data = ob_get_clean();

		if ( ! empty( $data ) ) {

			require_once ABSPATH . 'wp-admin/admin-header.php';
			echo $data; // XSS OK here, WPCS.
			require ABSPATH . 'wp-admin/admin-footer.php';
			exit;
		}

		return false;
	}

	if ( ! WP_Filesystem( $credentials ) ) {

		// Failed to connect, Error and request again
		request_filesystem_credentials( $url, '', true );

		$data = ob_get_clean();

		if ( ! empty( $data ) ) {

			require_once ABSPATH . 'wp-admin/admin-header.php';
			echo $data; // XSS OK here too, WPCS.
			require ABSPATH . 'wp-admin/admin-footer.php';
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
	$modules_dir = $wp_filesystem->find_folder( wordpoints_extensions_dir() );

	if ( empty( $modules_dir ) ) {
		return new WP_Error( 'fs_no_modules_dir', __( 'Unable to locate WordPoints Extension directory.', 'wordpoints' ) );
	}

	$modules_dir = trailingslashit( $modules_dir );
	$errors      = array();

	foreach ( $modules as $module_file ) {

		$validate = wordpoints_validate_module( $module_file );

		if ( is_wp_error( $validate ) ) {
			$errors[] = $module_file;
			continue;
		}

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
				// translators: Extension or list of extensions.
				_n(
					'Could not fully remove the extension %s.'
					, 'Could not fully remove the extensions %s.'
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

	$file              = wordpoints_module_basename( $module );
	$module_file       = wordpoints_extensions_dir() . '/' . $file;
	$module_dir        = dirname( $module_file );
	$uninstall_file    = $module_dir . '/uninstall.php';
	$un_installer_file = $module_dir . '/includes/class-un-installer.php';
	$installable_file  = $module_dir . '/classes/installable.php';

	if ( file_exists( $installable_file ) ) {

		WordPoints_Class_Autoloader::register_dir( dirname( $installable_file ) );

		$slug = WordPoints_Modules::get_slug( $module );
		$data = wordpoints_get_module_data( $module_file, false, false );

		$class = "WordPoints_{$data['namespace']}_Installable";

		$uninstaller = new WordPoints_Uninstaller( new $class( $slug ) );
		$uninstaller->run();

		return true;

	} elseif ( file_exists( $uninstall_file ) ) {

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

		WordPoints_Module_Paths::register( $uninstall_file );
		include $uninstall_file;

		return true;

	} elseif ( file_exists( $un_installer_file ) ) {

		$slug = WordPoints_Modules::get_slug( $module );

		$uninstaller = WordPoints_Installables::get_installer(
			'module'
			, $slug
			, 'uninstall' // Required, but not really used.
			, $un_installer_file
		);

		$uninstaller->uninstall();

		return true;

	} else {

		return false;

	} // End if ( uninstall file ) elseif ( uninstaller ) else.
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
 * @WordPress\action plugins_loaded 15 After components.
 *
 * @return void
 */
function wordpoints_load_modules() {

	$active_modules = wordpoints_get_array_option( 'wordpoints_active_modules' );

	if ( is_wordpoints_network_active() ) {

		$network_active_modules = array_keys(
			wordpoints_get_array_option( 'wordpoints_sitewide_active_modules', 'site' )
		);

		// On the network admin screens we only load the network active modules.
		if ( is_network_admin() ) {
			$active_modules = $network_active_modules;
		} else {
			$active_modules = array_merge( $active_modules, $network_active_modules );
		}
	}

	if ( ! empty( $active_modules ) ) {

		$modules_dir = wordpoints_extensions_dir();

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
	 * Fires after all active extensions are loaded.
	 *
	 * It will always be fired even when no extensions are active.
	 *
	 * @since 2.4.0
	 */
	do_action( 'wordpoints_extensions_loaded' );

	/**
	 * Fires after all active extensions are loaded.
	 *
	 * It will always be fired even when no extensions are active.
	 *
	 * @since 1.0.0
	 * @deprecated 2.4.0 Use wordpoints_extensions_loaded instead.
	 */
	do_action_deprecated( 'wordpoints_modules_loaded', array(), '2.4.0', 'wordpoints_extensions_loaded' );
}

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

/**
 * Gets the available extension updates.
 *
 * @since 2.4.0
 *
 * @return WordPoints_Extension_UpdatesI The available extension updates.
 */
function wordpoints_get_extension_updates() {

	$updates = new WordPoints_Extension_Updates();
	$updates->fill();

	return $updates;
}

/**
 * Checks for extension updates.
 *
 * @since 2.4.0
 *
 * @WordPress\action wordpoints_check_for_extension_updates Cron event registered by
 *                   wordpoints_schedule_extension_update_checks().
 *
 * @param int $cache_timeout Maximum acceptable age for the cache. If the cache is
 *                           older than this, it will be updated. The default is 12
 *                           hours.
 *
 * @return WordPoints_Extension_UpdatesI|false The updates, or false if the check was
 *                                             not run (due to the cache being fresh
 *                                             enough, or some other reason).
 */
function wordpoints_check_for_extension_updates( $cache_timeout = null ) {

	$check = new WordPoints_Extension_Updates_Check( $cache_timeout );
	return $check->run();
}

/**
 * Schedules the extension updates check.
 *
 * @since 2.4.0
 *
 * @WordPress\action init
 */
function wordpoints_schedule_extension_updates_check() {

	$event = 'wordpoints_check_for_extension_updates';

	if ( ! wp_next_scheduled( $event ) ) {
		wp_schedule_event( time(), 'twicedaily', $event );
	}
}

/**
 * Reschedules the extension updates check from the current time.
 *
 * @since 2.4.0
 *
 * @WordPress\action wordpoints_extension_update_check_completed
 */
function wordpoints_reschedule_extension_updates_check() {

	wp_reschedule_event(
		time()
		, 'twicedaily'
		, 'wordpoints_check_for_extension_updates'
	);
}

/**
 * Checks for extension updates after an upgrade.
 *
 * @since 2.4.0
 *
 * @WordPress\action upgrader_process_complete
 *
 * @param object $upgrader The upgrader.
 * @param array  $data     Info about the upgrade.
 */
function wordpoints_recheck_for_extension_updates_after_upgrade( $upgrader, $data ) {

	if ( isset( $data['type'] ) && 'translation' === $data['type'] ) {
		return;
	}

	wordpoints_check_for_extension_updates_now();
}

/**
 * Checks for extension updates with a cache timeout of one hour.
 *
 * @since 2.4.0
 */
function wordpoints_check_for_extension_updates_hourly() {
	wordpoints_check_for_extension_updates( HOUR_IN_SECONDS );
}

/**
 * Checks for extension updates with a cache timeout of zero.
 *
 * @since 2.4.0
 */
function wordpoints_check_for_extension_updates_now() {
	wordpoints_check_for_extension_updates( 0 );
}

/**
 * Clean the extensions cache.
 *
 * @since 2.4.0
 *
 * @param bool $clear_update_cache Whether to clear the updates cache.
 */
function wordpoints_clean_extensions_cache( $clear_update_cache = true ) {

	if ( $clear_update_cache ) {
		$updates = new WordPoints_Extension_Updates();
		$updates->set_time_checked( 0 );
		$updates->save();
	}

	wp_cache_delete( 'wordpoints_modules', 'wordpoints_modules' );
}

/**
 * Add the extension update counts to the other update counts.
 *
 * @since 2.4.0
 *
 * @WordPress\filter wp_get_update_data
 *
 * @param array $update_data The update data.
 *
 * @return array The updated update counts.
 */
function wordpoints_extension_update_counts( $update_data ) {

	$update_data['counts']['wordpoints_extensions'] = 0;

	if ( current_user_can( 'update_wordpoints_extensions' ) ) {
		$extension_updates = wordpoints_get_extension_updates()->get_new_versions();

		if ( ! empty( $extension_updates ) ) {
			$update_data['counts']['wordpoints_extensions'] = count( $extension_updates );

			$title = sprintf(
				// translators: Number of updates.
				_n(
					'%d WordPoints Extension Update'
					, '%d WordPoints Extension Updates'
					, $update_data['counts']['wordpoints_extensions']
					, 'wordpoints'
				)
				, $update_data['counts']['wordpoints_extensions']
			);

			if ( ! empty( $update_data['title'] ) ) {
				$update_data['title'] .= ', ';
			}

			$update_data['title'] .= esc_attr( $title );
		}
	}

	$update_data['counts']['total'] += $update_data['counts']['wordpoints_extensions'];

	return $update_data;
}

/**
 * Filters the extension data to add missing server headers for specific extensions.
 *
 * A few extensions were released without the `ID` header set, either because it
 * hadn't been invented yet or because we just forgot. Because of this, they are
 * unable to receive updates from WordPoints.org. This function filters the headers
 * for these extensions, and adds in the `ID` and `server` header values as needed.
 *
 * @since 2.4.0
 *
 * @WordPress\filter wordpoints_extension_data
 *
 * @param array $data Extension header data.
 *
 * @return array The filtered extension header data.
 */
function wordpoints_extension_data_missing_server_headers_filter( $data ) {

	$missing_headers = array(
		'Beta Tester'             => '316',
		'Importer'                => '430',
		'WooCommerce'             => '445',
		'Points Logs Regenerator' => '530',
		'Reset Points'            => '540',
	);

	if (
		empty( $data['ID'] )
		&& ( 'J.D. Grimes' === $data['author'] || 'WordPoints' === $data['author'] )
		&& isset( $missing_headers[ $data['name'] ] )
	) {
		$data['server'] = 'wordpoints.org';
		$data['ID']     = $missing_headers[ $data['name'] ];
	}

	return $data;
}

// EOF
