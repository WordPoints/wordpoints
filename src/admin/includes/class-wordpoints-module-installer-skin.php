<?php

/**
 * WordPoints module install skin.
 *
 * @package WordPoints\Modules
 * @since 1.1.0
 */

/**
 * The WordPress upgrader and skins.
 */
require_once ABSPATH . '/wp-admin/includes/class-wp-upgrader.php';

/**
 * WordPoints module installer skin.
 *
 * @since 1.1.0
 */
class WordPoints_Module_Installer_Skin extends WP_Upgrader_Skin {

	//
	// Public Vars.
	//

	/**
	 * The type of install (currently only 'upload' is an option).
	 *
	 * @since 1.1.0
	 *
	 * @type string $type
	 */
	public $type;

	//
	// Public Methods.
	//

	/**
	 * Construct the skin.
	 *
	 * @since 1.1.0
	 *
	 * @param array $args {
	 *        The arguments for the skin.
	 *
	 *        @type string $type   The type of install, 'upload' by default.
	 *        @type string $url    The form URL passed to request_filesystem_credentials() if needed.
	 *        @type string $nonce  An nonce to be added to the $url before it is passed torequest_filesystem_credentials().
	 *        @type string $title  Text for H2 title used by WP_Upgrader_Skin::header().
	 * }
	 */
	public function __construct( $args = array() ) {

		$defaults = array(
			'type'   => 'upload',
			'url'    => '',
			'nonce'  => '',
			'title'  => '',
		);

		$args = array_merge( $defaults, $args );

		$this->type = $args['type'];

		parent::__construct( $args );
	}

	/**
	 * Called after install.
	 *
	 * @since 1.1.0
	 */
	public function after() {

		$module_file = $this->upgrader->module_info();

		$install_actions = array();

		if ( $this->result && ! is_wp_error( $this->result ) ) {

			if ( is_multisite() && current_user_can( 'manage_network_wordpoints_modules' ) ) {

				$install_actions['network_activate'] = '<a href="' . wp_nonce_url( 'admin.php?page=wordpoints_modules&action=activate&amp;networkwide=1&amp;module=' . urlencode( $module_file ), "activate-module_{$module_file}" ) . '" target="_parent">' . esc_html__( 'Network Activate', 'wordpoints' ) . '</a>';

			} elseif ( current_user_can( 'activate_wordpoints_modules' ) ) {

				$install_actions['activate_module'] = '<a href="' . wp_nonce_url( 'admin.php?page=wordpoints_modules&action=activate&amp;module=' . urlencode( $module_file ), "activate-module_{$module_file}" ) . '" target="_parent">' . esc_html__( 'Activate Module', 'wordpoints' ) . '</a>';
			}
		}

		$install_actions['modules_page'] = '<a href="' . esc_attr( esc_url( self_admin_url( 'admin.php?page=wordpoints_modules' ) ) ) . '" target="_parent">' . esc_html__( 'Return to Modules page', 'wordpoints' ) . '</a>';
		$install_actions['install_page'] = '<a href="' . esc_attr( esc_url( self_admin_url( 'admin.php?page=wordpoints_install_modules' ) ) ) . '" target="_parent">' . esc_html__( 'Return to Module Installer', 'wordpoints' ) . '</a>';

		/**
		 * The install module action links.
		 *
		 * @since 1.1.0
		 *
		 * @param string[] $install_actions {
		 *        The HTML for the action links.
		 *
		 *        @type string $activate_module  Activate the module. Not available
		 *              on multisite, or if the user's capabilities are insufficient.
		 *        @type string $network_activate Network activate the module. Only
		 *              available on multisite and if the use has the required capabilities.
		 *        @type string $install_page     Return to the module install screen.
		 * }
		 * @param string $module_file The main module file.
		 */
		$install_actions = apply_filters( 'wordpoints_install_module_complete_actions', $install_actions, $module_file );

		if ( ! empty( $install_actions ) ) {
			$this->feedback( implode( ' | ', (array) $install_actions ) );
		}

	} // function after()

} // class WordPoints_Module_Installer_Skin
