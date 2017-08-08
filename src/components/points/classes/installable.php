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
	protected function get_db_tables() {
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
}

// EOF
