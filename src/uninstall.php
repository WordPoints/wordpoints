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

require_once( dirname( __FILE__ ) . '/includes/constants.php' );
require_once( WORDPOINTS_DIR . '/includes/functions.php' );
require_once( WORDPOINTS_DIR . '/includes/class-installables.php' );

wordpoints_register_installer();

WordPoints_Installables::uninstall( 'plugin', 'wordpoints' );

// EOF
