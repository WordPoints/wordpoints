<?php

/**
 * Module server class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Represents a remote server for modules.
 *
 * A server is one of many available module providers. It is a module repository,
 * which offers modules for download, and may also provide updates, etc.
 *
 * A server object is usually created automatically based on the Server header of
 * one of the installed modules. This file header specifies the server URL that
 * should be used with that module.
 *
 * A server is interacted with through an API, which actually handles the requests to
 * the remote URL of this server. This class is only intended to represent the server
 * itself.
 *
 * @since 2.4.0
 */
class WordPoints_Module_Server implements WordPoints_Module_ServerI {

	/**
	 * The URL of this server.
	 *
	 * This is the server URL, though it usually doesn't include the scheme.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Construct the class.
	 *
	 * @since 2.4.0
	 *
	 * @param string $url The server's URL, sans the scheme.
	 */
	public function __construct( $url ) {

		$this->url = $url;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_slug() {
		return $this->url;
	}

	/**
	 * Get the full server URL, including the HTTP scheme.
	 *
	 * @since 2.4.0
	 *
	 * @return string The server's full URL.
	 */
	public function get_url() {

		$url = 'http://' . $this->url;

		if ( $this->is_ssl_accessible() ) {
			$url = set_url_scheme( $url, 'https' );
		}

		return $url;
	}

	/**
	 * Check if this server is accessible over SSL.
	 *
	 * @since 2.4.0
	 *
	 * @return bool Whether the server URL can be accessed over SSL.
	 */
	protected function is_ssl_accessible() {

		$transient = 'wrdpnts_' . md5( "module_server_supports_ssl-{$this->url}" );

		$supports_ssl = get_site_transient( $transient );

		// If the transient has expired.
		if ( false === $supports_ssl ) {

			// The cached value is an integer so we can tell when the transient has expired.
			$supports_ssl = 0;

			if ( wp_http_supports( array( 'ssl' ) ) ) {

				$response = wp_safe_remote_get( 'https://' . $this->url );

				if ( ! is_wp_error( $response ) ) {

					$status = wp_remote_retrieve_response_code( $response );

					if ( 200 === (int) $status || 401 === (int) $status ) {
						$supports_ssl = 1;
					}
				}
			}

			set_site_transient( $transient, $supports_ssl, WEEK_IN_SECONDS );
		}

		return (bool) $supports_ssl;
	}

	/**
	 * Get the API used by this server.
	 *
	 * @since 2.4.0
	 *
	 * @return object|false The server API handler, or false if none available.
	 */
	public function get_api() {

		$api_slug = $this->get_api_slug();

		if ( ! $api_slug ) {
			return false;
		}

		return wordpoints_apps()
			->get_sub_app( 'module_server_apis' )
			->get( $api_slug, array( $this ) );
	}

	/**
	 * Gets the slug of the API the remote server offers.
	 *
	 * @since 2.4.0
	 *
	 * @return string|false The API slug, or false if unknown.
	 */
	protected function get_api_slug() {
		return $this->get_api_header();
	}

	/**
	 * Retrieve and parse the module API header from the remote server.
	 *
	 * The remote server can specify the supported API by sending the
	 * x-wordpoints-module-api header. This allows the API to be looked up with
	 * a single HEAD request.
	 *
	 * @since 2.4.0
	 *
	 * @return string|false The slug of the API specified in the header, or false
	 *                      the server doesn't set this header.
	 */
	protected function get_api_header() {

		// Check if there is a cached value available.
		$transient = 'wrdpnts_' . md5( "module_server_api-{$this->url}" );

		$api = get_site_transient( $transient );

		// If the transient has expired.
		if ( false === $api ) {

			$headers = wp_get_http_headers( $this->get_url() );

			if ( isset( $headers['x-wordpoints-module-api'] ) ) {
				$api = sanitize_key( $headers['x-wordpoints-module-api'] );
			}

			// Save it as a string, so we can tell when it has expired.
			set_site_transient( $transient, (string) $api, WEEK_IN_SECONDS );
		}

		return $api;
	}
}

// EOF
