<?php

/**
 * WordPoints Points component
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

/**
 * Points component constants and global vars.
 *
 * @since 1.7.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/constants.php';

/**
 * Points component utility functions.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/functions.php';

/**
 * Points component API functions.
 *
 * @since 1.7.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/points.php';

/**
 * Points hooks static class.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/class-wordpoints-points-hooks.php';

/**
 * Points hook abstract class.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/class-wordpoints-points-hook.php';

/**
 * Post type points hook abstract class.
 *
 * @since 1.5.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/abstracts/post-type.php';

/**
 * Comment approved points hook abstract class.
 *
 * @since 1.5.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/abstracts/comment-approved.php';

/**
 * The registration points hook
 *
 * @since 1.4.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/registration.php';

/**
 * The post publish points hook
 *
 * @since 1.4.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/post.php';

/**
 * The comment points hook
 *
 * @since 1.4.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/comment.php';

/**
 * The periodic points hook
 *
 * @since 1.4.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/periodic.php';

/**
 * The comment received points hook
 *
 * @since 1.8.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/comment-received.php';

/**
 * Points logs query class.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/class-wordpoints-points-logs-query.php';

/**
 * Shortcodes/template tags.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/shortcodes.php';

/**
 * Widgets.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/widgets.php';

/**
 * Logs related functions.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/logs.php';

/**
 * Deprecated functions and classes.
 *
 * @since 1.2.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/deprecated.php';

if ( is_admin() ) {

	// We are on the administration side of the site.

	/**
	 * Points administration.
	 *
	 * @since 1.0.0
	 */
	include_once WORDPOINTS_DIR . 'components/points/admin/admin.php';
}

// EOF
