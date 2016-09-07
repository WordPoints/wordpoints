<?php

/**
 * A mock un/installer class for use in the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Mock un/installer that tracks the hooks API mode for use in the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Mock_Un_Installer_Hook_Mode
	extends WordPoints_PHPUnit_Mock_Un_Installer {

	/**
	 * The mode of the hooks API when each method was called, indexed by method.
	 *
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	public $mode;

	/**
	 * @since 2.1.0
	 */
	public function install_single() {
		$this->mode[ __FUNCTION__ ] = wordpoints_hooks()->get_current_mode();
	}

	/**
	 * @since 2.1.0
	 */
	public function install_site() {
		$this->mode[ __FUNCTION__ ] = wordpoints_hooks()->get_current_mode();
	}

	/**
	 * @since 2.1.0
	 */
	public function install_network() {
		$this->mode[ __FUNCTION__ ] = wordpoints_hooks()->get_current_mode();
	}

	/**
	 * @since 2.1.0
	 */
	public function uninstall_single() {
		$this->mode[ __FUNCTION__ ] = wordpoints_hooks()->get_current_mode();
	}

	/**
	 * @since 2.1.0
	 */
	public function uninstall_site() {
		$this->mode[ __FUNCTION__ ] = wordpoints_hooks()->get_current_mode();
	}

	/**
	 * @since 2.1.0
	 */
	public function uninstall_network() {
		$this->mode[ __FUNCTION__ ] = wordpoints_hooks()->get_current_mode();
	}

	/**
	 * @since 2.1.0
	 */
	public function update_network_to_1_0_0() {
		$this->mode[ __FUNCTION__ ] = wordpoints_hooks()->get_current_mode();
	}

	/**
	 * @since 2.1.0
	 */
	public function update_site_to_1_0_0() {
		$this->mode[ __FUNCTION__ ] = wordpoints_hooks()->get_current_mode();
	}

	/**
	 * @since 2.1.0
	 */
	public function update_single_to_1_0_0() {
		$this->mode[ __FUNCTION__ ] = wordpoints_hooks()->get_current_mode();
	}
}

// EOF
