<?php

/**
 * Functions to update the plugin.
 *
 * @package WordPoints
 * @since 1.3.0
 */

_deprecated_file( __FILE__, '1.8.0' );

/**
 * Update the plugin to 1.3.0.
 *
 * @since 1.3.0
 * @deprecated 1.8.0 Use WordPoints_Un_Installer::update( '1.2.0', '1.3.0' ) instead.
 */
function wordpoints_update_1_3_0() {

	_deprecated_function( __FUNCTION__, '1.8.0', "WordPoints_Un_Installer::update( '1.2.0', '1.3.0' )" );

	/**
	 * Uninstall base class.
	 *
	 * @since 1.8.0
	 */
	include_once WORDPOINTS_DIR . 'includes/class-un-installer-base.php';

	/**
	 * The plugin un/installer.
	 *
	 * @since 1.8.0
	 */
	require_once( WORDPOINTS_DIR . '/includes/class-un-installer.php' );

	$updater = new WordPoints_Un_Installer;
	$updater->update( '1.2.0', '1.3.0' );
}

// EOF
