<?php

/**
 * Bulk module upgrader skin class.
 *
 * @package WordPoints
 * @since 2.4.0
 */

/**
 * The WordPress upgrader classes.
 *
 * @since 2.4.0
 */
require_once( ABSPATH . '/wp-admin/includes/class-wp-upgrader.php' );

/**
 * Bulk WordPoints module upgrader skin.
 *
 * @since 2.4.0
 */
class WordPoints_Module_Upgrader_Skin_Bulk extends Bulk_Upgrader_Skin {

	/**
	 * The module data.
	 *
	 * This is filled in by WordPoints_Module_Upgrader::bulk_upgrade().
	 *
	 * @since 2.4.0
	 *
	 * @type array $module_info
	 */
	protected $module_info = array();

	/**
	 * Add the string's skins.
	 *
	 * @since 2.4.0
	 */
	public function add_strings() {

		parent::add_strings();

		// translators: 1. Extension Name; 2. Count of update; 3. Total number of updates being installed.
		$this->upgrader->strings['skin_before_update_header'] = __( 'Updating Extension %1$s (%2$d/%3$d)', 'wordpoints' );
	}

	/**
	 * Sets the module currently being upgraded.
	 *
	 * @since 2.4.0
	 *
	 * @param string $module_file The basename path of the module file.
	 */
	public function set_module( $module_file ) {

		$this->module_info = wordpoints_get_module_data(
			wordpoints_modules_dir() . $module_file
		);
	}

	/**
	 * @since 2.4.0
	 */
	public function before( $title = '' ) {

		parent::before( $this->module_info['name'] );
	}

	/**
	 * @since 2.4.0
	 */
	public function after( $title = '' ) {

		parent::after( $this->module_info['name'] );
	}

	/**
	 * Displays the footer.
	 *
	 * @since 2.4.0
	 */
	public function bulk_footer() {

		parent::bulk_footer();

		$update_actions = array(
			'modules_page' => '<a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_modules' ) ) . '" target="_parent">' . esc_html__( 'Return to Extensions page', 'wordpoints' ) . '</a>',
			'updates_page' => '<a href="' . esc_url( self_admin_url( 'update-core.php' ) ) . '" target="_parent">' . esc_html__( 'Return to WordPress Updates', 'wordpoints' ) . '</a>',
		);

		if ( ! current_user_can( 'activate_wordpoints_modules' ) ) {
			unset( $update_actions['modules_page'] );
		}

		/**
		 * The action links for the bulk module update footer.
		 *
		 * @since 2.4.0
		 *
		 * @param array $update_actions {
		 *        HTML for links to appear in the bulk module updates footer.
		 *
		 *        @type string $modules_page Go to the modules page. Not available if
		 *                                   the user doesn't have the
		 *                                   'activate_wordpoints_modules' capability.
		 *        @type string $updates_page Go to the WordPress updates page.
		 * }
		 * @param array $module_info The module's data.
		 */
		$update_actions = apply_filters( 'wordpoints_bulk_update_modules_complete_actions', $update_actions, $this->module_info );

		if ( ! empty( $update_actions ) ) {
			$this->feedback( implode( ' | ', (array) $update_actions ) );
		}
	}

} // End class WordPoints_Module_Upgrader_Skin_Bulk.

// EOF
