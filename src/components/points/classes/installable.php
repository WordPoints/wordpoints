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
	public function get_custom_caps() {
		return wordpoints_points_get_custom_caps();
	}

	/**
	 * @since 2.4.0
	 */
	public function get_install_routines() {

		$install_routines = parent::get_install_routines();

		$option = 'wordpoints_legacy_points_hooks_disabled';
		$value  = array(
			'wordpoints_post_points_hook'             => true,
			'wordpoints_comment_points_hook'          => true,
			'wordpoints_comment_received_points_hook' => true,
			'wordpoints_periodic_points_hook'         => true,
			'wordpoints_registration_points_hook'     => true,
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
	public function get_update_routine_factories() {

		$factories = parent::get_update_routine_factories();

		// v1.2.0.
		$factories[] = new WordPoints_Updater_Factory(
			'1.2.0'
			, array( 'global' => array( 'WordPoints_Points_Updater_1_2_0_Logs' ) )
		);

		// v1.4.0.
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

		$factories[] = new WordPoints_Updater_Factory(
			'1.4.0'
			, array(
				'local' => array(
					array(
						'class' => 'WordPoints_Points_Updater_1_4_0_Hooks',
						'args'  => array( $hooks ),
					),
					'WordPoints_Points_Updater_1_4_0_Logs',
				),
			)
		);

		if ( is_wordpoints_network_active() ) {
			$factories[] = new WordPoints_Updater_Factory(
				'1.4.0'
				, array(
					'network' => array(
						array(
							'class' => 'WordPoints_Points_Updater_1_4_0_Hooks',
							'args'  => array( $hooks, true ),
						),
					),
				)
			);
		}

		// v1.5.0.
		if ( is_wordpoints_network_active() ) {
			$factories[] = new WordPoints_Updater_Factory(
				'1.5.0'
				, array(
					'site' => array(
						array(
							'class' => 'WordPoints_Installer_Caps',
							'args'  => array( wordpoints_points_get_custom_caps() ),
						),
					),
				)
			);
		}

		// v1.9.0.
		if ( version_compare( '1.9.0', $this->get_db_version( is_wordpoints_network_active() ), '>' ) ) {

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
		}

		$hooks = array(
			'comment' => 'comment_removed',
			'post'    => 'post_delete',
		);

		$factories[] = new WordPoints_Updater_Factory(
			'1.9.0'
			, array(
				'local' => array(
					array(
						'class' => 'WordPoints_Points_Updater_1_9_0_Hooks',
						'args'  => array( $hooks ),
					),
				),
			)
		);

		if ( is_wordpoints_network_active() ) {
			$factories[] = new WordPoints_Updater_Factory(
				'1.9.0'
				, array(
					'network' => array(
						array(
							'class' => 'WordPoints_Points_Updater_1_9_0_Hooks',
							'args'  => array( $hooks, true ),
						),
					),
				)
			);
		}

		// v1.10.0.
		$factories[] = new WordPoints_Updater_Factory(
			'1.10.0'
			, array(
				'global' => array(
					array(
						'class' => 'WordPoints_Points_Updater_1_10_0_Logs',
						'args'  => array( is_multisite() ),
					),
				),
			)
		);

		// v2.0.0.
		$factories[] = new WordPoints_Updater_Factory(
			'2.0.0'
			, array(
				'global' => array(
					array(
						'class' => 'WordPoints_Points_Updater_2_0_0_Tables',
						'args'  => array(
							array(
								'wordpoints_points_logs',
								'wordpoints_points_log_meta',
							),
							'base',
						),
					),
				),
			)
		);

		// v2.1.0.
		$factories[] = new WordPoints_Updater_Factory(
			'2.1.0'
			, array(
				'global' => array(
					array(
						'class' => 'WordPoints_Installer_Option',
						'args'  => array(
							'wordpoints_disabled_points_hooks_edit_points_types',
							true,
							null,
						),
					),
				),
			)
		);

		// v2.1.4.
		$factories[] = new WordPoints_Updater_Factory(
			'2.1.4'
			, array( 'global' => array( 'WordPoints_Points_Updater_2_1_4_Logs' ) )
		);

		// 2.4.0-alpha-5.
		$entity_slugs = array( 'user' );

		foreach ( wordpoints_get_post_types_for_auto_integration() as $post_type ) {
			$entity_slugs[] = "post\\{$post_type}";
			$entity_slugs[] = "comment\\{$post_type}";
		}

		$factories[] = new WordPoints_Updater_Factory(
			'2.4.0-alpha-5'
			, array(
				'global' => array(
					array(
						'class' => 'WordPoints_Points_Updater_Log_Meta_Entity_GUIDs_Int',
						'args'  => array( $entity_slugs ),
					),
				),
				'universal' => array(
					'WordPoints_Points_Updater_2_4_0_Condition_Contains',
				),
			)
		);

		if ( is_wordpoints_network_active() ) {
			$factories[] = new WordPoints_Updater_Factory(
				'2.4.0-alpha-5'
				, array(
					'site' => array(
						'WordPoints_Points_Updater_2_4_0_Reactions_Orphaned',
					),
				)
			);
		}

		return $factories;
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
					'wordpoints_last_status',
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
