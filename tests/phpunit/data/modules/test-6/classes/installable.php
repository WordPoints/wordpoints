<?php

/**
 * Installable class for the extension.
 *
 * @package Test6
 * @since 1.0.0
 */

/**
 * Installable for this extension.
 *
 * @since 1.0.0
 */
class WordPoints_Test_6_Installable extends WordPoints_Installable_Extension {

	/**
	 * @since 1.0.0
	 */
	public function get_install_routines() {

		$install_routines = parent::get_install_routines();

		$installer = new WordPoints_Installer_Option(
			'wordpoints_tests_module_6'
			, 'Testing!'
			, true
		);

		$install_routines['single'][]  = $installer;
		$install_routines['network'][] = $installer;

		return $install_routines;
	}

	/**
	 * @since 1.0.0
	 */
	protected function get_uninstall_routine_factories() {

		$uninstall_routine_factories = parent::get_uninstall_routine_factories();

		$uninstall_routine_factories[] = new WordPoints_Uninstaller_Factory_Options(
			array( 'global' => array( 'wordpoints_tests_module_6' ) )
		);

		return $uninstall_routine_factories;
	}
}

// EOF
