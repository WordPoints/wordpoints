<?php

/**
 * Easy Digital Downloads Software Licensing module server API class.
 *
 * @package WordPoints
 * @since 2.4.0
 */

/**
 * Module API for servers using Easy Digital Downloads and Software Licensing.
 *
 * @since 2.4.0
 */
class WordPoints_Module_Server_API_EDD_SL
	implements WordPoints_Module_Server_API_UpdatesI,
		WordPoints_Module_Server_API_LicensesI {

	/**
	 * The remote server that is being interacted with.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_Module_ServerI
	 */
	protected $server;

	/**
	 * @since 2.4.0
	 *
	 * @param WordPoints_Module_ServerI $server The server to use the API with.
	 */
	public function __construct( WordPoints_Module_ServerI $server ) {

		$this->server = $server;
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

		return new WordPoints_Module_Server_API_Module_License_EDD_SL(
			$this
			, $module_data
			, $license_key
		);
	}

	/**
	 * @since 2.4.0
	 */
	public function get_module_latest_version(
		WordPoints_Module_Server_API_Module_DataI $module_data
	) {
		return $this->get_module_info( $module_data, 'latest_version' );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_module_package_url(
		WordPoints_Module_Server_API_Module_DataI $module_data
	) {
		return $this->get_module_info( $module_data, 'package' );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_module_changelog(
		WordPoints_Module_Server_API_Module_DataI $module_data
	) {
		return $this->get_module_info( $module_data, 'changelog' );
	}

	/**
	 * Gets a piece of data for a module.
	 *
	 * Will check for a cached version from the module data object, and if not
	 * present, it will request the information from the remote server.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Module_Server_API_Module_DataI $module_data The module data.
	 * @param string                                    $key         The data to get.
	 *
	 * @return mixed The piece of info that was requested, or null if not found.
	 */
	public function get_module_info(
		WordPoints_Module_Server_API_Module_DataI $module_data,
		$key
	) {

		$value = $module_data->get( $key );

		if ( null !== $value ) {
			return $value;
		}

		$this->request_module_info( $module_data );

		return $module_data->get( $key );
	}

	/**
	 * Requests the module information from the remote server.
	 *
	 * And saves it to the module data object.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Module_Server_API_Module_DataI $module_data The module data.
	 *
	 * @return array|WP_Error The response from the server, or an error on failure.
	 */
	protected function request_module_info(
		WordPoints_Module_Server_API_Module_DataI $module_data
	) {

		$response = $this->request(
			'get_version'
			, $module_data->get_id()
			, $module_data->get( 'license_key' )
		);

		if ( ! is_array( $response ) ) {
			return $response;
		}

		if ( isset( $response['new_version'] ) ) {
			$module_data->set( 'latest_version', $response['new_version'] );
		}

		if ( isset( $response['package'] ) ) {
			$module_data->set( 'package', $response['package'] );
		}

		if ( isset( $response['sections']['changelog'] ) ) {
			$module_data->set( 'changelog', $response['sections']['changelog'] );
		}

		return $response;
	}

	/**
	 * Perform a request to a remote server.
	 *
	 * The possible actions include the following:
	 *
	 * - `get_version`        Get the information for a module.
	 * - `activate_license`   Activate the license for a module.
	 * - `deactivate_license` Deactivate the license for a module.
	 * - `check_license`      Check the status of a module's license.
	 *
	 * @since 2.4.0
	 *
	 * @param string $action    The action name for this request.
	 * @param string $module_id The ID of the module the request is for.
	 * @param string $license   The license for this module, if required.
	 *
	 * @return array|WP_Error The response, or an error on failure.
	 */
	public function request( $action, $module_id, $license = '' ) {

		$args = array(
			'timeout' => 15,
			'body' => array(
				'edd_action' => $action,
				'url'        => home_url(),
				'license'    => $license,
				'item_id'    => $module_id,
			),
		);

		$response = wp_safe_remote_post( $this->server->get_url(), $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if (
			$response
			&& isset( $response['sections'] )
			&& is_string( $response['sections'] )
			&& 1 !== preg_match( '~O:\d~', $response['sections'] ) // No object injection.
		) {
			// @codingStandardsIgnoreStart
			$response['sections'] = maybe_unserialize( $response['sections'] );
			// @codingStandardsIgnoreEnd
		}

		return $response;
	}
}

// EOF
