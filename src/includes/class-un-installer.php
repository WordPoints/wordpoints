<?php

/**
 * Class to un/install the plugin.
 *
 * @package WordPoints
 * @since 1.8.0
 */

/**
 * Un/install the plugin.
 *
 * @since 1.8.0
 */
class WordPoints_Un_Installer extends WordPoints_Un_Installer_Base {

	/**
	 * @since 1.8.0
	 */
	protected $option_prefix = 'wordpoints_';

	/**
	 * @since 1.8.0
	 */
	protected $updates = array(
		'1.3.0' => array( 'single' => true                ),
		'1.5.0' => array(                  'site' => true ),
		'1.8.0' => array(                  'site' => true ),
		'1.10.3' => array( 'single' => true, /*    -    */ 'network' => true ),
	);

	/**
	 * The plugin's capabilities.
	 *
	 * Used to hold the list of capabilities during install and uninstall, so that
	 * they don't have to be retrieved all over again for each site (if multisite).
	 *
	 * @since 1.8.0
	 *
	 * @type array $capabilties
	 */
	protected $capabilities;

	/**
	 * @since 1.8.0
	 */
	public function install( $network ) {

		$filter_func = ( $network ) ? '__return_true' : '__return_false';
		add_filter( 'is_wordpoints_network_active', $filter_func );

		// Check if the plugin has been activated/installed before.
		$installed = (bool) wordpoints_get_network_option( 'wordpoints_data' );

		$this->capabilities = wordpoints_get_custom_caps();

		parent::install( $network );

		// Activate the Points component, if this is the first activation.
		if ( false === $installed ) {
			$wordpoints_components = WordPoints_Components::instance();
			$wordpoints_components->load();
			$wordpoints_components->activate( 'points' );
		}

		remove_filter( 'is_wordpoints_network_active', $filter_func );
	}

	/**
	 * @since 1.8.0
	 */
	protected function before_uninstall() {

		$this->capabilities = array_keys( wordpoints_get_custom_caps() );
	}

	/**
	 * @since 1.8.0
	 */
	protected function before_update() {

		if ( $this->network_wide ) {
			unset( $this->updates['1_8_0'] );
		}

		$this->capabilities = wordpoints_get_custom_caps();
	}

	/**
	 * @since 1.8.0
	 */
	protected function install_network() {

		// Add plugin data.
		wordpoints_add_network_option(
			'wordpoints_data',
			array(
				'version'    => WORDPOINTS_VERSION,
				'components' => array(), // Components use this to store data.
				'modules'    => array(), // Modules can use this to store data.
			)
		);
	}

	/**
	 * @since 1.8.0
	 */
	protected function install_site() {
		wordpoints_add_custom_caps( $this->capabilities );
	}

	/**
	 * @since 1.8.0
	 */
	protected function install_single() {

		$this->install_network();
		$this->install_site();
	}

	/**
	 * @since 1.8.0
	 */
	protected function load_dependencies() {
		require_once dirname( __FILE__ ) . '/uninstall-bootstrap.php';
	}

	/**
	 * @since 1.8.0
	 */
	protected function uninstall_network() {

		$this->uninstall_modules();
		$this->uninstall_components();

		delete_site_option( 'wordpoints_data' );
		delete_site_option( 'wordpoints_active_components' );
		delete_site_option( 'wordpoints_excluded_users' );
		delete_site_option( 'wordpoints_sitewide_active_modules' );
	}

	/**
	 * @since 1.8.0
	 */
	protected function uninstall_site() {

		delete_option( 'wordpoints_data' );
		delete_option( 'wordpoints_active_modules' );
		delete_option( 'wordpoints_active_components' );
		delete_option( 'wordpoints_excluded_users' );
		delete_option( 'wordpoints_recently_activated_modules' );

		wp_cache_delete( 'wordpoints_modules' );

		wordpoints_remove_custom_caps( $this->capabilities );
	}

	/**
	 * @since 1.8.0
	 */
	protected function uninstall_single() {

		$this->uninstall_modules();
		$this->uninstall_components();
		$this->uninstall_site();
	}

	/**
	 * Uninstall modules.
	 *
	 * Note that modules aren't active when they are uninstalled, so they need to
	 * include any dependencies in their uninstall.php files.
	 *
	 * @since 1.8.0
	 */
	protected function uninstall_modules() {

		wordpoints_deactivate_modules(
			wordpoints_get_array_option( 'wordpoints_active_modules', 'site' )
		);

		foreach ( array_keys( wordpoints_get_modules() ) as $module ) {
			wordpoints_uninstall_module( $module );
		}

		$this->delete_modules_dir();
	}

	/**
	 * Attempt to delete the modules directory.
	 *
	 * @since 1.8.0
	 */
	protected function delete_modules_dir() {

		global $wp_filesystem;

		if ( $wp_filesystem instanceof WP_Filesystem ) {
			$wp_filesystem->delete( wordpoints_modules_dir(), true );
		}
	}

	/**
	 * Uninstall the components.
	 *
	 * @since 1.8.0
	 */
	protected function uninstall_components() {

		/*
		 * Back compat < 1.7.0
		 *
		 * The below notes no longer apply.
		 * --------------------------------
		 *
		 * Bulk 'deactivate' components. No other filters should be applied later than these
		 * (e.g., after 99) for this hook - doing so could have unexpected results.
		 *
		 * We do this so that we can load them to call the uninstall hooks, without them
		 * being active.
		 */
		add_filter( 'wordpoints_component_active', '__return_false', 100 );

		$components = WordPoints_Components::instance();

		// Back-compat < 1.7.0
		$components->load();

		// Uninstall the components.
		foreach ( $components->get() as $component => $data ) {
			$components->uninstall( $component );
		}
	}

	/**
	 * Update the site to 1.3.0.
	 *
	 * @since 1.8.0
	 */
	protected function update_single_to_1_3_0() {
		wordpoints_add_custom_caps( $this->capabilities );
	}

	/**
	 * Update a site to 1.5.0.
	 *
	 * @since 1.8.0
	 */
	protected function update_site_to_1_5_0() {
		wordpoints_add_custom_caps( $this->capabilities );
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
	 * Update a multisite network to 1.10.3.
	 *
	 * @since 1.10.3
	 */
	protected function update_network_to_1_10_3() {
		$this->update_single_to_1_10_3();
	}

	/**
	 * Update a non-multisite install to 1.10.3
	 *
	 * @since 1.10.3
	 */
	protected function update_single_to_1_10_3() {

		global $wp_filesystem;

		$modules_dir = wordpoints_modules_dir();

		if ( ! WP_Filesystem( false, $modules_dir ) ) {
			return;
		}

		$index_file = $modules_dir . '/index.php';

		if ( ! $wp_filesystem->exists( $index_file ) ) {
			$wp_filesystem->put_contents( $index_file, '<?php // Gold is silent.' );
		}
	}
}

// EOF
