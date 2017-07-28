<?php

/**
 * Points hooks uninstaller factory class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Factory for uninstall routines for points hooks.
 *
 * Points hooks settings are currently stored in the options table. The settings
 * for each type of points hook is stored in a separate option. The option name
 * is the class name (all lowercase'd) prefixed with 'wordpoints_hook-'. Within
 * the plugin, the storage and retrieval of hook settings is handled by core
 * functions, so how they are stored is not important to extensions. It is thus
 * possible that the method of storage could change in the future. To avoid
 * breakage if this happens, the hooks to uninstall are just specified by slug,
 * and this class will handle the rest.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Uninstaller_Factory_Points_Hooks
	extends WordPoints_Uninstaller_Factory_Options {

	/**
	 * @since 2.4.0
	 *
	 * @param string[][] $points_hooks The base IDs (class names) of the points hooks
	 *                                 to uninstall, indexed by context.
	 */
	public function __construct( $points_hooks ) {

		$options = array();

		foreach ( $points_hooks as $context => $slugs ) {
			foreach ( $slugs as $slug ) {
				$options[ $context ][] = 'wordpoints_hook-' . $slug;
			}
		}

		parent::__construct( $options );
	}
}

// EOF
