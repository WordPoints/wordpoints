<?php

/**
 * Admin screens with list tables uninstaller factory class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Factory for uninstall routines for admin screens with list tables.
 *
 * Any admin screen that uses list tables also will automatically have certain
 * screen options associated with it. Each user is able to configure these
 * options for themselves, and this is saved as user metadata.
 *
 * List tables have two main configuration options, which are both saves as user
 * metadata:
 *
 * - Hidden Columns
 * - Screen Options
 *
 * The hidden columns metadata is removed by default, as well as the 'per_page'
 * screen options.
 *
 * A note on screen options: they are retrieved with get_user_option(), however,
 * they are saved by update_user_option() with the $global argument set to true.
 * Because of this, even on multisite, they are saved like regular user metadata,
 * which is network wide, *not* prefixed for each site.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Factory_Admin_Screens_With_List_Tables
	implements WordPoints_Uninstaller_Factory_SingleI,
		WordPoints_Uninstaller_Factory_NetworkI {

	/**
	 * The data for each screen indexed by screen ID, indexed by context.
	 *
	 * @since 2.4.0
	 *
	 * @var array[][]
	 */
	protected $screens;

	/**
	 * @since 2.4.0
	 *
	 * @param array[][] $screens The screens and their data, indexed by context.
	 *                            Each screen ID is a key, with the value being an
	 *                            array of data for that screen. The data for each
	 *                            screen consists of the slug of the parent screen,
	 *                            and a list of extra user options to delete. By
	 *                            default the 'parent' screen is assumed to be
	 *                            'wordpoints'. If a screen has no parent, 'toplevel'
	 *                            should be supplied as the parent slug. The option
	 *                            deleted by default is 'per_page'. Specify different
	 *                            options with the 'options' array.
	 */
	public function __construct( $screens ) {

		$this->screens = wordpoints_map_context_shortcuts( $screens );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_for_single() {
		return $this->get_for_context( 'single' );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_for_network() {

		// The options are always stored as global user meta, not per-site.
		$routines = array_merge(
			$this->get_for_context( 'network' )
			, $this->get_for_context( 'site' )
		);

		return $routines;
	}

	/**
	 * Gets list table uninstall routines for a particular context.
	 *
	 * @since 2.4.0
	 *
	 * @param string $context The context.
	 *
	 * @return WordPoints_RoutineI[] The uninstall routines.
	 */
	protected function get_for_context( $context ) {

		if ( empty( $this->screens[ $context ] ) ) {
			return array();
		}

		$meta_keys = array();
		$defaults  = array(
			'parent'  => 'wordpoints',
			'options' => array( 'per_page' ),
		);

		foreach ( $this->screens[ $context ] as $screen_id => $args ) {

			$args = array_merge( $defaults, $args );

			$parent = $args['parent'];

			// The extensions screen is the top-level screen on each site when
			// network installed.
			if (
				(
					'wordpoints_extensions' === $screen_id
					|| 'wordpoints_modules' === $screen_id
				)
				&& 'site' === $context
			) {
				$parent = 'toplevel';
			}

			// Each user can hide specific columns of the table.
			if ( 'network' === $context ) {
				$meta_keys[] = "manage{$parent}_page_{$screen_id}-networkcolumnshidden";
			} else {
				$meta_keys[] = "manage{$parent}_page_{$screen_id}columnshidden";
			}

			// Loop through each of the other options provided by this list table.
			foreach ( $args['options'] as $option ) {

				// Each user gets to set the options to their liking.
				if ( 'network' === $context ) {
					$meta_keys[] = "{$parent}_page_{$screen_id}_network_{$option}";
				} else {
					$meta_keys[] = "{$parent}_page_{$screen_id}_{$option}";
				}
			}
		}

		return array( new WordPoints_Uninstaller_Metadata( 'user', $meta_keys ) );
	}
}

// EOF
