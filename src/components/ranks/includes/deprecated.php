<?php

/**
 * Deprecated functions of the ranks component.
 *
 * @package WordPoints\Ranks
 * @since 1.8.0
 */

/**
 * Install the ranks component.
 *
 * @since 1.7.0
 * @deprecated 1.8.0 Use WordPoints_Components::activate( 'ranks' ) instead.
 */
function wordpoints_ranks_component_activate() {

	_deprecated_function(
		__FUNCTION__
		, '1.8.0'
		, "WordPoints_Components::activate( 'ranks' )"
	);

	/**
	 * Installs the ranks component.
	 *
	 * @since 1.8.0
	 */
	require_once WORDPOINTS_DIR . 'components/ranks/includes/class-un-installer.php';

	$installer = new WordPoints_Ranks_Un_Installer;
	$installer->install( is_wordpoints_network_active() );
}

// EOF
