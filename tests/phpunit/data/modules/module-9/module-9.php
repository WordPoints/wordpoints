<?php

/**
 * Module Name: Module 9
 * Author:      J.D. Grimes
 * Author URI:  https://codesymphony.co/
 * Module URI:  https://codesymphony.co/
 * Version:     1.0.0
 * License:     GPLv2+
 * Description: Description.
 * Server:      wordpoints.org
 * ID:          9
 *
 * @package Module9
 */

add_filter( 'wordpoints_server_object_for_module', function ( $server, $module ) {

	if ( '9' !== $module['ID'] ) {
		return $server;
	}

	return new class ( 'test' ) extends WordPoints_Module_Server {

		/**
		 * @since 2.4.0
		 */
		public function get_api() {
			return new class implements
				WordPoints_Module_Server_APII,
				WordPoints_Module_Server_API_LicensesI {

				/**
				 * @since 2.4.0
				 */
				public function get_slug() {
					return 'test';
				}

				/**
				 * @since 2.4.0
				 */
				public function module_requires_license(
					WordPoints_Module_Server_API_Module_DataI $module_data
				) {
					return true;
				}

				/**
				 * @since 2.4.0
				 */
				public function get_module_license_object(
					WordPoints_Module_Server_API_Module_DataI $module_data,
					$license_key
				) {

					return new class implements WordPoints_Module_Server_API_Module_LicenseI,
						WordPoints_Module_Server_API_Module_License_ActivatableI,
						WordPoints_Module_Server_API_Module_License_DeactivatableI {

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
