<?php

/**
 * Class for un/installing the ranks component.
 *
 * @package WordPoints
 * @since 1.8.0
 */

/**
 * Un/installs the ranks component.
 *
 * @since 1.8.0
 */
class WordPoints_Ranks_Un_Installer extends WordPoints_Un_Installer_Base {

	//
	// Protected Vars.
	//

	/**
	 * @since 2.0.0
	 */
	protected $type = 'component';

	/**
	 * @since 1.8.0
	 */
	protected $updates = array(
		'1.8.0' => array( /*      -      */ 'site' => true, /*      -      */ ),
		'2.0.0' => array( 'single' => true, /*     -     */ 'network' => true ),
	);

	/**
	 * @since 2.0.0
	 */
	protected $schema = array(
		'global' => array(
			'tables' => array(
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
					user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
					rank_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
					PRIMARY KEY  (id)',
			),
		),
	);

	/**
	 * @since 2.0.0
	 */
	protected $uninstall = array(
		'local' => array(
			'options' => array(
				'wordpoints_rank_group-%',
			),
		),
	);

	/**
	 * @since 1.8.0
	 */
	protected function before_update() {

		if ( $this->network_wide ) {
			unset( $this->updates['1_8_0'] );
		}
	}

	/**
	 * @since 1.8.0
	 */
	protected function do_per_site_install() {

		return false;
	}

	/**
	 * @since 1.8.0
	 */
	protected function load_dependencies() {

		include_once( WORDPOINTS_DIR . 'components/ranks/includes/constants.php' );
	}

	/**
	 * Update a site to 1.8.0.
	 *
	 * @since 1.8.0
	 */
	protected function update_site_to_1_8_0() {
		$this->add_installed_site_id();
	}


	/**
	 * Update a site to 2.0.0.
	 *
	 * @since 2.0.0
	 */
	protected function update_network_to_2_0_0() {

		global $wpdb;

		// So that we can change tables to utf8mb4, we need to shorten the index
		// lengths to less than 767 bytes;
		$wpdb->query(
			"
			ALTER TABLE {$wpdb->wordpoints_ranks}
			DROP INDEX type,
			ADD INDEX type(type(191))
			"
		); // WPCS: cache pass.

		$this->maybe_update_tables_to_utf8mb4( 'global' );
	}

	/**
	 * Update a single site to 2.0.0.
	 *
	 * @since 2.0.0
	 */
	protected function update_single_to_2_0_0() {
		$this->update_network_to_2_0_0();
	}
}

return 'WordPoints_Ranks_Un_Installer';

// EOF
