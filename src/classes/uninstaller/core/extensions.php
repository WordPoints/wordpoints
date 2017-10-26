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

		// We use a custom filter on the legacy path, because the default one relies
		// on WordPoints's activation status.
		remove_filter( 'wordpoints_extensions_dir', 'wordpoints_legacy_modules_path', 5 );
		remove_filter( 'wordpoints_extensions_url', 'wordpoints_legacy_modules_path', 5 );

		add_filter( 'wordpoints_extensions_dir', array( $this, 'legacy_path' ), 5 );
		add_filter( 'wordpoints_extensions_url', array( $this, 'legacy_path' ), 5 );

		require_once WORDPOINTS_DIR . '/includes/modules.php';

		add_filter( 'is_wordpoints_extension_active', '__return_false' );

		foreach ( array_keys( wordpoints_get_modules() ) as $module ) {
			wordpoints_uninstall_module( $module );
		}

		global $wp_filesystem;

		if ( $wp_filesystem instanceof WP_Filesystem_Base ) {
			$wp_filesystem->delete( wordpoints_extensions_dir(), true );
		}
	}

	/**
	 * Filters the extension path to correct it to the legacy path if needed.
	 *
	 * @since 2.4.0
	 *
	 * @param string $path The extensions path.
	 *
	 * @return string The filtered path.
	 */
	public function legacy_path( $path ) {

		$new_path = WP_CONTENT_DIR . '/wordpoints-extensions';

		if ( false !== strpos( $path, $new_path ) && ! is_dir( $new_path ) ) {
			$path = str_replace(
				'/wordpoints-extensions'
				, '/wordpoints-modules'
				, $path
			);
		}

		return $path;
	}
}

// EOF
