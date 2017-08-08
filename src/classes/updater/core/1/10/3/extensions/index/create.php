<?php

/**
 * Create extensions index core 1.10.3 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Creates an index.php file in the extensions folder as part of the 1.10.3 update.
 *
 * @since 2.4.0
 */
class WordPoints_Updater_Core_1_10_3_Extensions_Index_Create
	implements WordPoints_RoutineI {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		global $wp_filesystem;

		$modules_dir = wordpoints_extensions_dir();

		if ( ! WP_Filesystem( false, $modules_dir ) ) {
			return;
		}

		$index_file = $modules_dir . '/index.php';

		if ( ! $wp_filesystem->exists( $index_file ) ) {
			$wp_filesystem->put_contents( $index_file, '<?php // Gold is silent.' );
		}
	}
}

// EOF
