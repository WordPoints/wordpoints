<?php

/**
 * Easy Digital Downloads Software Licensing Free module server API class.
 *
 * @package WordPoints
 * @since 2.4.0
 */

/**
 * Module API for servers using EDD Software Licensing and a shim for free modules.
 *
 * @since 2.4.0
 */
class WordPoints_Module_Server_API_EDD_SL_Free
	extends WordPoints_Module_Server_API_EDD_SL {

	/**
	 * @since 2.4.0
	 */
	public function module_requires_license(
		WordPoints_Module_Server_API_Module_DataI $module_data
	) {

		if ( $this->is_free_module( $module_data ) ) {
			return false;
		}

		return parent::module_requires_license( $module_data );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_module_license_object(
		WordPoints_Module_Server_API_Module_DataI $module_data,
		$license_key
	) {

		if ( $this->is_free_module( $module_data ) ) {
			return false;
		}

		return parent::get_module_license_object( $module_data, $license_key );
	}

	/**
	 * Checks if a module is free.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Module_Server_API_Module_DataI $module_data The module data.
	 *
	 * @return bool Whether or not this module is free.
	 */
	protected function is_free_module(
		WordPoints_Module_Server_API_Module_DataI $module_data
	) {
		return $this->get_module_info( $module_data, 'is_free' );
	}

	/**
	 * @since 2.4.0
	 */
	protected function request_module_info(
		WordPoints_Module_Server_API_Module_DataI $module_data
	) {

		$response = parent::request_module_info( $module_data );

		if ( ! is_array( $response ) ) {
			return $response;
		}

		$module_data->set( 'is_free', ! empty( $response['is_free'] ) );

		return $response;
	}
}

// EOF
