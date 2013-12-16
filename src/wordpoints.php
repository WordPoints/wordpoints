<?php

/**
 * Plugin Name: WordPoints
 * Plugin URI: http://wordpoints.org/
 * Description: The ultimate points, ranks and achievements plugin for WordPress.
 * Version: 1.1.0
 * Author: J.D. Grimes
 * Author URI: http://codesymphony.co/
 * License: GPLv2
 * Text Domain: wordpoints
 * Domain Path: /languages
 *
 * ---------------------------------------------------------------------------------|
 * Copyright 2013  J.D. Grimes  (email : jdg@codesymphony.co)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or later, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * ---------------------------------------------------------------------------------|
 *
 * This plugin uses {@link http://semver.org/ Semantic Versioning}. It is fully
 * {@link http://www.phpdoc.org/docs/latest/index.html docblocked}, and follows the
 * {@link http://codex.wordpress.org/WordPress_Coding_Standards WordPress coding
 * standards}.
 *
 * WordPoints is coded with extension in mind. There are plenty of comments
 * throughout, but if you need more documentation you can find it at WordPoints.org.
 *
 * The symphony begins here. Sit back and enjoy!
 *
 * @package WordPoints
 * @author J.D. Grimes <jdg@codesymphony.co>
 * @version 1.1.0
 * @license http://opensource.org/licenses/gpl-license.php GPL, version 2 or later.
 * @copyright 2013 J.D. Grimes
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
define( 'WORDPOINTS_VERSION', '1.1.0' );

/**
 * You are here.
 *
 * @since 1.0.0
 *
 * @const WORDPOINTS_DIR
 */
define( 'WORDPOINTS_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Include the activate file on activation.
 *
 * @since 1.0.0
 *
 * @action activate_wordpoints/wordpoints.php
 */
function wordpoints_activate() {

	// Get the plugin version and other data from the database.
	$wordpoints_data = get_option( 'wordpoints_data' );

	if ( ! $wordpoints_data ) {

		// If this didn't exist, then we need to install the plugin.

		/**
		 * Code to install the plugin.
		 *
		 * @since 1.0.0
		 */
		require WORDPOINTS_DIR . 'install.php';
	}
}
register_activation_hook( __FILE__, 'wordpoints_activate' );

/**
 * Core functions.
 *
 * Contains general functions that can be used by components and modules.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'includes/functions.php';

/**
 * Registers scripts and styles.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'includes/scripts.php';

/**
 * Components class.
 *
 * Loads components.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'includes/class-wordpoints-components.php';

/**
 * Module class.
 *
 * Loads modules, etc.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'includes/modules.php';

/**
 * Deprecated functions.
 *
 * @since 1.1.0
 */
include_once WORDPOINTS_DIR . 'includes/deprecated.php';

if ( is_admin() ) {

	// We are on the administration side of the site.

	/**
	 * Admin related code.
	 *
	 * This file also includes other admin files.
	 *
	 * @since 1.0.0
	 */
	include_once WORDPOINTS_DIR . 'admin/admin.php';
}

/**
 * Load the plugin's textdomain.
 *
 * @since 1.1.0
 *
 * @action plugins_loaded
 */
function wordpoints_load_textdoomain() {

	load_plugin_textdomain( 'wordpoints', false, WORDPOINTS_DIR . '/languages/' );
}
add_action( 'plugins_loaded', 'wordpoints_load_textdoomain' );

// end of file /wordpoints.php
