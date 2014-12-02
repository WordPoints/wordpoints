<?php

/**
 * Uninstall the plugin.
 *
 * Uninstallation, as apposed to deactivation, will remove all of the plugin's data.
 *
 * @package WordPoints
 * @since 1.0.0
 */

// Exit if we aren't being uninstalled.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once( dirname( __FILE__ ) . '/includes/class-un-installer-base.php' );
require_once( dirname( __FILE__ ) . '/includes/class-un-installer.php' );

$uninstaller = new WordPoints_Un_Installer;
$uninstaller->uninstall();

// EOF
