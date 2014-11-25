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
		'1.8.0' => array( 'site' => true ),
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
	 * @since 1.8.0
	 */
	protected function uninstall_network() {

		$this->uninstall_ranks_main();
	}

	/**
	 * @since 1.8.0
	 */
	protected function uninstall_site() {

		global $wpdb;

		$options = $wpdb->get_col(
			"
				SELECT `option_name`
				FROM `{$wpdb->options}`
				WHERE `option_name` LIKE 'wordpoints_rank_group-%'
			"
		);

		foreach ( $options as $option ) {
			delete_option( $option );
		}
	}

	/**
	 * @since 1.8.0
	 */
	protected function uninstall_single() {

		$this->uninstall_ranks_main();
		$this->uninstall_site();
	}

	/**
	 * Uninstall the main portion of the ranks component.
	 *
	 * @since 1.8.0
	 */
	protected function uninstall_ranks_main() {

		global $wpdb;

		$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->wordpoints_ranks . '`' );
		$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->wordpoints_rankmeta . '`' );
		$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->wordpoints_user_ranks . '`' );
	}

	/**
	 * Update a site to 1.8.0.
	 *
	 * @since 1.8.0
	 */
	protected function update_site_to_1_8_0() {
		$this->add_installed_site_id();
	}
}

return 'WordPoints_Ranks_Un_Installer';

// EOF
