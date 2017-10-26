<?php

/**
 * Caps installer class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Installer for custom user capabilities.
 *
 * @since 2.4.0
 */
class WordPoints_Installer_Caps implements WordPoints_RoutineI {

	/**
	 * Custom capabilities to install.
	 *
	 * @since 2.4.0
	 *
	 * @var string[]
	 */
	protected $caps;

	/**
	 * @since 2.4.0
	 *
	 * @param string[] $caps An array of capabilities of the format processed by
	 *                       {@see wordpoints_add_custom_caps()}.
	 */
	public function __construct( $caps ) {
		$this->caps = $caps;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {
		wordpoints_add_custom_caps( $this->caps );
	}
}

// EOF
