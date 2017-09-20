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

require_once dirname( __FILE__ ) . '/includes/constants.php';

// Some functions are used by the uninstaller.
require_once WORDPOINTS_DIR . '/includes/functions.php';

// The hooks API needs to be set up for the uninstaller to set the appropriate
// hooks mode during uninstallation.
require_once WORDPOINTS_DIR . '/includes/apps.php';
require_once WORDPOINTS_DIR . '/includes/hooks.php';
require_once WORDPOINTS_DIR . '/includes/filters.php';

require_once WORDPOINTS_DIR . '/classes/class/autoloader.php';

WordPoints_Class_Autoloader::register_dir( WORDPOINTS_DIR . 'classes' );

$uninstaller = new WordPoints_Uninstaller( new WordPoints_Installable_Core() );
$uninstaller->run();

// EOF
