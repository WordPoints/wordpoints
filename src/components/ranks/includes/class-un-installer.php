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
	 * @since 1.8.0
	 */
	protected $option_prefix = 'wordpoints_ranks_';

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
	protected $uninstall = array(
		'local' => array(
			'options' => array(
				'wordpoints_rank_group-%',
			),
		),
		'global' => array(
			'tables' => array(
				'wordpoints_ranks',
				'wordpoints_rankmeta',
				'wordpoints_user_ranks',
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
	protected function install_network() {

		$this->install_ranks_main();
	}

	/**
	 * @since 1.8.0
	 */
	protected function install_site() {}

	/**
	 * @since 1.8.0
	 */
	protected function install_single() {

		$this->install_ranks_main();
	}

	/**
	 * Install the main portion of the points component.
	 *
	 * @since 1.8.0
	 */
	protected function install_ranks_main() {

		dbDelta( wordpoints_ranks_get_db_schema() );

		$this->set_component_version( 'ranks', WORDPOINTS_VERSION );
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
		);

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
