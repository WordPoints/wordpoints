<?php

/**
 * Deprecated administration-side functions of the points component.
 *
 * @package WordPoints\Points\Administration
 * @since   2.1.0
 */

/**
 * Register admin scripts.
 *
 * @since 1.7.0
 * @deprecated 2.1.0 Use wordpoints_points_admin_register_scripts() instead.
 */
function wordpoints_admin_register_scripts() {

	_deprecated_function(
		__FUNCTION__
		, '2.1.0'
		, 'wordpoints_points_admin_register_scripts()'
	);

	wordpoints_points_admin_register_scripts();
}

// EOF
