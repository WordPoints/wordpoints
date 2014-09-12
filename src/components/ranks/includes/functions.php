<?php

/**
 * Ranks component functions.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Install the ranks component.
 *
 * @since 1.7.0
 *
 * @action wordpoints_activate_component-ranks
 */
function wordpoints_ranks_component_activate() {

	$wordpoints_data = wordpoints_get_array_option( 'wordpoints_data', 'network' );

	if ( ! isset( $wordpoints_data['components']['ranks']['version'] ) ) {

		// The component hasn't yet been installed.

		/**
		 * Installs the ranks component.
		 *
		 * @since 1.7.0
		 */
		require WORDPOINTS_DIR . 'components/ranks/install.php';
	}
}
add_action( 'wordpoints_component_activate-ranks', 'wordpoints_ranks_component_activate' );

/**
 * Get the database schema for the ranks component.
 *
 * @since 1.7.0
 *
 * @return string CREATE TABLE queries that can be passed to dbDelta().
 */
function wordpoints_ranks_get_db_schema() {

	global $wpdb;

	$charset_collate = '';

	if ( ! empty( $wpdb->charset ) ) {
		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}

	if ( ! empty( $wpdb->collate ) ) {
		$charset_collate .= " COLLATE {$wpdb->collate}";
	}

	return "CREATE TABLE {$wpdb->wordpoints_ranks} (
			id BIGINT(20) NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			type VARCHAR(255) NOT NULL,
			rank_group VARCHAR(255) NOT NULL,
			blog_id SMALLINT(5) UNSIGNED NOT NULL,
			site_id SMALLINT(5) UNSIGNED NOT NULL,
			PRIMARY KEY  (id),
			KEY type (type),
			KEY site (blog_id,site_id)
		) {$charset_collate};
		CREATE TABLE {$wpdb->wordpoints_rankmeta} (
			meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			wordpoints_rank_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			meta_key VARCHAR(255) DEFAULT NULL,
			meta_value LONGTEXT,
			PRIMARY KEY  (meta_id),
			KEY wordpoints_rank_id (wordpoints_rank_id)
		) {$charset_collate};
		CREATE TABLE {$wpdb->wordpoints_user_ranks} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			rank_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			PRIMARY KEY  (id)
		) {$charset_collate};";
}

// EOF
