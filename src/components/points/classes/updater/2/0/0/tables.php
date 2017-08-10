<?php

/**
 * Tables 2.0.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates the points DB tables to 2.0.0.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Updater_2_0_0_Tables
	extends WordPoints_Updater_DB_Tables_UTF8MB4 {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		global $wpdb;

		// So that we can change tables to utf8mb4, we need to shorten the index
		// lengths to less than 767 bytes.
		$wpdb->query(
			"
			ALTER TABLE {$wpdb->wordpoints_points_logs}
			DROP INDEX points_type,
			ADD INDEX points_type(points_type(191))
			"
		); // WPCS: cache pass.

		$wpdb->query(
			"
			ALTER TABLE {$wpdb->wordpoints_points_logs}
			DROP INDEX log_type,
			ADD INDEX log_type(log_type(191))
			"
		); // WPCS: cache pass.

		$wpdb->query(
			"
			ALTER TABLE {$wpdb->wordpoints_points_log_meta}
			DROP INDEX meta_key,
			ADD INDEX meta_key(meta_key(191))
			"
		); // WPCS: cache pass.

		parent::run();
	}
}

// EOF
