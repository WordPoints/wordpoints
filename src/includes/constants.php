<?php

/**
 * WordPoints constants.
 *
 * @package WordPoints
 * @since 1.2.0
 */

/**
 * The plugin version.
 *
 * Conforms to {@link http://semver.org/ Semantic Versioning}.
 *
 * @since 1.0.0
 *
 * @const WORDPOINTS_VERSION
 */
define( 'WORDPOINTS_VERSION', '1.10.3' );

/**
 * The full path to the plugin's main directory.
 *
 * @since 1.0.0
 *
 * @const WORDPOINTS_DIR
 */
define( 'WORDPOINTS_DIR', plugin_dir_path( dirname( __FILE__ ) ) );

/**
 * The full URL to the plugin's main directory.
 *
 * @since 1.7.0
 *
 * @const WORDPOINTS_URL
 */
define( 'WORDPOINTS_URL', plugins_url( '', WORDPOINTS_DIR . 'wordpoints.php' ) );

// EOF
