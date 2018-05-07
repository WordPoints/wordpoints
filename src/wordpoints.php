<?php

/**
 * Plugin Name: WordPoints
 * Plugin URI: https://wordpoints.org/
 * Description: Create one or more points systems for your site, and reward user activity.
 * Version: 2.4.2
 * Author: J.D. Grimes
 * Author URI: https://codesymphony.co/
 * License: GPLv2
 * Text Domain: wordpoints
 * Domain Path: /languages
 *
 * ---------------------------------------------------------------------------------|
 * Copyright 2013-18  J.D. Grimes  (email : jdg@codesymphony.co)
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
 * {@link https://www.phpdoc.org/docs/latest/index.html docblocked}, and follows the
 * {@link https://codex.wordpress.org/WordPress_Coding_Standards WordPress coding
 * standards}.
 *
 * WordPoints is coded with extension in mind. There are plenty of comments
 * throughout, but if you need more documentation you can find it at WordPoints.org.
 *
 * The symphony begins here. Sit back and enjoy!
 *
 * @package WordPoints
 * @author J.D. Grimes <jdg@codesymphony.co>
 * @version 2.4.2
 * @license https://opensource.org/licenses/gpl-license.php GPL, version 2 or later.
 * @copyright 2013-18 J.D. Grimes
 */

/**
 * Plugin defined constants.
 *
 * @since 1.2.0
 */
require_once dirname( __FILE__ ) . '/includes/constants.php';

/**
 * Core functions.
 *
 * Contains general functions that can be used by components and extensions.
 *
 * @since 1.0.0
 */
require_once WORDPOINTS_DIR . 'includes/functions.php';

/**
 * Apps functions.
 *
 * Functions related to registering "apps", or OOP APIs that are loaded in JIT
 * fashion. Also functions related to core apps.
 *
 * @since 2.1.0
 */
require_once WORDPOINTS_DIR . 'includes/apps.php';

/**
 * Hooks functions.
 *
 * Functions for the core hooks app, a framework for reacting to WordPress actions
 * based on stored predefined criteria.
 *
 * @since 2.1.0
 */
require_once WORDPOINTS_DIR . 'includes/hooks.php';

/**
 * Extension functions.
 *
 * Loads extensions, etc.
 *
 * @since 1.0.0
 */
require_once WORDPOINTS_DIR . 'includes/modules.php';

/**
 * Class autoloader.
 *
 * Unfortunately, it can't be autoloaded. :-)
 *
 * @since 2.1.0
 */
require_once WORDPOINTS_DIR . 'classes/class/autoloader.php';

// Register the classes to autoload.
WordPoints_Class_Autoloader::register_dir( WORDPOINTS_DIR . 'classes' );

// Set up the components class.
WordPoints_Components::set_up();

/**
 * Action and filter hooks.
 *
 * @since 2.1.0
 */
require_once WORDPOINTS_DIR . 'includes/filters.php';

/**
 * Deprecated functions.
 *
 * @since 1.1.0
 */
require_once WORDPOINTS_DIR . 'includes/deprecated.php';

if ( is_admin() ) {

	// We are on the administration side of the site.

	/**
	 * Admin related code.
	 *
	 * This file also includes other admin files.
	 *
	 * @since 1.0.0
	 */
	require_once WORDPOINTS_DIR . 'admin/admin.php';
}

// EOF
