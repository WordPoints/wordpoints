<?php

/**
 * Rename extensions directory core 2.4.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Renames the extensions directory as part of the 2.4.0 update.
 *
 * @since 2.4.0
 */
class WordPoints_Updater_Core_2_4_0_Extensions_Directory_Rename
	implements WordPoints_RoutineI {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		global $wp_filesystem;

		if ( ! $wp_filesystem && ! WP_Filesystem() ) {
			update_site_option( 'wordpoints_legacy_extensions_dir', true );
			return;
		}

		$legacy = WP_CONTENT_DIR . '/wordpoints-modules';

		if ( $wp_filesystem->is_dir( $legacy ) ) {

			$moved = $wp_filesystem->move( $legacy, WP_CONTENT_DIR . '/wordpoints-extensions' );

			if ( ! $moved ) {
				update_site_option( 'wordpoints_legacy_extensions_dir', true );
			}
		}
	}
}

// EOF
