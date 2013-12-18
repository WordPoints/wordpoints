<?php

/**
 * Install the plugin.
 *
 * @package WordPoints
 * @since 1.0.0
 */

// Add plugin data.
add_option(
	'wordpoints_data',
	array(
		'version'    => WORDPOINTS_VERSION,
		'components' => array(), // Components use this to store data.
		'modules'    => array(), // Modules can use this to store data.
	)
);

// Activate the Points component.
$wordpoints_components = WordPoints_Components::instance();
$wordpoints_components->load();
$wordpoints_components->activate( 'points' );

// end of file /install.php
