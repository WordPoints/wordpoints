<?php

/**
 * Abstract class for un/installing a plugin/component/module.
 *
 * @package WordPoints
 * @since 1.8.0
 */

/**
 * Base class to be extended for un/installing a plugin/component/module.
 *
 * @since 1.8.0
 */
abstract class WordPoints_Un_Installer_Base {

	//
	// Protected Vars.
	//

	/**
	 * The name of the boolean option to indicate whether network install is skipped.
	 *
	 * @since 1.8.0
	 *
	 * @type string $network_install_skipped_option
	 */
	protected $network_install_skipped_option;

	/**
	 * The name of the boolean option indicating whether this is network installed.
	 *
	 * @since 1.8.0
	 *
	 * @type string $network_installed_option
	 */
	protected $network_installed_option;

	/**
	 * The name of an option containing an array of site IDs where this is installed.
	 *
	 * @since 1.8.0
	 *
	 * @type string $installed_sites_option
	 */
	protected $installed_sites_option;

	//
	// Public Methods.
	//

	/**
	 * Run the install routine.
	 *
	 * @since 1.8.0
	 *
	 * @param bool $network Whether the install should be network-wide on multisite.
	 */
	public function install( $network ) {

		if ( is_multisite() ) {

			$this->install_network();

			if ( $network ) {

				update_site_option( $this->network_installed_option, true );

				if ( $this->do_per_site_install() ) {

					$original_blog_id = get_current_blog_id();

					foreach ( $this->get_all_site_ids() as $blog_id ) {
						switch_to_blog( $blog_id );
						$this->install_site();
					}

					switch_to_blog( $original_blog_id );

					// See http://wordpress.stackexchange.com/a/89114/27757
					unset( $GLOBALS['_wp_switched_stack'] );
					$GLOBALS['switched'] = false;

				} else {

					// We'll check this later and let the user know that per-site
					// install was skipped.
					add_site_option( $this->network_install_skipped_option, true );
				}

			} else {

				$this->install_site();

				$sites = wordpoints_get_array_option( $this->installed_sites_option, 'site' );
				$sites[] = get_current_blog_id();

				update_site_option( $this->installed_sites_option, $sites );
			}

		} else {

			$this->install_single();
		}
	}

	/**
	 * Run the uninstallation routine.
	 *
	 * @since 1.8.0
	 */
	public function uninstall() {

		$this->load_dependencies();

		$this->before_uninstall();

		if ( is_multisite() ) {

			if ( $this->do_per_site_uninstall() ) {

				$original_blog_id = get_current_blog_id();

				foreach ( $this->get_installed_site_ids() as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->uninstall_site();
				}

				switch_to_blog( $original_blog_id );

				// See http://wordpress.stackexchange.com/a/89114/27757
				unset( $GLOBALS['_wp_switched_stack'] );
				$GLOBALS['switched'] = false;
			}

			$this->uninstall_network();

			delete_site_option( $this->installed_sites_option );
			delete_site_option( $this->network_installed_option );
			delete_site_option( $this->network_install_skipped_option );

		} else {

			$this->uninstall_single();
		}
	}

	//
	// Protected Methods.
	//

	/**
	 * Check whether we should run the install for each site in the network.
	 *
	 * On large networks we don't attempt the per-site install.
	 *
	 * @since 1.8.0
	 *
	 * @return bool Whether to do the per-site installation.
	 */
	protected function do_per_site_install() {

		return ! wp_is_large_network();
	}

	/**
	 * Get the IDs of all sites on the network.
	 *
	 * @since 1.8.0
	 *
	 * @return array The IDs of all sites on the network.
	 */
	protected function get_all_site_ids() {

		global $wpdb;

		return $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
	}

	/**
	 * Check if this entity is network installed.
	 *
	 * @since 1.8.0
	 *
	 * @return bool Whether the code is network installed.
	 */
	protected function is_network_installed() {

		return (bool) get_site_option( $this->network_installed_option );
	}

	/**
	 * Check if we should run the uninstall for each site on the network.
	 *
	 * On large multisite networks we don't attempt the per-site uninstall.
	 *
	 * @since 1.8.0
	 *
	 * @return bool Whether to do the per-site uninstallation.
	 */
	protected function do_per_site_uninstall() {

		if ( $this->is_network_installed() && wp_is_large_network() ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the IDs of all sites on which this is installed.
	 *
	 * @since 1.8.0
	 *
	 * @return array The IDs of the sites where this entity is installed.
	 */
	protected function get_installed_site_ids() {

		if ( $this->is_network_installed() ) {
			$sites = $this->get_all_site_ids();
		} else {
			$sites = wordpoints_get_array_option( $this->installed_sites_option, 'site' );
		}

		return $sites;
	}

	/**
	 * Run before uninstalling, but after loading dependencies.
	 *
	 * @since 1.8.0
	 */
	protected function before_uninstall() {}

	//
	// Abstract Methods.
	//

	/**
	 * Load any dependencies of the unisntall code.
	 *
	 * @since 1.8.0
	 */
	abstract protected function load_dependencies();

	/**
	 * Uninstall from the network.
	 *
	 * This runs on multisite to uninstall only the things that are common to the
	 * whole network. For example, it would delete any "site" (network-wide) options.
	 *
	 * @since 1.8.0
	 */
	abstract protected function uninstall_network();

	/**
	 * Uninstall from a single site on the network.
	 *
	 * This runs on multisite to uninstall from a single site on the network, which
	 * will be the current site when this method is called.
	 *
	 * @since 1.8.0
	 */
	abstract protected function uninstall_site();

	/**
	 * Uninstall from a single site.
	 *
	 * This runs when the WordPress site is not a multisite. It should completely
	 * uninstall the entity.
	 *
	 * @since 1.8.0
	 */
	abstract protected function uninstall_single();
}

// EOF
