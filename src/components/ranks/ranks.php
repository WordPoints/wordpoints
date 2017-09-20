<?php

/**
 * WordPoints Ranks component.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

WordPoints_Class_Autoloader::register_dir(
	WORDPOINTS_DIR . 'components/ranks/classes'
);

WordPoints_Class_Autoloader::register_dir(
	WORDPOINTS_DIR . 'components/ranks/includes'
);

/**
 * Ranks constants and global vars.
 *
 * @since 1.7.0
 */
require_once WORDPOINTS_DIR . 'components/ranks/includes/constants.php';

/**
 * Ranks API functions.
 *
 * @since 1.7.0
 */
require_once WORDPOINTS_DIR . 'components/ranks/includes/ranks.php';

/**
 * Action and filter hooks.
 *
 * @since 1.7.0
 */
require_once WORDPOINTS_DIR . 'components/ranks/includes/filters.php';

/**
 * Deprecated code.
 *
 * @since 1.8.0
 */
require_once WORDPOINTS_DIR . 'components/ranks/includes/deprecated.php';

if ( wordpoints_component_is_active( 'points' ) ) {

	/**
	 * Ranks integration with the Points component.
	 *
	 * @since 1.7.0
	 */
	require_once WORDPOINTS_DIR . 'components/ranks/includes/integration/points.php';
}

if ( is_admin() ) {

	/**
	 * Administration-side rank code.
	 *
	 * @since 1.7.0
	 */
	require_once WORDPOINTS_DIR . 'components/ranks/admin/admin.php';
}

// EOF
