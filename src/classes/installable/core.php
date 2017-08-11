<?php

/**
 * Core installable class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Provides install, update, and uninstall routines for WordPoints core.
 *
 * @since 2.4.0
 */
class WordPoints_Installable_Core extends WordPoints_Installable {

	/**
	 * @since 2.4.0
	 */
	protected $slug = 'wordpoints';

	/**
	 * @since 2.4.0
	 */
	protected $type = 'plugin';

	/**
	 * @since 2.4.0
	 */
	protected $custom_caps_getter = 'wordpoints_get_custom_caps';

	/**
	 * @since 2.4.0
	 */
	public function get_version() {
		return WORDPOINTS_VERSION;
	}

	/**
	 * @since 2.4.0
	 */
	public function unset_db_version( $network = false ) {

		// No need to do anything, running this would actually set the DB version
		// again after it's already been deleted by the uninstall routine.
	}

	/**
	 * @since 2.4.0
	 */
	protected function get_db_tables() {
		return array(
			'global' => array(
				'wordpoints_hook_periods' => '
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					hit_id BIGINT(20) UNSIGNED NOT NULL,
					signature CHAR(64) NOT NULL,
					PRIMARY KEY  (id),
					KEY period_signature (hit_id,signature(8))',
				'wordpoints_hook_hits'    => '
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					action_type VARCHAR(255) NOT NULL,
					signature_arg_guids TEXT NOT NULL,
					event VARCHAR(255) NOT NULL,
					reactor VARCHAR(255) NOT NULL,
					reaction_mode VARCHAR(255) NOT NULL,
					reaction_store VARCHAR(255) NOT NULL,
					reaction_context_id TEXT NOT NULL,
					reaction_id BIGINT(20) UNSIGNED NOT NULL,
					date DATETIME NOT NULL,
					PRIMARY KEY  (id)',
				'wordpoints_hook_hitmeta' => '
					meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					wordpoints_hook_hit_id BIGINT(20) UNSIGNED NOT NULL,
					meta_key VARCHAR(255) NOT NULL,
					meta_value LONGTEXT,
					PRIMARY KEY  (meta_id),
					KEY hit_id (wordpoints_hook_hit_id),
					KEY meta_key (meta_key(191))',
			),
		);
	}

	/**
	 * @since 2.4.0
	 */
	public function get_install_routines() {

		$install_routines = parent::get_install_routines();

		$core = new WordPoints_Installer_Core();

		$install_routines['single'][] = $core;
		$install_routines['network'][] = $core;

		return $install_routines;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_update_routines() {

		$updates = parent::get_update_routines();

		$db_version = $this->get_db_version( is_wordpoints_network_active() );

		// v1.8.0.
		if ( version_compare( '1.8.0', $db_version, '>' ) ) {
			if ( ! is_wordpoints_network_active() ) {
				$updates['site'][] = new WordPoints_Updater_Installed_Site_ID_Add( $this );
			}
		}

		// v1.10.3.
		if ( version_compare( '1.10.3', $db_version, '>' ) ) {
			$routine = new WordPoints_Updater_Core_1_10_3_Extensions_Index_Create();
			$updates['single'][]  = $routine;
			$updates['network'][] = $routine;
		}

		// 2.1.0-alpha-3.
		if ( version_compare( '2.1.0-alpha-3', $db_version, '>' ) ) {

			$routines             = $this->get_db_tables_install_routines();
			$updates['single'][]  = $routines['single'][0];
			$updates['network'][] = $routines['network'][0];

		} elseif ( version_compare( '2.3.0-alpha-2', $db_version, '>' ) ) {

			// 2.3.0-alpha-2.
			// We don't need to update the table if the new schema has just been used to
			// install it.
			$routine = new WordPoints_Updater_DB_Table_Column_Rename(
				'wordpoints_hook_hits'
				, 'primary_arg_guid'
				, 'signature_arg_guids'
				, 'TEXT NOT NULL'
				, 'base'
			);
			$updates['single'][]  = $routine;
			$updates['network'][] = $routine;
		}

		// 2.4.0-alpha-2.
		if ( version_compare( '2.4.0-alpha-2', $db_version, '>' ) ) {

			$routine = new WordPoints_Updater_Core_Extension_Merge(
				'wordpointsorg/wordpointsorg.php'
			);

			$updates['single'][]  = $routine;
			$updates['network'][] = $routine;
			$updates['site'][]    = new WordPoints_Uninstaller_Callback(
				'wordpoints_deactivate_modules'
				, array( 'wordpointsorg/wordpointsorg.php' )
			);
		}

		// 2.4.0-alpha-3.
		if ( version_compare( '2.4.0-alpha-3', $db_version, '>' ) ) {

			$routine = new WordPoints_Installer_Caps( wordpoints_get_custom_caps() );
			$updates['single'][] = $routine;
			$updates['site'][]   = $routine;

			$routine = new WordPoints_Updater_Core_2_4_0_Extensions_Directory_Rename();
			$updates['single'][]  = $routine;
			$updates['network'][] = $routine;
		}

		return $updates;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_uninstall_routines() {

		// We'd do this in the components uninstaller, but we do it here for legacy
		// reasons, in case extensions expect it, since they are uninstalled first.
		WordPoints_Components::set_up();

		$uninstall_routines = array();

		// Normally we wouldn't run code here directly, but in this case we need to
		// for legacy reasons. In the future we'd be able to avoid this by bundling
		// the extension and component routines with our own.
		$extensions = new WordPoints_Uninstaller_Core_Extensions();
		$extensions->run();

		$components = new WordPoints_Uninstaller_Core_Components();
		$components->run();

		return array_merge_recursive(
			$uninstall_routines
			, parent::get_uninstall_routines()
		);
	}

	/**
	 * @since 2.4.0
	 */
	protected function get_uninstall_routine_factories() {

		$factories = parent::get_uninstall_routine_factories();

		$factories[] = new WordPoints_Uninstaller_Factory_Options(
			array(
				'network' => array(
					'wordpoints_sitewide_active_modules',
					'wordpoints_network_install_skipped',
					'wordpoints_network_installed',
					'wordpoints_network_update_skipped',
					'wordpoints_breaking_deactivated_modules',
				),
				'local'   => array(
					'wordpoints_active_modules',
					'wordpoints_recently_activated_modules',
				),
				'global' => array(
					'wordpoints_edd_sl_module_licenses',
					'wordpoints_edd_sl_module_info',
				),
				'universal' => array(
					'wordpoints_data',
					'wordpoints_active_components',
					'wordpoints_excluded_users',
					'wordpoints_incompatible_modules',
					'wordpoints_module_check_rand_str',
					'wordpoints_module_check_nonce',
					'wordpoints_hook_reaction-%',
					'wordpoints_hook_reaction_index-%',
					'wordpoints_hook_reaction_last_id-%',
					'wordpoints_installable_versions',
				),
			)
		);

		$factories[] = new WordPoints_Uninstaller_Factory_Transients(
			array( 'global' => array( 'wordpoints_extension_updates' ) )
		);

		$factories[] = new WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables(
			array(
				'universal' => array(
					'wordpoints_extensions' => array(),
					'wordpoints_modules' => array(),
				),
			)
		);

		return $factories;
	}
}

// EOF
