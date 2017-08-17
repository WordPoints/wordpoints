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
	public function get_update_routine_factories() {

		$db_version = $this->get_db_version();

		if ( $db_version && version_compare( $this->version, $db_version, '>' ) ) {

			$network = null;

			if ( 'module' === $this->type && is_multisite() ) {
				$network = is_wordpoints_module_active_for_network(
					wordpoints_module_basename(
						WordPoints_Modules::get_data( $this->slug, 'raw_file' )
					)
				);
			}

			$installer = WordPoints_Installables::get_installer( $this->type, $this->slug );
			$installer->update( $db_version, $this->version, $network );
		}

		return parent::get_update_routine_factories();
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
