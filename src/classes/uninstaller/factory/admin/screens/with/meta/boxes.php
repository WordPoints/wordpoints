<?php

/**
 * Admin screens with meta boxes uninstaller factory class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Factory for uninstall routines for admin screens with meta boxes.
 *
 * Any admin screen that uses meta boxes also will automatically have certain
 * screen options associated with it. Each user is able to configure these
 * options for themselves, and this is saved as user metadata.
 *
 * The default options currently are:
 *
 * - Hidden meta boxes.
 * - Closed meta boxes.
 * - Reordered meta boxes.
 *
 * This class will automatically supply uninstall routines to remove all of
 * these.
 *
 * A note on screen options: they are retrieved with get_user_option(), however,
 * they are saved by update_user_option() with the $global argument set to true.
 * Because of this, even on multisite, they are saved like regular user metadata,
 * which is network wide, *not* prefixed for each site. So we only need to run
 * the meta box and list table uninstall for network and single (i.e., "global"),
 * even if the screen is "universal".
 */
class WordPoints_Uninstaller_Factory_Admin_Screens_With_Meta_Boxes
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
	 * @param array[][] $screens The admin screens with meta boxes, indexed by
	 *                            context. Each screen ID is a key, with the value
	 *                            being an array of data for that screen. The data
	 *                            for each screen consists of the slug of the parent
	 *                            screen, and a list of extra user options to delete.
	 *                            By default the 'parent' screen is assumed to be
	 *                            'wordpoints'. If a screen has no parent, 'toplevel'
	 *                            should be supplied as the parent slug. The options
	 *                            deleted by default are 'closedpostboxes',
	 *                            'metaboxhidden', 'meta-box-order'. Supply
	 *                            additional options with the 'options' array.
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
	 * Gets the uninstall routines for a particular context.
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
		$options   = array( 'closedpostboxes', 'metaboxhidden', 'meta-box-order' );
		$defaults  = array(
			'parent'  => 'wordpoints',
			'options' => array(),
		);

		foreach ( $this->screens[ $context ] as $screen_id => $args ) {

			$args            = array_merge( $defaults, $args );
			$args['options'] = array_merge( $options, $args['options'] );

			// Each user gets to set the options to their liking.
			foreach ( $args['options'] as $option ) {

				$meta_key = "{$option}_{$args['parent']}_page_{$screen_id}";

				if ( 'network' === $context ) {
					$meta_key .= '-network';
				}

				$meta_keys[] = $meta_key;
			}
		}

		return array( new WordPoints_Uninstaller_Metadata( 'user', $meta_keys ) );
	}
}

// EOF
