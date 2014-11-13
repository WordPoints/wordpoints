<?php

/**
 * Class for un/installing the points component.
 *
 * @package WordPoints
 * @since 1.8.0
 */

/**
 * Un/installs the points component.
 *
 * @since 1.8.0
 */
class WordPoints_Points_Un_Installer extends WordPoints_Un_Installer_Base {

	//
	// Protected Vars.
	//

	/**
	 * @since 1.8.0
	 */
	protected $option_prefix = 'wordpoints_points_';

	/**
	 * The points types the user has created.
	 *
	 * Used during uninstall to keep from having to retreive them when looping over
	 * sites on multisite.
	 *
	 * @since 1.8.0
	 *
	 * @type array $points_types
	 */
	protected $points_types;

	/**
	 * The component's capabilities.
	 *
	 * Used to hold the list of capabilities during install and uninstall, so that
	 * they don't have to be retrieved all over again for each site (if multisite).
	 *
	 * @since 1.8.0
	 *
	 * @type array $custom_caps
	 */
	protected $custom_caps;

	/**
	 * The component's capabilities (keys only).
	 *
	 * Used to hold the list of capabilities during install and uninstall, so that
	 * they don't have to be retrieved all over again for each site (if multisite).
	 *
	 * @since 1.8.0
	 *
	 * @type array $custom_caps_keys
	 */
	protected $custom_caps_keys;

	/**
	 * @since 1.8.0
	 */
	public function before_install() {

		$this->custom_caps = wordpoints_points_get_custom_caps();
		$this->custom_caps_keys = array_keys( $this->custom_caps );
	}

	/**
	 * @since 1.8.0
	 */
	protected function before_uninstall() {

		$this->points_types = wordpoints_get_points_types();
		$this->custom_caps_keys = array_keys( wordpoints_points_get_custom_caps() );
	}

	/**
	 * @since 1.8.0
	 */
	protected function install_network() {

		$this->install_points_main();
	}

	/**
	 * @since 1.8.0
	 */
	protected function install_site() {

		/*
		 * Regenerate the custom caps every time on multisite, because they depend on
		 * network activation status.
		 */
		wordpoints_remove_custom_caps( $this->custom_caps_keys );
		wordpoints_add_custom_caps( $this->custom_caps );
	}

	/**
	 * @since 1.8.0
	 */
	protected function install_single() {

		wordpoints_add_custom_caps( $this->custom_caps );
		add_option( 'wordpoints_default_points_type', '' );

		$this->install_points_main();
	}

	/**
	 * Install the main portion of the points component.
	 *
	 * @since 1.8.0
	 */
	protected function install_points_main() {

		dbDelta( wordpoints_points_get_db_schema() );

		$wordpoints_data = wordpoints_get_array_option( 'wordpoints_data', 'network' );

		if ( empty( $wordpoints_data['components']['points']['version'] ) ) {
			$wordpoints_data['components']['points']['version'] = WORDPOINTS_VERSION;
		}

		wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
	}

	/**
	 * @since 1.8.0
	 */
	protected function load_dependencies() {

		require_once WORDPOINTS_DIR . '/components/points/includes/functions.php';
		require_once WORDPOINTS_DIR . '/components/points/includes/constants.php';
	}

	/**
	 * @since 1.8.0
	 */
	protected function uninstall_network() {

		$this->uninstall_points_main();

		delete_site_option( 'wordpoints_points_types' );
		delete_site_option( 'wordpoints_default_points_type' );
		delete_site_option( 'wordpoints_points_types_hooks' );
	}

	/**
	 * @since 1.8.0
	 */
	protected function uninstall_site() {

		global $wpdb;

		foreach ( $this->points_types as $slug => $settings ) {

			delete_metadata( 'comment', 0, "wordpoints_last_status-{$slug}", '', true );

			$prefix = $wpdb->get_blog_prefix();
			delete_metadata( 'user', 0, $prefix . "wordpoints_points-{$slug}", '', true );
			delete_metadata( 'user', 0, $prefix . 'wordpoints_points_period_start', '', true );
		}

		$this->uninstall_points_single();
	}

	/**
	 * @since 1.8.0
	 */
	protected function uninstall_single() {

		$this->uninstall_points_main();
		$this->uninstall_points_single();
	}

	/**
	 * Uninstall the main portion of the points component.
	 *
	 * @since 1.8.0
	 */
	protected function uninstall_points_main() {

		global $wpdb;

		$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->wordpoints_points_logs . '`' );
		$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->wordpoints_points_log_meta . '`' );

		foreach ( $this->points_types as $slug => $settings ) {

			delete_metadata( 'user', 0, "wordpoints_points-{$slug}", '', true );
		}

		delete_metadata( 'user', 0, 'wordpoints_points_period_start', '', true );
	}

	/**
	 * Uninstall the points component from a single site/site on a network.
	 *
	 * @since 1.8.0
	 */
	protected function uninstall_points_single() {

		delete_option( 'wordpoints_points_types' );
		delete_option( 'wordpoints_default_points_type' );
		delete_option( 'wordpoints_points_types_hooks' );

		delete_option( 'wordpoints_hook-wordpoints_registration_points_hook' );
		delete_option( 'wordpoints_hook-wordpoints_post_points_hook' );
		delete_option( 'wordpoints_hook-wordpoints_comment_points_hook' );
		delete_option( 'wordpoints_hook-wordpoints_periodic_points_hook' );

		delete_option( 'widget_wordpoints_points_logs_widget' );
		delete_option( 'widget_wordpoints_top_users_widget' );
		delete_option( 'widget_wordpoints_points_widget' );

		wordpoints_remove_custom_caps( $this->custom_caps_keys );
	}
}

return 'WordPoints_Points_Un_Installer';

// EOF
