<?php

/**
 * Widgets uninstaller factory.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Factory for uninstall routines for widgets.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Factory_Widgets
	implements WordPoints_Uninstaller_Factory_SingleI,
		WordPoints_Uninstaller_Factory_SiteI {

	/**
	 * The uninstall routines.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_RoutineI[]
	 */
	protected $routines;

	/**
	 * @since 2.4.0
	 *
	 * @param string[] $widgets The base IDs (class names) of the widgets to uninstall.
	 */
	public function __construct( $widgets ) {

		$options = array();

		foreach ( $widgets as $widget ) {
			$options[] = 'widget_' . $widget;
		}

		$this->routines = array(
			new WordPoints_Uninstaller_Callback( 'delete_option', $options ),
		);
	}

	/**
	 * @since 2.4.0
	 */
	public function get_for_single() {
		return $this->routines;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_for_site() {
		return $this->routines;
	}
}

// EOF
