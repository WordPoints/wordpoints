<?php

/**
 * Class to un/install the plugin.
 *
 * @package WordPoints
 * @since 1.8.0
 * @deprecated 2.4.0
 */

_deprecated_file( __FILE__, '2.4.0' );

/**
 * Un/install the plugin.
 *
 * @since 1.8.0
 * @deprecated 2.4.0 Use WordPoints_Installable_Core instead.
 */
class WordPoints_Un_Installer extends WordPoints_Un_Installer_Base {

	/**
	 * Uninstall modules.
	 *
	 * Note that modules aren't active when they are uninstalled, so they need to
	 * include any dependencies in their uninstall.php files.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function uninstall_modules() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Attempt to delete the modules directory.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function delete_modules_dir() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Uninstall the components.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function uninstall_components() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update the site to 1.3.0.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function update_single_to_1_3_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a site to 1.5.0.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function update_site_to_1_5_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a site to 1.8.0.
	 *
	 * @since 1.8.0
	 * @deprecated 2.4.0
	 */
	protected function update_site_to_1_8_0() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a multisite network to 1.10.3.
	 *
	 * @since 1.10.3
	 * @deprecated 2.4.0
	 */
	protected function update_network_to_1_10_3() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a non-multisite install to 1.10.3
	 *
	 * @since 1.10.3
	 * @deprecated 2.4.0
	 */
	protected function update_single_to_1_10_3() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a multisite network to 2.1.0.
	 *
	 * @since 2.1.0
	 * @deprecated 2.4.0
	 */
	protected function update_network_to_2_1_0_alpha_3() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a non-multisite install to 2.1.0.
	 *
	 * @since 2.1.0
	 * @deprecated 2.4.0
	 */
	protected function update_single_to_2_1_0_alpha_3() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a multisite network to 2.3.0.
	 *
	 * @since 2.3.0
	 * @deprecated 2.4.0
	 */
	protected function update_network_to_2_3_0_alpha_2() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}

	/**
	 * Update a non-multisite install to 2.3.0.
	 *
	 * @since 2.3.0
	 * @deprecated 2.4.0
	 */
	protected function update_single_to_2_3_0_alpha_2() {
		_deprecated_function( __METHOD__, '2.4.0' );
	}
}

return 'WordPoints_Un_Installer';

// EOF
