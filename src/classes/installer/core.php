<?php

/**
 * Core installer class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Runs the main install routine for WordPoints core.
 *
 * @since 2.4.0
 */
class WordPoints_Installer_Core implements WordPoints_RoutineI {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		$data = wordpoints_get_maybe_network_option( 'wordpoints_data' );

		// Add plugin data.
		if ( ! is_array( $data ) ) {

			wordpoints_update_maybe_network_option(
				'wordpoints_data',
				array(
					'version'    => WORDPOINTS_VERSION,
					'components' => array(), // Components use this to store data.
					'modules'    => array(), // Modules can use this to store data.
				)
			);

		} else {

			// Make sure the version is set properly even if the data is already
			// there, in case the plugin is being reactivated and things had been
			// corrupted somehow.
			$data['version'] = WORDPOINTS_VERSION;

			wordpoints_update_maybe_network_option( 'wordpoints_data', $data );
		}
	}
}

// EOF
