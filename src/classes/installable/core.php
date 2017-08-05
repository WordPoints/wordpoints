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
	public function get_uninstall_routines() {

		// We'd do this in the components uninstaller, but we do it here for legacy
		// reasons, in case extensions expect it, since they are uninstalled first.
		WordPoints_Components::set_up();

		$uninstall_routines = array();

		$extensions = new WordPoints_Uninstaller_Core_Extensions();

		$uninstall_routines['single'][] = $extensions;
		$uninstall_routines['network'][] = $extensions;

		$components = new WordPoints_Uninstaller_Core_Components();

		$uninstall_routines['single'][] = $components;
		$uninstall_routines['network'][] = $components;

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
