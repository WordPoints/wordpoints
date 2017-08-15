<?php

/**
 * Points installable class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Provides install, update, and uninstall routines for the points component.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Installable extends WordPoints_Installable_Component {

	/**
	 * @since 2.4.0
	 */
	protected $slug = 'points';

	/**
	 * @since 2.4.0
	 */
	protected $custom_caps_getter = 'wordpoints_points_get_custom_caps';

	/**
	 * @since 2.4.0
	 */
	public function get_db_tables() {
		return array(
			'global' => array(
				'wordpoints_points_logs' => "
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
					KEY points_type (points_type(191)),
					KEY log_type (log_type(191))",
				'wordpoints_points_log_meta' => '
					meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					log_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
					meta_key VARCHAR(255) DEFAULT NULL,
					meta_value LONGTEXT,
					PRIMARY KEY  (meta_id),
					KEY log_id (log_id),
					KEY meta_key (meta_key(191))',
			),
		);
	}

	/**
	 * @since 2.4.0
	 */
	public function get_install_routines() {

		$install_routines = parent::get_install_routines();

		$option = 'wordpoints_legacy_points_hooks_disabled';
		$value  = array(
			'wordpoints_post_points_hook' => true,
			'wordpoints_comment_points_hook' => true,
			'wordpoints_comment_received_points_hook' => true,
			'wordpoints_periodic_points_hook' => true,
			'wordpoints_registration_points_hook' => true,
		);

		$routine = new WordPoints_Installer_Option( $option, $value );

		$install_routines['single'][]  = $routine;
		$install_routines['site'][]    = $routine;
		$install_routines['network'][] = new WordPoints_Installer_Option(
			$option
			, $value
			, true
		);

		return $install_routines;
	}

	/**
	 * @since 2.4.0
	 */
	protected function get_custom_caps_install_routines() {

		$install_routines = parent::get_custom_caps_install_routines();

		/*
		 * Regenerate the custom caps every time on multisite, because they depend on
		 * network activation status.
		 */
		array_unshift(
			$install_routines['site']
			, new WordPoints_Uninstaller_Caps(
				array( 'manage_wordpoints_points_types' )
			)
		);

		return $install_routines;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_update_routines() {

		$updates = parent::get_update_routines();

		$db_version = $this->get_db_version( is_wordpoints_network_active() );

		// v1.2.0.
		if ( version_compare( '1.2.0', $db_version, '>' ) ) {
			$routine              = new WordPoints_Points_Updater_1_2_0_Logs();
			$updates['single'][]  = $routine;
			$updates['network'][] = $routine;
		}

		// v1.4.0.
		if ( version_compare( '1.4.0', $db_version, '>' ) ) {

			$hooks = array(
				array(
					'hook'      => 'wordpoints_post_points_hook',
					'new_hook'  => 'wordpoints_post_delete_points_hook',
					'key'       => 'publish',
					'split_key' => 'trash',
				),
				array(
					'hook'      => 'wordpoints_comment_points_hook',
					'new_hook'  => 'wordpoints_comment_removed_points_hook',
					'key'       => 'approve',
					'split_key' => 'disapprove',
				),
			);

			$routine = new WordPoints_Points_Updater_1_4_0_Hooks( $hooks );

			$updates['single'][] = $routine;
			$updates['site'][]   = $routine;

			if ( is_wordpoints_network_active() ) {
				$updates['network'][] = new WordPoints_Points_Updater_1_4_0_Hooks(
					$hooks
					, true
				);
			}

			$routine = new WordPoints_Points_Updater_1_4_0_Logs();
			$updates['single'][] = $routine;
			$updates['site'][]   = $routine;
		}

		// v1.5.0.
		if ( version_compare( '1.5.0', $db_version, '>' ) ) {
			if ( ! is_wordpoints_network_active() ) {
				$updates['site'][] = new WordPoints_Installer_Caps(
					wordpoints_points_get_custom_caps()
				);
			}
		}

		// v1.9.0.
		if ( version_compare( '1.9.0', $db_version, '>' ) ) {

			// We initialize the hooks early, because we use them during the update.
			// This is also need for the 1.4.0 update, but if that update is running,
			// this one will too, so no need for duplication.
			remove_action( 'wordpoints_extensions_loaded', array( 'WordPoints_Points_Hooks', 'initialize_hooks' ) );

			WordPoints_Points_Hooks::register(
				'WordPoints_Comment_Removed_Points_Hook'
			);

			WordPoints_Points_Hooks::register(
				'WordPoints_Post_Delete_Points_Hook'
			);

			WordPoints_Points_Hooks::initialize_hooks();

			$hooks = array(
				'comment' => 'comment_removed',
				'post' => 'post_delete',
			);

			$routine = new WordPoints_Points_Updater_1_9_0_Hooks( $hooks );

			$updates['single'][] = $routine;
			$updates['site'][]   = $routine;

			if ( is_wordpoints_network_active() ) {
				$updates['network'][] = new WordPoints_Points_Updater_1_9_0_Hooks(
					$hooks
					, true
				);
			}
		}

		// v1.10.0.
		if ( version_compare( '1.10.0', $db_version, '>' ) ) {

			$updates['single'][]  = new WordPoints_Points_Updater_1_10_0_Logs();
			$updates['network'][] = new WordPoints_Points_Updater_1_10_0_Logs( true );
		}

		// v2.0.0.
		if ( version_compare( '2.0.0', $db_version, '>' ) ) {

			$routine = new WordPoints_Points_Updater_2_0_0_Tables(
				array( 'wordpoints_points_logs', 'wordpoints_points_log_meta' )
				, 'base'
			);

			$updates['single'][]  = $routine;
			$updates['network'][] = $routine;
		}

		// v2.1.0.
		if ( version_compare( '2.1.0', $db_version, '>' ) ) {

			$routine = new WordPoints_Installer_Option(
				'wordpoints_disabled_points_hooks_edit_points_types'
				, true
				, null
			);

			$updates['single'][]  = $routine;
			$updates['network'][] = $routine;
		}

		// v2.1.4.
		if ( version_compare( '2.1.4', $db_version, '>' ) ) {

			$routine              = new WordPoints_Points_Updater_2_1_4_Logs();
			$updates['single'][]  = $routine;
			$updates['network'][] = $routine;
		}

		return $updates;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_uninstall_routines() {

		// The functions.php is needed for the custom caps getter function. The
		// others are loaded for legacy reasons and for the sake of extensions.
		require_once WORDPOINTS_DIR . '/components/points/includes/constants.php';
		require_once WORDPOINTS_DIR . '/components/points/includes/functions.php';
		require_once WORDPOINTS_DIR . '/components/points/includes/points.php';

		return parent::get_uninstall_routines();
	}

	/**
	 * @since 2.4.0
	 */
	protected function get_uninstall_routine_factories() {

		$factories = parent::get_uninstall_routine_factories();

		$factories[] = new WordPoints_Uninstaller_Factory_Options(
			array(
				'local' => array(
					'wordpoints_%_hook_legacy',
				),
				'global' => array(
					'wordpoints_disabled_points_hooks_edit_points_types',
				),
				'universal' => array(
					'wordpoints_points_types',
					'wordpoints_default_points_type',
					'wordpoints_points_types_hooks',
					'wordpoints_legacy_points_hooks_disabled',
					'wordpoints_imported_points_hooks',
				),
			)
		);

		$factories[] = new WordPoints_Points_Uninstaller_Factory_Points_Hooks(
			array(
				'universal' => array(
					'wordpoints_registration_points_hook',
					'wordpoints_post_points_hook',
					'wordpoints_post_delete_points_hook',
					'wordpoints_comment_points_hook',
					'wordpoints_comment_removed_points_hook',
					'wordpoints_periodic_points_hook',
					'wordpoints_comment_received_points_hook',
				),
			)
		);

		$factories[] = new WordPoints_Uninstaller_Factory_Widgets(
			array(
				'wordpoints_points_logs_widget',
				'wordpoints_top_users_widget',
				'wordpoints_points_widget',
			)
		);

		$factories[] = new WordPoints_Uninstaller_Factory_Metadata(
			'user'
			, array(
				'universal' => array(
					'wordpoints_points_period_start',
					'wordpoints_points-%',
				),
			)
		);

		$factories[] = new WordPoints_Uninstaller_Factory_Metadata(
			'comment'
			, array(
				'universal' => array(
					'wordpoints_last_status-%',
				),
			)
		);

		$factories[] = new WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes(
			array(
				'universal' => array(
					'wordpoints_points_types' => array(),
				),
			)
		);

		return $factories;
	}
}

// EOF
