<?php

/**
 * Modules class.
 *
 * @package WordPoints
 * @since 2.3.0
 */

/**
 * Store for holding module data.
 *
 * This class acts as a data store for module "header" data, that, in plugins, is
 * included in a comment at the top of the main file. Module which want to make their
 * data available without having to have their main file parsed, can instead register
 * the data using this class.
 *
 * In WordPoints 1.0.0 there was a WordPoints_Modules class, but it served a slightly
 * different purpose.
 *
 * The class is static, and cannot be constructed.
 *
 * @since 2.0.0
 */
final class WordPoints_Modules {

	//
	// Private Vars.
	//

	/**
	 * The module file headers supported by default.
	 *
	 * @since 2.0.0
	 *
	 * @var string[]
	 */
	private static $default_headers = array(
		'Extension Name' => 'name',
		'Extension URI'  => 'uri',
		'Module Name'    => 'module_name',
		'Module URI'     => 'module_uri',
		'Version'        => 'version',
		'Description'    => 'description',
		'Author'         => 'author',
		'Author URI'     => 'author_uri',
		'Text Domain'    => 'text_domain',
		'Domain Path'    => 'domain_path',
		'Network'        => 'network',
		'Update API'     => 'update_api',
		'Channel'        => 'channel',
		'Server'         => 'server',
		'ID'             => 'ID',
		'Namespace'      => 'namespace',
	);

	/**
	 * The registered modules.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	private static $registered = array();

	//
	// Private Methods.
	//

	/**
	 * In-constructable.
	 *
	 * @since 2.0.0
	 */
	private function __construct() {}

	/**
	 * I am 1.
	 *
	 * @since 2.0.0
	 */
	private function __clone() {}

	//
	// Public Methods.
	//

	/**
	 * Get the slug of a module from the full path of one of it's files.
	 *
	 * @since 2.0.0
	 *
	 * @param string $file The full path of a module's file.
	 *
	 * @return string The unique slug used to identify this module.
	 */
	public static function get_slug( $file ) {

		return strrev(
			basename( strrev( wordpoints_module_basename( $file ) ) )
		);
	}

	/**
	 * Get the data for a module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $module The full path to one of the module's files.
	 * @param string $data   The slug of the piece of data to get.
	 *
	 * @return string[]|string|false The data, or false if it isn't registered.
	 */
	public static function get_data( $module, $data = null ) {

		$slug = self::get_slug( $module );

		if ( ! isset( self::$registered[ $slug ] ) ) {
			return false;
		}

		if ( $data ) {
			if ( ! isset( self::$registered[ $slug ][ $data ] ) ) {
				return false;
			}

			return self::$registered[ $slug ][ $data ];
		}

		return self::$registered[ $slug ];
	}

	/**
	 * Register a module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $data The header data for the module.
	 * @param string $file The full path to the main file of the module.
	 *
	 * @return bool True, or false if the module has already been registered.
	 */
	public static function register( $data, $file ) {

		$slug = self::get_slug( $file );

		if ( isset( self::$registered[ $slug ] ) ) {
			return false;
		}

		self::$registered[ $slug ] = self::parse_headers( $data );

		self::$registered[ $slug ]['raw']      = $data;
		self::$registered[ $slug ]['raw_file'] = wp_normalize_path( $file );

		self::register_installable( $slug, $file );
		self::maybe_load_textdomain( $slug, $file );

		return true;
	}

	/**
	 * Parse the header data from the raw module headers.
	 *
	 * @since 2.0.0
	 *
	 * @param string $data The raw headers.
	 *
	 * @return array The parsed header data.
	 */
	private static function parse_headers( $data ) {

		$parsed = array();

		$lines = explode( "\n", $data );

		foreach ( $lines as $line ) {

			$line = trim( $line );

			if ( empty( $line ) ) {
				continue;
			}

			$parts = explode( ':', $line, 2 );

			$data_slug = trim( $parts[0] );

			if ( isset( self::$default_headers[ $parts[0] ] ) ) {
				$data_slug = self::$default_headers[ $parts[0] ];
			}

			$parsed[ $data_slug ] = trim( $parts[1] );
		}

		return $parsed + array_fill_keys( self::$default_headers, '' );
	}

	/**
	 * Register the installable for a module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug The module slug.
	 * @param string $file The main module file.
	 */
	private static function register_installable( $slug, $file ) {

		wordpoints_apps()->get_sub_app( 'installables' )->register(
			'extension'
			, $slug
			, 'WordPoints_Modules::get_installable'
			, self::$registered[ $slug ]['version']
			, is_wordpoints_module_active_for_network( $file )
		);
	}

	/**
	 * Load the text domain for a module, if one is specified.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug The module slug.
	 * @param string $file The main module file.
	 */
	private static function maybe_load_textdomain( $slug, $file ) {

		if ( empty( self::$registered[ $slug ]['text_domain'] ) ) {
			return;
		}

		$path = false;

		if ( ! empty( self::$registered[ $slug ]['domain_path'] ) ) {
			$path = wordpoints_module_basename( $file ) . self::$registered[ $slug ]['domain_path'];
		}

		wordpoints_load_module_textdomain(
			self::$registered[ $slug ]['text_domain']
			, $path
		);
	}

	/**
	 * Runs the install routine for a module.
	 *
	 * @since 2.4.0
	 *
	 * @internal Not intended to be used except internally by core.
	 *
	 * @param string $slug         The slug of the module.
	 * @param bool   $network_wide Whether to install it network wide.
	 */
	public static function install( $slug, $network_wide ) {

		$installable = self::get_installable( 'extension', $slug );

		if ( ! $installable ) {
			return;
		}

		if ( $installable instanceof WordPoints_Installable_Legacy ) {

			WordPoints_Installables::get_installer(
				'module'
				, $slug
				, self::$registered[ $slug ]['version']
				, dirname( self::$registered[ $slug ]['raw_file'] ) . '/includes/class-un-installer.php'
			)
				->install( $network_wide );

		} else {

			$installer = new WordPoints_Installer( $installable, $network_wide );
			$installer->run();
		}
	}

	/**
	 * Gets the installable object for a module.
	 *
	 * @since 2.4.0
	 *
	 * @param string $type The installable type.
	 * @param string $slug The installable slug.
	 *
	 * @return WordPoints_InstallableI|false The installable object, or false.
	 */
	public static function get_installable( $type, $slug ) {

		$installable = false;

		if ( ! isset( self::$registered[ $slug ] ) ) {
			return $installable;
		}

		$module = self::$registered[ $slug ];

		$uninstaller = dirname( $module['raw_file'] ) . '/includes/class-un-installer.php';

		if (
			$module['namespace']
			&& class_exists( "WordPoints_{$module['namespace']}_Installable" )
		) {

			$class       = "WordPoints_{$module['namespace']}_Installable";
			$installable = new $class( $slug );

		} elseif ( file_exists( $uninstaller ) ) {

			$installable = new WordPoints_Installable_Legacy(
				'module'
				, $slug
				, $module['version']
			);
		}

		return $installable;
	}
}

// EOF
