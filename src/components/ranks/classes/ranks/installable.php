<?php

/**
 * Ranks installable class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Provides install, update, and uninstall routines for the ranks component.
 *
 * @since 2.4.0
 */
class WordPoints_Ranks_Installable extends WordPoints_Installable_Component {

	/**
	 * @since 2.4.0
	 */
	protected $slug = 'ranks';

	/**
	 * @since 2.4.0
	 */
	protected function get_db_tables() {
		return array(
			'global' => array(
				'wordpoints_ranks' => '
					id BIGINT(20) NOT NULL AUTO_INCREMENT,
					name VARCHAR(255) NOT NULL,
					type VARCHAR(255) NOT NULL,
					rank_group VARCHAR(255) NOT NULL,
					blog_id SMALLINT(5) UNSIGNED NOT NULL,
					site_id SMALLINT(5) UNSIGNED NOT NULL,
					PRIMARY KEY  (id),
					KEY type (type(191)),
					KEY site (blog_id,site_id)',
				'wordpoints_rankmeta' => '
					meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					wordpoints_rank_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
					meta_key VARCHAR(255) DEFAULT NULL,
					meta_value LONGTEXT,
					PRIMARY KEY  (meta_id),
					KEY wordpoints_rank_id (wordpoints_rank_id)',
				'wordpoints_user_ranks' => '
					id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					user_id BIGINT(20) UNSIGNED NOT NULL,
					rank_id BIGINT(20) UNSIGNED NOT NULL,
					rank_group VARCHAR(255) NOT NULL,
					blog_id BIGINT(20) UNSIGNED NOT NULL,
					site_id BIGINT(20) UNSIGNED NOT NULL,
					PRIMARY KEY  (id),
					UNIQUE KEY (user_id,blog_id,site_id,rank_group(185))',
			),
		);
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

		// v2.0.0.
		if ( version_compare( '2.0.0', $db_version, '>' ) ) {

			$routine = new WordPoints_Ranks_Updater_2_0_0_Tables(
				array(
					'wordpoints_ranks',
					'wordpoints_rankmeta',
					'wordpoints_user_ranks',
				)
				, 'base'
			);

			$updates['single'][]  = $routine;
			$updates['network'][] = $routine;
		}

		// v2.4.0-alpha-4.
		if ( version_compare( '2.4.0-alpha-4', $db_version, '>' ) ) {

			$routine = new WordPoints_Ranks_Updater_2_4_0_Tables();
			$updates['single'][]  = $routine;
			$updates['network'][] = $routine;

			$routine = new WordPoints_Ranks_Updater_2_4_0_User_Ranks();
			$updates['single'][] = $routine;
			$updates['site'][]   = $routine;
		}

		return $updates;
	}

	/**
	 * @since 2.4.0
	 */
	protected function get_uninstall_routine_factories() {

		include_once( WORDPOINTS_DIR . 'components/ranks/includes/constants.php' );

		$factories = parent::get_uninstall_routine_factories();

		$factories[] = new WordPoints_Uninstaller_Factory_Options(
			array(
				'local' => array(
					'wordpoints_filled_base_ranks',
					'wordpoints_rank_group-%',
				),
			)
		);

		return $factories;
	}
}

// EOF
