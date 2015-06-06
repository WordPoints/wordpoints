<?php

/**
 * Module installer skin class for use in the PHPUnit tests.
 *
 * @package WordPoints\Tests
 * @since 2.0.0
 */

/**
 * Module installer skin for use in the tests.
 *
 * @since 2.0.0
 */
class WordPoints_Module_Installer_Skin_TestDouble extends WordPoints_Module_Installer_Skin {

	/**
	 * A count of the number of times the header was shown.
	 *
	 * @since 2.0.0
	 *
	 * @type int $header_shown
	 */
	public $header_shown = 0;

	/**
	 * A count of the number of times the footer was shown.
	 *
	 * @since 2.0.0
	 *
	 * @type int $footer_shown
	 */
	public $footer_shown = 0;

	/**
	 * A list of errors reported by the skin.
	 *
	 * @since 2.0.0
	 *
	 * @type string[] $errors
	 */
	public $errors = array();

	/**
	 * A list of the feedback displayed to the user.
	 *
	 * @since 2.0.0
	 *
	 * @type string[]
	 */
	public $feedback;

	/**
	 * @since 2.0.0
	 */
	public function header() {
		$this->header_shown++;
	}

	/**
	 * @since 2.0.0
	 */
	public function footer() {
		$this->footer_shown++;
	}

	/**
	 * @since 2.0.0
	 */
	public function error( $errors ) {
		$this->errors[] = $errors;
	}

	/**
	 * @since 2.0.0
	 */
	public function feedback( $string ) {
		$this->feedback[] = $string;
	}
}

// EOF
