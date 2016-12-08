<?php

/**
 * Installs modules remotely.
 *
 * @package WordPoints\PHPUnit
 * @since   2.2.0
 */

// The $data variable is provided by the including file.
$modules = $data;

// Load files to be included before the modules are installed.
if ( isset( $custom_files['before_modules'] ) ) {
	foreach ( $custom_files['before_modules'] as $file => $data ) {
		require $file;
	}
}

// Activate the modules.
foreach ( $modules as $module => $module_info ) {
	wordpoints_activate_module( $module, '', $module_info['network_wide'] );
}

// Load files to be included after the modules are installed.
if ( isset( $custom_files['after_modules'] ) ) {
	foreach ( $custom_files['after_modules'] as $file => $data ) {
		require $file;
	}
}

// EOF
