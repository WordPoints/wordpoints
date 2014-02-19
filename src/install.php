<?php

/**
 * Install the plugin.
 *
 * @package WordPoints
 * @since 1.0.0
 */

/* @var bool $network_active Defined in the including file. */
$network_active;

$install_status = ( $network_active ) ? 'network' : 'single';

/**
 * Defined when the plugin is being installed.
 *
 * If the plugin is being network-activated on a multisite install, the value will
 * be 'network'. Otherwise, the value is 'single'.
 *
 * @since 1.3.0
 *
 * @const string WORDPOINTS_INSTALLING
 */
define( 'WORDPOINTS_INSTALLING', $install_status );

// Add plugin data.
wordpoints_add_network_option(
	'wordpoints_data',
	array(
		'version'    => WORDPOINTS_VERSION,
		'components' => array(), // Components use this to store data.
		'modules'    => array(), // Modules can use this to store data.
	)
);

// Add custom capabilities to the correct roles.
$capabilities = wordpoints_get_custom_caps();

if ( $network_active ) {

	global $wpdb;

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	foreach ( $blog_ids as $blog_id ) {

		switch_to_blog( $blog_id );
		wordpoints_add_custom_caps( $capabilities );
		restore_current_blog();
	}

} else {

	wordpoints_add_custom_caps( $capabilities );
}

// Activate the Points component.
$wordpoints_components = WordPoints_Components::instance();
$wordpoints_components->load();
$wordpoints_components->activate( 'points' );

// end of file /install.php
