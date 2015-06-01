<?php

/**
 * Class for managing installable entities like modules and components.
 *
 * @package WordPoints
 * @since 2.0.0
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

		if ( isset( self::$registered[ $type ][ $slug ] ) ) {
			return false;
		}

		self::$registered[ $type ][ $slug ] = $data;

		return true;
	}

	/**
	 * Install an installable.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The type of installable, 'module', 'component', 'plugin'.
	 * @param string $slug The installable's slug.
	 * @param bool   $network_wide Whether to install network-wide. Default is no.
	 *
	 * @return WordPoints_Un_Installer_Base|false Whether the module was installed.
	 */
	public static function install( $type, $slug, $network_wide = false ) {

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
	 *
	 * @param string $type The type of installable: 'module', 'component', 'plugin'.
	 * @param string $slug The installable's slug.
	 *
	 * @return WordPoints_Un_Installer_Base|false Whether the module was uninstalled.
	 */
	public static function uninstall( $type, $slug ) {

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
	 *
	 * @WordPress\action wordpoints_modules_loaded 5 Before most module code runs.
	 */
	public static function maybe_do_updates() {

		$wordpoints_data = get_option( 'wordpoints_data' );

		if ( is_wordpoints_network_active() ) {
			$network_wide_data = get_site_option( 'wordpoints_data' );
		}

		$updated = false;

		foreach ( self::$registered as $type => $installables ) {

			foreach ( $installables as $slug => $installable ) {

				if ( isset( $network_wide_data ) && $installable['network_wide'] ) {
					$data =& $network_wide_data;
				} else {
					$data =& $wordpoints_data;
				}

				if ( 'wordpoints' === $slug ) {

					if ( ! isset( $data['version'] ) ) {
						continue;
					}

					$db_version = $data['version'];

				} else {

					// This installable hasn't been installed yet, so we don't update.
					if ( ! isset( $data[ "{$type}s" ][ $slug ]['version'] ) ) {
						continue;
					}

					$db_version = $data[ "{$type}s" ][ $slug ]['version'];
				}

				$code_version = $installable['version'];

				// If the DB version isn't less than the code version, we don't need to upgrade.
				if ( version_compare( $db_version, $code_version ) !== -1 ) {
					continue;
				}

				$installer = self::get_installer( $type, $slug );

				if ( ! $installer ) {
					continue;
				}

				$installer->update( $db_version, $code_version );

				if ( 'wordpoints' === $slug ) {
					$data['version'] = $code_version;
				} else {
					$data[ "{$type}s" ][ $slug ]['version'] = $code_version;
				}

				$updated = true;
			}
		}

		if ( $updated ) {
			update_option( 'wordpoints_data', $wordpoints_data );

			if ( isset( $network_wide_data ) ) {
				update_site_option( 'wordpoints_data', $network_wide_data );
			}
		}
	}

	/**
	 * Show the admin a notice if the update/install for an installable was skipped.
	 *
	 * @since 2.0.0
	 *
	 * @WordPoints\action admin_notices
	 */
	public static function admin_notices() {

		if ( ! is_wordpoints_network_active() ) {
			return;
		}

		self::show_admin_notices( 'install' );
		self::show_admin_notices( 'update' );
	}

	/**
	 * Show the admin a notice if the update/install for an installable was skipped.
	 *
	 * @since 2.0.0
	 *
	 * @param string $notice_type The type of notices to display, 'update', or 'install'.
	 */
	protected static function show_admin_notices( $notice_type ) {

		$all_skipped = array_filter(
			wordpoints_get_array_option( "wordpoints_network_{$notice_type}_skipped", 'site' )
		);

		if ( empty( $all_skipped ) ) {
			return;
		}

		$messages = array();

		if ( 'install' === $notice_type ) {
			/* translators: 1 module/plugin name, 2 "module", "plugin", or "component". */
			$message_template = __( 'WordPoints detected a large network and has skipped part of the installation process for the &#8220;%1$s&#8221; %2$s.', 'wordpoints' );
		} else {
			/* translators: 1 module/plugin name, 2 "module", "plugin", or "component", 3 version number. */
			$message_template = __( 'WordPoints detected a large network and has skipped part of the update process for the &#8220;%1$s&#8221; %2$s for version %3$s (and possibly later versions).', 'wordpoints' );
		}

		foreach ( $all_skipped as $type => $skipped ) {

			if ( ! self::can_show_admin_notices( $type ) ) {
				continue;
			}

			switch ( $type ) {

				case 'module':
					$type_name = __( 'module', 'wordpoints' );
				break;

				case 'component':
					$type_name = __( 'component', 'wordpoints' );
				break;

				default:
					$type_name = __( 'plugin', 'wordpoints' );
			}

			foreach ( $skipped as $slug => $version ) {

				if ( empty( self::$registered[ $type ][ $slug ]['network_wide'] ) ) {
					continue;
				}

				// Normally we might have used the installable's fancy name instead
				// of the slug, but this is such an edge case to start with that I
				// decided not to. Also of note: the version is only used in the
				// update message.
				$messages[] = esc_html(
					sprintf(
						$message_template
						, $slug
						, $type_name
						, $version
					)
				);
			}
		}

		if ( ! empty( $messages ) ) {

			$message = '<p>' . implode( '</p><p>', $messages ) . '</p>';
			$message .= '<p>' . esc_html__( 'The rest of the process needs to be completed manually. If this has not been done already, some parts of the component may not function properly.', 'wordpoints' );
			$message .= ' <a href="http://wordpoints.org/user-guide/multisite/" target="_blank">' . esc_html__( 'Learn more.', 'wordpoints' ) . '</a></p>';

			$args = array(
				'dismissible' => true,
				'option' => "wordpoints_network_{$notice_type}_skipped",
			);

			wordpoints_show_admin_error( $message, $args );
		}
	}

	/**
	 * Check whether to display any admin notices regarding updates.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The type of installables notices are being displayed for.
	 *
	 * @return bool Whether to display the admin notices.
	 */
	protected static function can_show_admin_notices( $type ) {

		switch ( $type ) {

			case 'module':
				return current_user_can( 'wordpoints_manage_network_modules' );

			default:
				return current_user_can( 'manage_network_plugins' );
		}
	}

	/**
	 * Get the installer class for an installable.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The type of installable: 'module', 'component', etc.
	 * @param string $slug The slug of the installable to get the installer for.
	 *
	 * @return WordPoints_Un_Installer_Base|false The installer.
	 */
	public static function get_installer( $type, $slug ) {

		static $loaded_base_uninstaller = false;

		if ( ! isset( self::$registered[ $type ][ $slug ] ) ) {
			return false;
		}

		if ( ! isset( self::$installers[ $type ][ $slug ] ) ) {

			if ( ! $loaded_base_uninstaller ) {

				/**
				 * Uninstall base class.
				 *
				 * @since 2.0.0
				 */
				require_once( WORDPOINTS_DIR . '/includes/class-un-installer-base.php' );

				$loaded_base_uninstaller = true;
			}

			if ( ! file_exists( self::$registered[ $type ][ $slug ]['un_installer'] ) ) {
				return false;
			}

			self::$installers[ $type ][ $slug ] = require(
				self::$registered[ $type ][ $slug ]['un_installer']
			);
		}

		return new self::$installers[ $type ][ $slug ](
			$slug
			, self::$registered[ $type ][ $slug ]['version']
		);
	}

	/**
	 * Install network-wide installables on a new site when it is created.
	 *
	 * @since 2.0.0
	 *
	 * @WordPress\action wpmu_new_blog
	 *
	 * @param int $blog_id The ID of the new blog.
	 */
	public static function wpmu_new_blog( $blog_id ) {

		foreach ( self::$registered as $type => $installables ) {

			$network_installables = wp_list_filter(
				$installables
				, array( 'network_wide' => true )
			);

			if ( empty( $network_installables ) ) {
				continue;
			}

			foreach ( $network_installables as $slug => $installable ) {

				$installer = self::get_installer( $type, $slug );

				if ( ! $installer ) {
					continue;
				}

				$installer->install_on_site( $blog_id );
			}
		}
	}
}
add_action( 'wordpoints_modules_loaded', 'WordPoints_Installables::maybe_do_updates', 5 );
add_action( 'admin_notices', 'WordPoints_Installables::admin_notices' );
add_action( 'wpmu_new_blog', 'WordPoints_Installables::wpmu_new_blog' );

// EOF
