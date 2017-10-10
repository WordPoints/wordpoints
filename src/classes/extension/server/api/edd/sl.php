<?php

/**
 * Easy Digital Downloads Software Licensing extension server API class.
 *
 * @package WordPoints
 * @since 2.4.0
 */

/**
 * Extension API for servers using Easy Digital Downloads and Software Licensing.
 *
 * @since 2.4.0
 */
class WordPoints_Extension_Server_API_EDD_SL
	implements WordPoints_Extension_Server_APII,
		WordPoints_Extension_Server_API_Updates_InstallableI,
		WordPoints_Extension_Server_API_Updates_ChangelogI,
		WordPoints_Extension_Server_API_LicensesI {

	/**
	 * The slug of the API.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The remote server that is being interacted with.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_Extension_ServerI
	 */
	protected $server;

	/**
	 * @since 2.4.0
	 *
	 * @param string                       $slug   The API's slug.
	 * @param WordPoints_Extension_ServerI $server The server to use the API with.
	 */
	public function __construct( $slug, WordPoints_Extension_ServerI $server ) {

		$this->slug   = $slug;
		$this->server = $server;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_slug() {
		return $this->slug;
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

		return new WordPoints_Extension_Server_API_Extension_License_EDD_SL(
			$this
			, $extension_data
			, $license_key
		);
	}

	/**
	 * @since 2.4.0
	 */
	public function get_extension_latest_version(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	) {
		return $this->get_extension_info( $extension_data, 'latest_version' );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_extension_package_url(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	) {
		return $this->get_extension_info( $extension_data, 'package' );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_extension_changelog(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	) {
		return $this->get_extension_info( $extension_data, 'changelog' );
	}

	/**
	 * Gets a piece of data for an extension.
	 *
	 * Will check for a cached version from the extension data object, and if not
	 * present, it will request the information from the remote server.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Extension_Server_API_Extension_DataI $extension_data The extension data.
	 * @param string                                          $key            The data to get.
	 *
	 * @return mixed The piece of info that was requested, or null if not found.
	 */
	public function get_extension_info(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data,
		$key
	) {

		$value = $extension_data->get( $key );

		if ( null !== $value ) {
			return $value;
		}

		$this->request_extension_info( $extension_data );

		return $extension_data->get( $key );
	}

	/**
	 * Requests the extension information from the remote server.
	 *
	 * And saves it to the extension data object.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Extension_Server_API_Extension_DataI $extension_data The extension data.
	 *
	 * @return array|WP_Error The response from the server, or an error on failure.
	 */
	protected function request_extension_info(
		WordPoints_Extension_Server_API_Extension_DataI $extension_data
	) {

		$license_key = $extension_data->get( 'license_key' );

		$response = $this->request(
			'get_version'
			, $extension_data->get_id()
			, $license_key
		);

		if ( ! is_array( $response ) ) {
			return $response;
		}

		// If the license key wasn't valid, update its status.
		if (
			isset( $response['msg'] )
			&& empty( $response['new_version'] )
			&& $extension_data->get( 'license_status' )
		) {

			$extension_data->delete( 'license_status' );

			if ( $license_key ) {
				$license = $this->get_extension_license_object(
					$extension_data
					, $license_key
				);

				$license->is_valid();
			}

			return $response;

		} elseif (
			$license_key
			&& isset( $response['new_version'] )
			&& 'valid' !== $extension_data->get( 'license_status' )
			&& false === $extension_data->get( 'is_free' )
		) {

			// Or, if this was a success but the license was marked as invalid,
			// update its status.
			$extension_data->delete( 'license_status' );

			$license = $this->get_extension_license_object(
				$extension_data
				, $license_key
			);

			$license->is_valid();
		}

		if ( isset( $response['new_version'] ) ) {
			$extension_data->set( 'latest_version', $response['new_version'] );
		}

		if ( isset( $response['package'] ) ) {
			$extension_data->set( 'package', $response['package'] );
		}

		if ( isset( $response['homepage'] ) ) {
			$extension_data->set( 'homepage', $response['homepage'] );
		}

		if ( isset( $response['sections']['changelog'] ) ) {
			$extension_data->set( 'changelog', $response['sections']['changelog'] );
		}

		return $response;
	}

	/**
	 * Perform a request to a remote server.
	 *
	 * The possible actions include the following:
	 *
	 * - `get_version`        Get the information for an extension.
	 * - `activate_license`   Activate the license for an extension.
	 * - `deactivate_license` Deactivate the license for an extension.
	 * - `check_license`      Check the status of an extension's license.
	 *
	 * @since 2.4.0
	 *
	 * @param string $action       The action name for this request.
	 * @param string $extension_id The ID of the extension the request is for.
	 * @param string $license      The license for this extension, if required.
	 *
	 * @return array|WP_Error The response, or an error on failure.
	 */
	public function request( $action, $extension_id, $license = '' ) {

		$args = array(
			'timeout' => 15,
			'body'    => array(
				'edd_action' => $action,
				'url'        => home_url(),
				'license'    => $license,
				'item_id'    => $extension_id,
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
