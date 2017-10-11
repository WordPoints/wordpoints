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
					UNIQUE KEY user_rank (user_id,blog_id,site_id,rank_group(185))',
			),
		);
	}

	/**
	 * @since 2.4.0
	 */
	public function get_update_routine_factories() {

		$factories = parent::get_update_routine_factories();

		// v2.0.0.
		$factories[] = new WordPoints_Updater_Factory(
			'2.0.0'
			, array(
				'global' => array(
					array(
						'class' => 'WordPoints_Ranks_Updater_2_0_0_Tables',
						'args'  => array(
							array(
								'wordpoints_ranks',
								'wordpoints_rankmeta',
								'wordpoints_user_ranks',
							),
							'base',
						),
					),
				),
			)
		);

		// v2.4.0-alpha-4.
		$factories[] = new WordPoints_Updater_Factory(
			'2.4.0-alpha-4'
			, array( 'global' => array( 'WordPoints_Ranks_Updater_2_4_0_Tables' ) )
		);

		$factories[] = new WordPoints_Updater_Factory(
			'2.4.0-alpha-4'
			, array(
				'local' => array( 'WordPoints_Ranks_Updater_2_4_0_User_Ranks' ),
			)
		);

		return $factories;
	}

	/**
	 * @since 2.4.0
	 */
	protected function get_uninstall_routine_factories() {

		require_once WORDPOINTS_DIR . 'components/ranks/includes/constants.php';

		$factories = parent::get_uninstall_routine_factories();

		$factories[] = new WordPoints_Uninstaller_Factory_Options(
			array(
				'local' => array(
					'wordpoints_filled_base_ranks',
					'wordpoints_rank_group-%',
					'wordpoints_ranks_2_4_0_update_duplicates',
				),
			)
		);

		return $factories;
	}
}

// EOF
