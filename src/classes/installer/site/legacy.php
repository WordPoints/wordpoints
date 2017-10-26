<?php

/**
 * Legacy site installer class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Installs a legacy installable with an un/installer on a new site on the network.
 *
 * @since 2.4.0
 */
class WordPoints_Installer_Site_Legacy implements WordPoints_RoutineI {

	/**
	 * The un/installer object.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_Un_Installer_Base
	 */
	protected $installer;

	/**
	 * @since 2.4.0
	 *
	 * @param WordPoints_Un_Installer_Base $installer The un/installer object.
	 */
	public function __construct( $installer ) {

		$this->installer = $installer;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		$this->installer->install_on_site( get_current_blog_id() );
	}
}

// EOF
