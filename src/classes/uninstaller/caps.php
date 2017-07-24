<?php

/**
 * Caps uninstaller class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Uninstaller for custom user capabilities.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Caps implements WordPoints_RoutineI {

	/**
	 * Custom capabilities to uninstall.
	 *
	 * @since 2.4.0
	 *
	 * @var string[]
	 */
	protected $caps;

	/**
	 * @since 2.4.0
	 *
	 * @param string[] $caps An array of capabilities to remove.
	 */
	public function __construct( $caps ) {
		$this->caps = $caps;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {
		wordpoints_remove_custom_caps( $this->caps );
	}
}

// EOF
