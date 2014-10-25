<?php

/**
 * WordPoints Ranks component.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Ranks constants and global vars.
 *
 * @since 1.7.0
 */
include_once WORDPOINTS_DIR . 'components/ranks/includes/constants.php';

/**
 * Ranks component utility functions.
 *
 * @since 1.7.0
 */
include_once WORDPOINTS_DIR . 'components/ranks/includes/functions.php';

/**
 * Ranks API functions.
 *
 * @since 1.7.0
 */
include_once WORDPOINTS_DIR . 'components/ranks/includes/ranks.php';

/**
 * Rank class.
 *
 * @since 1.7.0
 */
include_once WORDPOINTS_DIR . 'components/ranks/includes/class-wordpoints-rank.php';

/**
 * Rank types class.
 *
 * @since 1.7.0
 */
include_once WORDPOINTS_DIR . 'components/ranks/includes/class-wordpoints-rank-types.php';

/**
 * Rank type class.
 *
 * @since 1.7.0
 */
include_once WORDPOINTS_DIR . 'components/ranks/includes/class-wordpoints-rank-type.php';

/**
 * Rank groups class.
 *
 * @since 1.7.0
 */
include_once WORDPOINTS_DIR . 'components/ranks/includes/class-wordpoints-rank-groups.php';

/**
 * Rank group class.
 *
 * @since 1.7.0
 */
include_once WORDPOINTS_DIR . 'components/ranks/includes/class-wordpoints-rank-group.php';

/**
 * Rank types.
 *
 * @since 1.7.0
 */
include_once WORDPOINTS_DIR . 'components/ranks/includes/rank-types.php';

if ( wordpoints_component_is_active( 'points' ) ) {

	/**
	 * Ranks integration with the Points component.
	 *
	 * @since 1.7.0
	 */
	include_once WORDPOINTS_DIR . 'components/ranks/includes/integration/points.php';
}

if ( is_admin() ) {

	/**
	 * Administration-side rank code.
	 *
	 * @since 1.7.0
	 */
	include_once WORDPOINTS_DIR . 'components/ranks/admin/admin.php';
}

// EOF
