<?php

/**
 * Extension Name: Module 9
 * Author:         J.D. Grimes
 * Author URI:     https://codesymphony.co/
 * Extension URI:  https://codesymphony.co/
 * Version:        1.0.0
 * License:        GPLv2+
 * Description:    Description.
 * Server:         wordpoints.org
 * ID:             9
 *
 * @package Extension9
 */

add_filter( 'wordpoints_server_object_for_extension', function ( $server, $extension ) {

	if ( '9' !== $extension['ID'] ) {
		return $server;
	}

	return new class ( 'test' ) extends WordPoints_Extension_Server {

		/**
		 * @since 2.4.0
		 */
		public function get_api() {
			return new class implements
				WordPoints_Extension_Server_APII,
				WordPoints_Extension_Server_API_LicensesI {

				/**
				 * @since 2.4.0
				 */
				public function get_slug() {
					return 'test';
				}

				/**
				 * @since 2.4.0
				 */
				public function extension_requires_license(
					WordPoints_Extension_Server_API_Extension_DataI $extension_data
				) {
					return true;
				}

				/**
				 * @since 2.4.0
				 */
				public function get_extension_license_object(
					WordPoints_Extension_Server_API_Extension_DataI $extension_data,
					$license_key
				) {

					return new class implements WordPoints_Extension_Server_API_Extension_LicenseI,
						WordPoints_Extension_Server_API_Extension_License_ActivatableI,
						WordPoints_Extension_Server_API_Extension_License_DeactivatableI {

						/**
						 * @since 2.4.0
						 */
						public function is_valid() {
							return true;
						}

						/**
						 * @since 2.4.0
						 */
						public function is_activatable() {
							return true;
						}

						/**
						 * @since 2.4.0
						 */
						public function is_active() {
							return true;
						}

						/**
						 * @since 2.4.0
						 */
						public function activate() {
							return true;
						}

						/**
						 * @since 2.4.0
						 */
						public function is_deactivatable() {
							return true;
						}

						/**
						 * @since 2.4.0
						 */
						public function deactivate() {
							return true;
						}
					};
				}
			};
		}
	};

}, 10, 2 );

// EOF
