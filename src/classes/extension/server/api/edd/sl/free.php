<?php

/**
 * Easy Digital Downloads Software Licensing Free extension server API class.
 *
 * @package WordPoints
 * @since 2.4.0
 */

/**
 * Extension API for servers using EDD Software Licensing and a shim for free extensions.
 *
 * @since 2.4.0
 */
class WordPoints_Extension_Server_API_EDD_SL_Free
	extends WordPoints_Extension_Server_API_EDD_SL {

	/**
	 * @since 2.4.0
	 */
	public function extension_requires_license(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	) {

		if ( $this->is_free_extension( $extension_data ) ) {
			return false;
		}

		return parent::extension_requires_license( $extension_data );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_extension_license_object(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data,
		$license_key
	) {

		if ( $this->is_free_extension( $extension_data ) ) {
			return false;
		}

		return parent::get_extension_license_object( $extension_data, $license_key );
	}

	/**
	 * Checks if an extension is free.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Extension_Server_API_Extension_DataI $extension_data The extension data.
	 *
	 * @return bool Whether or not this extension is free.
	 */
	protected function is_free_extension(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	) {
		return $this->get_extension_info( $extension_data, 'is_free' );
	}

	/**
	 * @since 2.4.0
	 */
	protected function request_extension_info(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	) {

		$response = parent::request_extension_info( $extension_data );

		if ( ! is_array( $response ) ) {
			return $response;
		}

		$extension_data->set( 'is_free', ! empty( $response['is_free'] ) );

		return $response;
	}
}

// EOF
