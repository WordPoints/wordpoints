<?php

/**
 * Tables 2.0.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates the ranks DB tables to 2.0.0.
 *
 * @since 2.4.0
 */
class WordPoints_Ranks_Updater_2_0_0_Tables
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
			ALTER TABLE {$wpdb->wordpoints_ranks}
			DROP INDEX type,
			ADD INDEX type(type(191))
			"
		); // WPCS: cache pass.

		parent::run();
	}
}

// EOF
