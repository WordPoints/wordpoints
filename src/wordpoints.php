<?php

/**
 * Plugin Name: WordPoints
 * Plugin URI: http://wordpoints.org/
 * Description: Create one or more points systems for your site, and reward user activity.
 * Version: 2.1.0-alpha-3
 * Author: J.D. Grimes
 * Author URI: http://codesymphony.co/
 * License: GPLv2
 * Text Domain: wordpoints
 * Domain Path: /languages
 *
 * ---------------------------------------------------------------------------------|
 * Copyright 2013-2015  J.D. Grimes  (email : jdg@codesymphony.co)
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
 * @version 2.1.0-alpha-3
 * @license http://opensource.org/licenses/gpl-license.php GPL, version 2 or later.
 * @copyright 2013-2015 J.D. Grimes
 */

/**
 * Plugin defined constants.
 *
 * @since 1.2.0
 */
include_once dirname( __FILE__ ) . '/includes/constants.php';

/**
 * Core functions.
 *
 * Contains general functions that can be used by components and modules.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'includes/functions.php';

/**
 * Apps functions.
 *
 * Functions related to registering "apps", or OOP APIs that are loaded in JIT
 * fashion. Also functions related to core apps.
 *
 * @since 2.1.0
 */
include_once WORDPOINTS_DIR . 'includes/apps.php';

/**
 * Hooks functions.
 *
 * Functions for the core hooks app, a framework for reacting to WordPress actions
 * based on stored predefined criteria.
 *
 * @since 2.1.0
 */
include_once WORDPOINTS_DIR . 'includes/hooks.php';

/**
 * Installables class.
 *
 * @since 2.0.0
 */
include_once WORDPOINTS_DIR . 'includes/class-installables.php';

/**
 * Components class.
 *
 * Loads components.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'includes/class-wordpoints-components.php';

/**
 * Modules class.
 *
 * @since 2.0.0
 */
include_once WORDPOINTS_DIR . 'includes/class-modules.php';

/**
 * Module functions.
 *
 * Loads modules, etc.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'includes/modules.php';

/**
 * Shortcode handler class.
 *
 * @since 1.8.0
 */
include_once WORDPOINTS_DIR . 'includes/class-shortcode.php';

/**
 * The base widget class.
 *
 * @since 1.9.0
 */
include_once WORDPOINTS_DIR . 'includes/class-widget.php';

/**
 * Class autoloader.
 *
 * Unfortunately, it can't be autoloaded. :-)
 *
 * @since 2.1.0
 */
require_once( WORDPOINTS_DIR . 'includes/classes/class/autoloader.php' );

// Register the classes to autoload.
WordPoints_Class_Autoloader::register_dir(
	WORDPOINTS_DIR . 'includes/classes'
	, 'WordPoints_'
);

/**
 * Action and filter hooks.
 *
 * @since 2.1.0
 */
include_once WORDPOINTS_DIR . 'includes/filters.php';

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

// EOF
