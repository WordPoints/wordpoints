<?php

/**
 * Utility functions for the points component.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

// Back-compat < 1.7.0
include_once(  WORDPOINTS_DIR . 'components/points/points.php' );

/**
 * Install the points component.
 *
 * @since 1.0.0
 *
 * @action wordpoints_activate_component-points
 */
function wordpoints_points_component_activate() {

	/*
	 * Regenerate the custom caps every time on multisite, because they depend on
	 * network activation status.
	 */
	if ( is_multisite() ) {

		global $wpdb;

		$custom_caps = wordpoints_points_get_custom_caps();
		$custom_caps_keys = array_keys( $custom_caps );

		$network_active = is_wordpoints_network_active();

		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

		foreach ( $blog_ids as $blog_id ) {

			switch_to_blog( $blog_id );

			wordpoints_remove_custom_caps( $custom_caps_keys );

			if ( $network_active ) {
				wordpoints_add_custom_caps( $custom_caps );
			}

			restore_current_blog();
		}

		if ( ! $network_active ) {
			wordpoints_add_custom_caps( $custom_caps );
		}
	}

	$wordpoints_data = wordpoints_get_array_option( 'wordpoints_data', 'network' );

	if ( ! isset( $wordpoints_data['components']['points']['version'] ) ) {

		// The component hasn't yet been installed.

		/**
		 * Installs the points component.
		 *
		 * @since 1.0.0
		 */
		require WORDPOINTS_DIR . 'components/points/install.php';
	}
}
add_action( 'wordpoints_component_activate-points', 'wordpoints_points_component_activate' );

/**
 * Update the points component.
 *
 * @since 1.2.0
 *
 * @action wordpoints_components_loaded
 */
function wordpoints_points_component_update() {

	$db_version = '1.0.0';

	$wordpoints_data = wordpoints_get_network_option( 'wordpoints_data' );

	if ( isset( $wordpoints_data['components']['points']['version'] ) ) {
		$db_version = $wordpoints_data['components']['points']['version'];
	}

	// If the DB version isn't less than the code version, we don't need to upgrade.
	if ( version_compare( $db_version, WORDPOINTS_VERSION ) !== -1 ) {
		return;
	}

	/**
	 * The update functions for the points component.
	 *
	 * @since 1.2.0
	 */
	require_once WORDPOINTS_DIR . 'components/points/includes/update.php';

	switch ( 1 ) {

		case version_compare( '1.2.0', $db_version ):
			wordpoints_points_update_1_2_0();
		// fallthru

		case version_compare( '1.4.0', $db_version ):
			wordpoints_points_update_1_4_0();
		// fallthru

		case version_compare( '1.5.0', $db_version ):
			if ( 1 !== version_compare( '1.4.0', $db_version ) ) {
				// This doesn't need to run if we just ran the 1.4.0 update.
				wordpoints_points_update_1_5_0();
			}
		// fallthru

		case version_compare( '1.5.1', $db_version ):
			wordpoints_points_update_1_5_1();
		// fallthru
	}

	$wordpoints_data['components']['points']['version'] = WORDPOINTS_VERSION;

	wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
}
add_action( 'wordpoints_components_loaded', 'wordpoints_points_component_update' );

/**
 * Register scripts and styles for the component.
 *
 * @since 1.0.0
 *
 * @action wp_enqueue_scripts 5 Register scripts before default enqueue (10).
 * @action admin_enqueue_scripts 5 Register admin scripts so they are ready on 10.
 */
function wordpoints_points_register_scripts() {

	$assets_url = WORDPOINTS_URL . '/components/points/assets/';

	wp_register_style(
		'wordpoints-top-users'
		,$assets_url . 'css/top-users.css'
		,null
		,WORDPOINTS_VERSION
	);

	wp_register_style(
		'wordpoints-points-logs'
		,$assets_url . 'css/points-logs.css'
		,null
		,WORDPOINTS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'wordpoints_points_register_scripts', 5 );
add_action( 'admin_enqueue_scripts', 'wordpoints_points_register_scripts', 5 );

/**
 * Get the custom caps added by the points component.
 *
 * @since 1.3.0
 *
 * @return array The custom capabilities as keys, WP core counterparts as values.
 */
function wordpoints_points_get_custom_caps() {

	return array(
		'set_wordpoints_points'                  => 'manage_options',
		'manage_network_wordpoints_points_hooks' => 'manage_network_options',
		'manage_wordpoints_points_types'         => ( is_wordpoints_network_active() ) ? 'manage_network_options' : 'manage_options',
	);
}

/**
 * Add custom capabilities to new sites on creation when in network mode.
 *
 * @since 1.5.0
 *
 * @action wpmu_new_blog
 *
 * @param int $blog_id The ID of the new site.
 */
function wordpoints_points_add_custom_caps_to_new_sites( $blog_id ) {

	if ( ! is_wordpoints_network_active() ) {
		return;
	}

	switch_to_blog( $blog_id );
	wordpoints_add_custom_caps( wordpoints_points_get_custom_caps() );
	restore_current_blog();
}
add_action( 'wpmu_new_blog', 'wordpoints_points_add_custom_caps_to_new_sites' );

/**
 * Format points for display.
 *
 * @since 1.0.0
 *
 * @filter wordpoints_format_points 5 Runs before default of 10, but you should
 *         remove the filter if you will always be overriding it.
 *
 * @param string $formatted The formatted points value.
 * @param int    $points    The raw points value.
 * @param string $type      The type of $points.
 *
 * @return string $points formatted with prefix and suffix.
 */
function wordpoints_format_points_filter( $formatted, $points, $type ) {

	$points_type = wordpoints_get_points_type( $type );

	if ( isset( $points_type['prefix'], $points_type['suffix'] ) ) {

		if ( $points < 0 ) {

			$points = abs( $points );
			$points_type['prefix'] = '-' . $points_type['prefix'];
		}

		$formatted = esc_html( $points_type['prefix'] . $points . $points_type['suffix'] );
	}

	return $formatted;
}
add_filter( 'wordpoints_format_points', 'wordpoints_format_points_filter', 5, 3 );

/**
 * Display a dropdown of points types.
 *
 * The $args parameter accepts an extra argument, 'options', which will be added to
 * the points types in the dropdown.
 *
 * @since 1.0.0
 *
 * @param array $args The arguments for the dropdown {@see
 *        WordPoints_Dropdown_Builder::$args}
 */
function wordpoints_points_types_dropdown( array $args ) {

	$points_types = array();

	foreach ( wordpoints_get_points_types() as $slug => $settings ) {

		$points_types[ $slug ] = $settings['name'];
	}

	if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
		$points_types = $args['options'] + $points_types;
	}

	$dropdown = new WordPoints_Dropdown_Builder( $points_types, $args );

	$dropdown->display();
}

/**
 * Delete points logs and meta when a user is deleted.
 *
 * @since 1.2.0
 *
 * @action deleted_user
 *
 * @param int $user_id The ID of the user just deleted.
 */
function wordpoints_delete_points_logs_for_user( $user_id ) {

	global $wpdb;

	$blog_only = '';

	// If the user is only being deleted from a single blog on multisite.
	if ( is_multisite() && get_userdata( $user_id ) ) {
		$blog_only = 'AND blog_id = %d';
	}

	// Delete log meta.
	$wpdb->query(
		$wpdb->prepare(
			"
				DELETE
				FROM {$wpdb->wordpoints_points_log_meta}
				WHERE log_id IN(
					SELECT id
					FROM {$wpdb->wordpoints_points_logs}
					WHERE user_id = %d
						AND site_id = %d
						{$blog_only}
				)
			"
			,$user_id
			,$wpdb->siteid
			,$wpdb->blogid
		)
	);

	$where = array( 'user_id' => $user_id );

	if ( $blog_only !== '' ) {
		$where['blog_id'] = $wpdb->blogid;
	}

	// Now delete the logs.
	$wpdb->delete(
		$wpdb->wordpoints_points_logs
		,$where
		,'%d'
	);
}
add_action( 'deleted_user', 'wordpoints_delete_points_logs_for_user' );

/**
 * Delete logs and meta for a blog when it is deleted.
 *
 * @since 1.2.0
 *
 * @action delete_blog
 *
 * @param int $blog_id The ID of the blog being deleted.
 */
function wordpoints_delete_points_logs_for_blog( $blog_id ) {

	global $wpdb;

	// Delete log meta.
	$wpdb->query(
		$wpdb->prepare(
			"
				DELETE
				FROM {$wpdb->wordpoints_points_log_meta}
				WHERE log_id IN(
					SELECT id
					FROM {$wpdb->wordpoints_points_logs}
					WHERE blog_id = %d
				)
			"
			,$blog_id
		)
	);

	// Now delete the logs.
	$wpdb->delete(
		$wpdb->wordpoints_points_logs
		,array( 'blog_id' => $blog_id )
		,'%d'
	);
}
add_action( 'delete_blog', 'wordpoints_delete_points_logs_for_blog' );

/**
 * Display a message with a points type's settings when it uses a custom meta key.
 *
 * @since 1.3.0
 *
 * @action wordpoints_points_type_form_top
 *
 * @param string $points_type The type of points the settings are being shown for.
 */
function wordpoints_points_settings_custom_meta_key_message( $points_type ) {

	$custom_key = wordpoints_get_points_type_setting( $points_type, 'meta_key' );

	if ( ! empty( $custom_key ) ) {
		echo '<p>' . esc_html( sprintf( __( 'This points type uses a custom meta key: %s', 'wordpoints' ), $custom_key ) ) . '</p>';
	}
}
add_action( 'wordpoints_points_type_form_top', 'wordpoints_points_settings_custom_meta_key_message' );

/**
 * Show a message on the points logs admin panel when a type uses a custom meta key.
 *
 * @since 1.3.0
 *
 * @action wordpoints_admin_points_logs_tab
 *
 * @param string $points_type The type of points whose logs are being displayed.
 */
function wordpoints_points_logs_custom_meta_key_message( $points_type ) {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$custom_key = wordpoints_get_points_type_setting( $points_type, 'meta_key' );

	if ( ! empty( $custom_key ) ) {
		wordpoints_show_admin_error( esc_html( sprintf( __( 'This points type uses a custom meta key (&#8220;%s&#8221;). If this key is also used by another plugin, changes made by it will not be logged. Only transactions performed by WordPoints are included in the logs.', 'wordpoints' ), $custom_key ) ) );
	}
}
add_action( 'wordpoints_admin_points_logs_tab', 'wordpoints_points_logs_custom_meta_key_message' );

/**
 * Register the global cache groups used by this component.
 *
 * @since 1.5.0
 *
 * @action init 5 Earlier than the default so that the groups will be registered.
 */
function wordpoints_points_add_global_cache_groups() {

	wp_cache_add_global_groups( 'wordpoints_network_points_logs_query' );
}
add_action( 'init', 'wordpoints_points_add_global_cache_groups', 5 );

/**
 * Get the database schema for the points component.
 *
 * @since 1.5.1
 *
 * @return string CREATE TABLE queries that can be passed to dbDelta().
 */
function wordpoints_points_get_db_schema() {

	global $wpdb;

	$charset_collate = '';

	if ( ! empty( $wpdb->charset ) ) {
		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}

	if ( ! empty( $wpdb->collate ) ) {
		$charset_collate .= " COLLATE {$wpdb->collate}";
	}

	return "CREATE TABLE {$wpdb->wordpoints_points_logs} (
			id BIGINT(20) NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) NOT NULL,
			log_type VARCHAR(255) NOT NULL,
			points BIGINT(20) NOT NULL,
			points_type VARCHAR(255) NOT NULL,
			text LONGTEXT,
			blog_id SMALLINT(5) UNSIGNED NOT NULL,
			site_id SMALLINT(5) UNSIGNED NOT NULL,
			date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY points_type (points_type),
			KEY log_type (log_type)
		) {$charset_collate};
		CREATE TABLE {$wpdb->wordpoints_points_log_meta} (
			meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			log_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			meta_key VARCHAR(255) DEFAULT NULL,
			meta_value LONGTEXT,
			PRIMARY KEY  (meta_id),
			KEY log_id (log_id),
			KEY meta_key (meta_key)
		) {$charset_collate};";
}

// EOF
