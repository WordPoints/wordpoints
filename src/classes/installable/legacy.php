<?php

/**
 * Legacy installable class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Shim for legacy installables that still have un/installers.
 *
 * This is useful in back-compat code, so that we can still use the new installables
 * API to manage updates and installation on new sites added to the network.
 *
 * @since 2.4.0
 */
class WordPoints_Installable_Legacy extends WordPoints_Installable_Basic {

	/**
	 * @since 2.4.0
	 */
	public function get_update_routines() {

		$db_version = $this->get_db_version();

		if ( $db_version && version_compare( $this->version, $db_version, '>' ) ) {
			$installer = WordPoints_Installables::get_installer( $this->type, $this->slug );
			$installer->update( $db_version, $this->version );
		}

		return parent::get_update_routines();
	}

	/**
	 * @since 2.4.0
	 */
	public function get_install_routines() {

		$install_routines = parent::get_install_routines();

		$install_routines['site'] = new WordPoints_Installer_Site_Legacy(
			WordPoints_Installables::get_installer( $this->type, $this->slug )
		);

		return $install_routines;
	}
}

// EOF
