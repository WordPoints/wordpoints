<?php

/**
 * WordPoints Points component
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

WordPoints_Class_Autoloader::register_dir(
	WORDPOINTS_DIR . 'components/points/includes'
);

WordPoints_Class_Autoloader::register_dir(
	WORDPOINTS_DIR . 'components/points/classes'
);

/**
 * Points component constants and global vars.
 *
 * @since 1.7.0
 */
require_once WORDPOINTS_DIR . 'components/points/includes/constants.php';

/**
 * Points component utility functions.
 *
 * @since 1.0.0
 */
require_once WORDPOINTS_DIR . 'components/points/includes/functions.php';

/**
 * Points component apps registration functions.
 *
 * @since 2.2.0
 */
require_once WORDPOINTS_DIR . 'components/points/includes/apps.php';

/**
 * Points component API functions.
 *
 * @since 1.7.0
 */
require_once WORDPOINTS_DIR . 'components/points/includes/points.php';

/**
 * Logs related functions.
 *
 * @since 1.0.0
 */
require_once WORDPOINTS_DIR . 'components/points/includes/logs.php';

/**
 * Action and filter hooks.
 *
 * @since 2.1.0
 */
require_once WORDPOINTS_DIR . 'components/points/includes/filters.php';

/**
 * Deprecated functions and classes.
 *
 * @since 1.2.0
 */
require_once WORDPOINTS_DIR . 'components/points/includes/deprecated.php';

if ( is_admin() ) {

	// We are on the administration side of the site.

	/**
	 * Points administration.
	 *
	 * @since 1.0.0
	 */
	require_once WORDPOINTS_DIR . 'components/points/admin/admin.php';
}

// EOF
