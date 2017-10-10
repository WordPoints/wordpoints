<?php

/**
 * Extension upgrader skin class.
 *
 * @package WordPoints
 * @since 2.4.0
 */

/**
 * The WordPress upgrader classes.
 *
 * @since 2.4.0
 */
require_once ABSPATH . '/wp-admin/includes/class-wp-upgrader.php';

/**
 * Provides a visual "skin" for the extension upgrader.
 *
 * @since 2.4.0
 */
class WordPoints_Extension_Upgrader_Skin extends WP_Upgrader_Skin {

	/**
	 * The extension slug.
	 *
	 * @since 2.4.0
	 *
	 * @type string $extension
	 */
	protected $extension;

	/**
	 * Whether the extension is active.
	 *
	 * @since 2.4.0
	 *
	 * @type bool $extension_active
	 */
	protected $extension_active = false;

	/**
	 * Whether the extension is network active.
	 *
	 * @since 2.4.0
	 *
	 * @type bool $extension_network_active
	 */
	protected $extension_network_active = false;

	/**
	 * Constructs the skin.
	 *
	 * @since 2.4.0
	 *
	 * @param array $args {
	 *
	 *        @type string $url       The form URL passed to request_filesystem_credentials() if needed.
	 *        @type string $extension The basename extension path.
	 *        @type string $nonce     An nonce to be added to the $url before it is passed to request_filesystem_credentials().
	 *        @type string $title     Text for H1 title used by WP_Upgrader_Skin::header().
	 * }
	 */
	public function __construct( $args = array() ) {

		$defaults = array(
			'url'       => '',
			'extension' => '',
			'nonce'     => '',
			'title'     => __( 'Update Extension', 'wordpoints' ),
		);

		$args = wp_parse_args( $args, $defaults );

		$this->extension                = $args['extension'];
		$this->extension_active         = is_wordpoints_module_active( $this->extension );
		$this->extension_network_active = is_wordpoints_module_active_for_network(
			$this->extension
		);

		parent::__construct( $args );
	}

	/**
	 * @since 2.4.0
	 */
	public function after() {

		// Refresh the main extension file, since it may have changed in the update.
		if ( $this->upgrader instanceof WordPoints_Extension_Upgrader ) {
			$this->extension = $this->upgrader->module_info();
		}

		if ( ! empty( $this->extension ) && ! is_wp_error( $this->result ) && $this->extension_active ) {

			$url = wp_nonce_url( self_admin_url( 'update.php?action=wordpoints-reactivate-extension&network_wide=' . $this->extension_network_active . '&extension=' . rawurlencode( $this->extension ) ), "reactivate-extension_{$this->extension}" );

			?>

			<iframe name="wordpoints_extension_reactivation" style="border: 0; overflow: hidden;" width="100%" height="170px" src="<?php echo esc_url( $url ); ?>"></iframe>

			<?php
		}

		$update_actions = $this->get_extension_update_actions();

		if ( ! empty( $update_actions ) ) {
			$this->feedback( implode( ' | ', (array) $update_actions ) );
		}
	}

	/**
	 * Get the extension update actions.
	 *
	 * @since 2.4.0
	 *
	 * @return string[] The anchor elements for the actions links to display.
	 */
	public function get_extension_update_actions() {

		$update_actions = array(
			'activate_extension' => '<a href="' . esc_url( wp_nonce_url( self_admin_url( 'admin.php?page=wordpoints_extensions&action=activate&module=' . rawurlencode( $this->extension ) ), "activate-module_{$this->extension}" ) ) . '" target="_parent">' . esc_html__( 'Activate Extension', 'wordpoints' ) . '</a>',
			'extensions_page'    => '<a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_extensions' ) ) . '" target="_parent">' . esc_html__( 'Return to Extensions page', 'wordpoints' ) . '</a>',
		);

		if (
			$this->extension_active
			|| ! $this->result
			|| is_wp_error( $this->result )
			|| ! current_user_can( 'activate_wordpoints_extensions' )
		) {
			unset( $update_actions['activate_extension'] );
		}

		/**
		 * The extension update complete action URLs.
		 *
		 * @since 2.4.0
		 *
		 * @param array $update_actions {
		 *        The HTML links for the actions.
		 *
		 *        @type string $activate_extension Activate the extension.
		 *        @type string $extensions_page    Return to the extensions page.
		 * }
		 * @param string $extension The basename path to the extension file.
		 */
		return apply_filters( 'wordpoints_update_extension_complete_actions', $update_actions, $this->extension );
	}

} // End class WordPoints_Extension_Upgrader_Skin.

// EOF
