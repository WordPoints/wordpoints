<?php

/**
 * Points component administration.
 *
 * This code is run only on the administration pages. It registers the points
 * administration panels, etc.
 *
 * @package WordPoints\Points\Administration
 * @since 1.0.0
 */

/**
 * AJAX callbacks.
 *
 * @since 1.2.0
 */
require_once WORDPOINTS_DIR . 'components/points/admin/includes/ajax.php';

/**
 * Admin-side functions.
 *
 * @since 2.1.0
 */
require_once WORDPOINTS_DIR . '/components/points/admin/includes/functions.php';

/**
 * Admin-side hooks.
 *
 * @since 2.1.0
 */
require_once WORDPOINTS_DIR . '/components/points/admin/includes/filters.php';

WordPoints_Class_Autoloader::register_dir(
	WORDPOINTS_DIR . '/components/points/admin/classes'
);

// EOF
