<?php

/**
 * Bulk extension upgrader skin class.
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
 * Bulk WordPoints extension upgrader skin.
 *
 * @since 2.4.0
 */
class WordPoints_Extension_Upgrader_Skin_Bulk extends Bulk_Upgrader_Skin {

	/**
	 * The extension data.
	 *
	 * This is filled in by WordPoints_Extension_Upgrader::bulk_upgrade().
	 *
	 * @since 2.4.0
	 *
	 * @type array $extension_info
	 */
	protected $extension_info = array();

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
	 * Sets the extension currently being upgraded.
	 *
	 * @since 2.4.0
	 *
	 * @param string $extension_file The basename path of the extension file.
	 */
	public function set_extension( $extension_file ) {

		$this->extension_info = wordpoints_get_module_data(
			wordpoints_modules_dir() . $extension_file
		);
	}

	/**
	 * @since 2.4.0
	 */
	public function before( $title = '' ) {

		parent::before( $this->extension_info['name'] );
	}

	/**
	 * @since 2.4.0
	 */
	public function after( $title = '' ) {

		parent::after( $this->extension_info['name'] );
	}

	/**
	 * Displays the footer.
	 *
	 * @since 2.4.0
	 */
	public function bulk_footer() {

		parent::bulk_footer();

		$update_actions = array(
			'extensions_page' => '<a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_modules' ) ) . '" target="_parent">' . esc_html__( 'Return to Extensions page', 'wordpoints' ) . '</a>',
			'updates_page'    => '<a href="' . esc_url( self_admin_url( 'update-core.php' ) ) . '" target="_parent">' . esc_html__( 'Return to WordPress Updates', 'wordpoints' ) . '</a>',
		);

		if ( ! current_user_can( 'activate_wordpoints_modules' ) ) {
			unset( $update_actions['extensions_page'] );
		}

		/**
		 * The action links for the bulk extension update footer.
		 *
		 * @since 2.4.0
		 *
		 * @param array $update_actions {
		 *        HTML for links to appear in the bulk extension updates footer.
		 *
		 *        @type string $extensions_page Go to the extensions page. Not available if
		 *                                      the user doesn't have the
		 *                                      'activate_wordpoints_modules' capability.
		 *        @type string $updates_page    Go to the WordPress updates page.
		 * }
		 * @param array $extension_info The extension's data.
		 */
		$update_actions = apply_filters( 'wordpoints_bulk_update_extensions_complete_actions', $update_actions, $this->extension_info );

		if ( ! empty( $update_actions ) ) {
			$this->feedback( implode( ' | ', (array) $update_actions ) );
		}
	}

} // End class WordPoints_Extension_Upgrader_Skin_Bulk.

// EOF
