<?php

/**
 * Class to un/install the module.
 *
 * @package Test6
 * @since 1.0.0
 */

/**
 * Un/install the module.
 *
 * @since 1.0.0
 */
class WordPointsTests_Module_6_Un_Installer extends WordPoints_Un_Installer_Base {

	/**
	 * @since 1.0.0
	 */
	protected $type = 'module';

	/**
	 * @since 1.0.0
	 */
	protected $uninstall = array(
		'global' => array( 'options' => array( 'wordpoints_tests_module_6' ) ),
	);

	/**
	 * @since 1.0.0
	 */
	public function install_network() {

		add_site_option( 'wordpoints_tests_module_6', 'Testing!' );

		parent::install_network();
	}

	/**
	 * @since 1.0.0
	 */
	public function install_single() {

		add_option( 'wordpoints_tests_module_6', 'Testing!' );

		parent::install_single();
	}
}

return 'WordPointsTests_Module_6_Un_Installer';

// EOF
