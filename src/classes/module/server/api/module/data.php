<?php

/**
 * Module server API module data class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Stores module data retrieved from a module server.
 *
 * @since 2.4.0
 */
class WordPoints_Module_Server_API_Module_Data
	implements WordPoints_Module_Server_API_Module_DataI {

	/**
	 * The ID of the module.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The data.
	 *
	 * @since 2.4.0
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * The name of the option the data is stored in.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $option_name;

	/**
	 * @since 2.4.0
	 *
	 * @param string                    $id     The ID of the module the data is for.
	 * @param WordPoints_Module_ServerI $server The server the module is from.
	 */
	public function __construct( $id, WordPoints_Module_ServerI $server ) {

		$this->id = $id;

		$this->option_name = "wordpoints_module_data-{$server->get_slug()}-{$this->id}";

		$this->data  = wordpoints_get_array_option( $this->option_name, 'site' );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @since 2.4.0
	 */
	public function get( $key ) {

		if ( ! isset( $this->data[ $key ] ) ) {
			return null;
		}

		return $this->data[ $key ];
	}

	/**
	 * @since 2.4.0
	 */
	public function set( $key, $value ) {

		if ( isset( $this->data[ $key ] ) && $this->data[ $key ] === $value ) {
			return true;
		}

		$this->data[ $key ] = $value;

		return update_site_option( $this->option_name, $this->data );
	}

	/**
	 * @since 2.4.0
	 */
	public function delete( $key ) {

		unset( $this->data[ $key ] );

		return update_site_option( $this->option_name, $this->data );
	}
}

// EOF
