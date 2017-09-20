<?php

/**
 * Installables class.
 *
 * @package WordPoints
 * @since 2.3.0
 */

/**
 * Handles installing, uninstalling, and updating modules, components, and plugins.
 *
 * This class acts as a container for the activated modules, components, and other
 * installable entities. As such, it works as an interface between the entities' un/
 * install classes and any code which wishes to use them.
 *
 * This class is static, and cannot be constructed.
 *
 * @since 2.0.0
 * @deprecated 2.4.0 Use the installables app instead.
 */
final class WordPoints_Installables {

	//
	// Private Vars.
	//

	/**
	 * The registered installables.
	 *
	 * It is indexed by the different types of installables, 'module', 'component',
	 * etc. Each list is then indexed by the installable's slug.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	private static $registered = array();

	/**
	 * The installers for the installables.
	 *
	 * This array is of the same format as self::$registered.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	private static $installers = array();

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
	 * Register an installable.
	 *
	 * @since 2.0.0
	 * @deprecated 2.4.0 Use WordPoints_Installables_App::register() instead.
	 *
	 * @param string $type The type of installable, 'module', 'component', 'plugin'.
	 * @param string $slug The installable's unique slug.
	 * @param array  $data {
	 *        @type string $version      The installable's version.
	 *        @type string $un_installer The full path to the un/installer file.
	 *        @type bool   $network_wide Whether this installable is active network-wide.
	 * }
	 *
	 * @return bool True, or false if the installable has already been registered.
	 */
	public static function register( $type, $slug, $data ) {

		_deprecated_function(
			__METHOD__
			, '2.4.0'
			, 'WordPoints_Installables_App::register'
		);

		self::$registered[ $type ][ $slug ] = $data;

		/** @var WordPoints_Installables_App $installables */
		$installables = wordpoints_apps()->get_sub_app( 'installables' );
		$installables->register(
			'module' === $type ? 'extension' : $type
			, $slug
			, 'WordPoints_Installables::installer_loader'
			, $data['version']
			, ! empty( $data['network_wide'] )
		);

		return true;
	}

	/**
	 * Install an installable.
	 *
	 * @since 2.0.0
	 * @deprecated 2.4.0 Use WordPoints_Installer instead.
	 *
	 * @param string $type The type of installable, 'module', 'component', 'plugin'.
	 * @param string $slug The installable's slug.
	 * @param bool   $network_wide Whether to install network-wide. Default is no.
	 *
	 * @return WordPoints_Un_Installer_Base|false Whether the module was installed.
	 */
	public static function install( $type, $slug, $network_wide = false ) {

		_deprecated_function( __METHOD__, '2.4.0' );

		if ( ! isset( self::$registered[ $type ][ $slug ] ) ) {
			return false;
		}

		$installer = self::get_installer( $type, $slug );

		if ( ! $installer ) {
			return false;
		}

		$installer->install( $network_wide );

		return $installer;
	}

	/**
	 * Uninstall an installable.
	 *
	 * @since 2.0.0
	 * @deprecated 2.4.0 Use WordPoints_Uninstaller instead.
	 *
	 * @param string $type The type of installable: 'module', 'component', 'plugin'.
	 * @param string $slug The installable's slug.
	 *
	 * @return WordPoints_Un_Installer_Base|false Whether the module was uninstalled.
	 */
	public static function uninstall( $type, $slug ) {

		_deprecated_function( __METHOD__, '2.4.0' );

		if ( ! isset( self::$registered[ $type ][ $slug ] ) ) {
			return false;
		}

		$installer = self::get_installer( $type, $slug );

		if ( ! $installer ) {
			return false;
		}

		$installer->uninstall();

		return $installer;
	}

	/**
	 * Check if any of the active installables has an update, and run them if so.
	 *
	 * @since 2.0.0
	 * @deprecated 2.4.0 Use wordpoints_installables_maybe_update() instead.
	 */
	public static function maybe_do_updates() {

		_deprecated_function( __METHOD__, '2.4.0', 'wordpoints_installables_maybe_update' );

		wordpoints_installables_maybe_update();
	}

	/**
	 * Show the admin a notice if the update/install for an installable was skipped.
	 *
	 * @since 2.0.0
	 * @deprecated 2.4.0 Use wordpoints_update_skipped_show_admin_notices() instead.
	 */
	public static function admin_notices() {

		_deprecated_function( __METHOD__, '2.4.0', 'wordpoints_update_skipped_show_admin_notices' );

		if ( is_wordpoints_network_active() ) {
			wordpoints_admin_show_update_skipped_notices( 'install' );
			wordpoints_admin_show_update_skipped_notices( 'update' );
		}
	}

	/**
	 * Get the installer class for an installable.
	 *
	 * @since 2.0.0
	 * @since 2.4.0 The $version and $un_installer parameters were added so that an
	 *              un/installer could be retrieved without having to be registered.
	 *
	 * @param string $type         The type of installable: 'module', 'component', etc.
	 * @param string $slug         The slug of the installable to get the installer for.
	 * @param string $version      The version of the installable. Only used if $un_installer is passed.
	 * @param string $un_installer The path to the un/installer file.
	 *
	 * @return WordPoints_Un_Installer_Base|false The installer.
	 */
	public static function get_installer( $type, $slug, $version = null, $un_installer = null ) {

		if ( ! isset( $un_installer ) ) {

			if ( ! isset( self::$registered[ $type ][ $slug ] ) ) {
				return false;
			}

			$version      = self::$registered[ $type ][ $slug ]['version'];
			$un_installer = self::$registered[ $type ][ $slug ]['un_installer'];
		}

		if ( ! isset( self::$installers[ $type ][ $slug ] ) ) {

			if ( ! file_exists( $un_installer ) ) {
				return false;
			}

			self::$installers[ $type ][ $slug ] = require $un_installer;
		}

		return new self::$installers[ $type ][ $slug ]( $slug, $version );
	}

	/**
	 * Install network-wide installables on a new site when it is created.
	 *
	 * @since 2.0.0
	 * @deprecated 2.4.0 Use wordpoints_installables_install_on_new_site() instead.
	 *
	 * @param int $blog_id The ID of the new blog.
	 */
	public static function wpmu_new_blog( $blog_id ) {

		_deprecated_function( __METHOD__, '2.4.0', 'wordpoints_installables_install_on_new_site' );

		wordpoints_installables_install_on_new_site( $blog_id );
	}

	/**
	 * Loads the installer for an installable.
	 *
	 * @since 2.4.0
	 *
	 * @param string $type The type of installable.
	 * @param string $slug The slug of the installable.
	 *
	 * @return WordPoints_InstallableI The installable object.
	 */
	public static function installer_loader( $type, $slug ) {

		if ( 'extension' === $type ) {
			$type = 'module';
		}

		return new WordPoints_Installable_Legacy(
			$type
			, $slug
			, self::$registered[ $type ][ $slug ]['version']
		);
	}
}

// EOF
