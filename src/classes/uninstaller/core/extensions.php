<?php

/**
 * Core extensions uninstaller class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Uninstaller for WordPoints extensions.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Core_Extensions implements WordPoints_RoutineI {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		require_once WORDPOINTS_DIR . '/includes/modules.php';

		wordpoints_deactivate_modules(
			wordpoints_get_array_option( 'wordpoints_active_modules', 'site' )
		);

		foreach ( array_keys( wordpoints_get_modules() ) as $module ) {
			wordpoints_uninstall_module( $module );
		}

		global $wp_filesystem;

		if ( $wp_filesystem instanceof WP_Filesystem_Base ) {
			$wp_filesystem->delete( wordpoints_extensions_dir(), true );
		}
	}
}

// EOF
