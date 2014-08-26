<?php

/**
 * Points Hooks.
 *
 * These are the Points Hooks included with the plugin. They are each an extension of
 * the base class WordPoints_Points_Hook.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 * @deprecated 1.4.0
 * @deprecated Use the files in the /hooks/ directory instead.
 */

_deprecated_file(
	'/components/points/includes/hooks.php'
	, '1.4.0'
	, 'any of the files in /components/points/includes/hooks/'
	, 'The file was split into separate files for each points hook.'
);

/**
 * The registration points hook
 *
 * @since 1.4.0
 */
include_once WORDPOINTS_DIR . 'components/points/includes/hooks/registration.php';

/**
 * The post points hook
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

// EOF
