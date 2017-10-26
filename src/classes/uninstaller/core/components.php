<?php

/**
 * Core components uninstaller class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Uninstaller for WordPoints components.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Core_Components implements WordPoints_RoutineI {

	/**
	 * @since 2.4.0
	 */
	public function run() {

		/** This action is documented in classes/components.php */
		do_action( 'wordpoints_components_register' );

		$components = WordPoints_Components::instance();

		// Uninstall the components.
		foreach ( $components->get() as $component => $data ) {

			if ( isset( $data['installable'] ) ) {

				WordPoints_Class_Autoloader::register_dir(
					WORDPOINTS_DIR . "components/{$component}/classes"
				);

				$installable = $data['installable'];

				$installer = new WordPoints_Uninstaller( new $installable( $component ) );
				$installer->run();

			} elseif ( isset( $data['un_installer'] ) ) {

				WordPoints_Installables::get_installer(
					'component'
					, $component
					, 'uninstall' // Required, but not used.
					, $data['un_installer']
				)
					->uninstall();
			}
		}
	}
}

// EOF
