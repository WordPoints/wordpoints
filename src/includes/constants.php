<?php

/**
 * WordPoints constants and global vars.
 *
 * @package WordPoints
 * @since 1.2.0
 */

global $wpdb;

$wpdb->wordpoints_hook_hits    = $wpdb->base_prefix . 'wordpoints_hook_hits';
$wpdb->wordpoints_hook_hitmeta = $wpdb->base_prefix . 'wordpoints_hook_hitmeta';
$wpdb->wordpoints_hook_periods = $wpdb->base_prefix . 'wordpoints_hook_periods';

/**
 * The plugin version.
 *
 * Conforms to {@link http://semver.org/ Semantic Versioning}.
 *
 * @since 1.0.0
 *
 * @const WORDPOINTS_VERSION
 */
define( 'WORDPOINTS_VERSION', '2.5.0-alpha-3' );

/**
 * The full path to the plugin's main directory.
 *
 * @since 1.0.0
 *
 * @const WORDPOINTS_DIR
 */
define( 'WORDPOINTS_DIR', dirname( dirname( __FILE__ ) ) . '/' );

/**
 * The full URL to the plugin's main directory.
 *
 * @since 1.7.0
 *
 * @const WORDPOINTS_URL
 */
define( 'WORDPOINTS_URL', plugins_url( '', WORDPOINTS_DIR . 'wordpoints.php' ) );

/**
 * The Ed25519 public key used to verify extension packages from wordpoints.org.
 *
 * @since 2.5.0
 *
 * @const WORDPOINTS_ORG_EXTENSIONS_ED25519_PUBLIC_KEY
 */
define( 'WORDPOINTS_ORG_EXTENSIONS_ED25519_PUBLIC_KEY', '9c564cdb1763a72a81f2ddee1e27230ea4c18748ee14324ac4671d4be701492e' );

// EOF
