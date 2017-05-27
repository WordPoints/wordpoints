<?php

/**
 * Module upgrader skin class.
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
 * Provides a visual "skin" for the module upgrader.
 *
 * @since 2.4.0
 */
class WordPoints_Module_Upgrader_Skin extends WP_Upgrader_Skin {

	/**
	 * The module slug.
	 *
	 * @since 2.4.0
	 *
	 * @type string $module
	 */
	protected $module;

	/**
	 * Whether the module is active.
	 *
	 * @since 2.4.0
	 *
	 * @type bool $module_active
	 */
	protected $module_active = false;

	/**
	 * Whether the module is network active.
	 *
	 * @since 2.4.0
	 *
	 * @type bool $module_network_active
	 */
	protected $module_network_active = false;

	/**
	 * Constructs the skin.
	 *
	 * @since 2.4.0
	 *
	 * @param array $args {
	 *
	 *        @type string $url    The form URL passed to request_filesystem_credentials() if needed.
	 *        @type string $module The basename module path.
	 *        @type string $nonce  An nonce to be added to the $url before it is passed to request_filesystem_credentials().
	 *        @type string $title  Text for H1 title used by WP_Upgrader_Skin::header().
	 * }
	 */
	public function __construct( $args = array() ) {

		$defaults = array(
			'url'    => '',
			'module' => '',
			'nonce'  => '',
			'title'  => __( 'Update Module', 'wordpoints' ),
		);

		$args = wp_parse_args( $args, $defaults );

		$this->module                = $args['module'];
		$this->module_active         = is_wordpoints_module_active( $this->module );
		$this->module_network_active = is_wordpoints_module_active_for_network(
			$this->module
		);

		parent::__construct( $args );
	}

	/**
	 * @since 2.4.0
	 */
	public function after() {

		// Refresh the main module file, since it may have changed in the update.
		if ( $this->upgrader instanceof WordPoints_Module_Upgrader ) {
			$this->module = $this->upgrader->module_info();
		}

		if ( ! empty( $this->module ) && ! is_wp_error( $this->result ) && $this->module_active ) {

			$url = wp_nonce_url( self_admin_url( 'admin.php?page=wordpoints_configure&tab=modules&action=activate-module&networkwide=' . $this->module_network_active . '&module=' . rawurlencode( $this->module ) ), "activate-module_{$this->module}" );

			?>

			<iframe style="border: 0; overflow: hidden;" width="100%" height="170px" src="<?php echo esc_url( $url ); ?>"></iframe>

			<?php
		}

		$update_actions = $this->get_module_update_actions();

		if ( ! empty( $update_actions ) ) {
			$this->feedback( implode( ' | ', (array) $update_actions ) );
		}
	}

	/**
	 * Get the module update actions.
	 *
	 * @since 2.4.0
	 *
	 * @return string[] The anchor elements for the actions links to display.
	 */
	public function get_module_update_actions() {

		$update_actions = array(
			'activate_module' => '<a href="' . esc_url( wp_nonce_url( self_admin_url( 'admin.php?page=wordpoints_modules&action=activate&amp;module=' . rawurlencode( $this->module ) ), "activate-module_{$this->module}" ) ) . '" target="_parent">' . esc_html__( 'Activate Module', 'wordpoints' ) . '</a>',
			'modules_page'    => '<a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_modules' ) ) . '" target="_parent">' . esc_html__( 'Return to Modules page', 'wordpoints' ) . '</a>',
		);

		if (
			$this->module_active
			|| ! $this->result
			|| is_wp_error( $this->result )
			|| ! current_user_can( 'activate_wordpoints_modules' )
		) {
			unset( $update_actions['activate_module'] );
		}

		/**
		 * The module update complete action URLs.
		 *
		 * @since 2.4.0
		 *
		 * @param array $update_actions {
		 *        The HTML links for the actions.
		 *
		 *        @type string $activate_module Activate the module.
		 *        @type string $modules_page    Return to the modules page.
		 * }
		 * @param string $module The basename path to the module file.
		 */
		return apply_filters( 'wordpoints_update_module_complete_actions', $update_actions, $this->module );
	}

} // End class WordPoints_Module_Upgrader_Skin.

// EOF
