<?php

/**
 * Mock entity class for the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Mock entity class for the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Mock_Entity extends WordPoints_Entity {

	/**
	 * @since 2.1.0
	 */
	protected $id_field = 'id';

	/**
	 * @since 2.1.0
	 */
	protected $storage_info = array( 'type' => 'test', 'info' => array() );

	/**
	 * @since 2.1.0
	 */
	protected function get_entity( $id ) {

		if ( isset( $this->getter ) ) {
			return parent::get_entity( $id );
		}

		return (object) array( 'id' => $id, 'type' => $this->slug );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return 'Mock Entity';
	}

	/**
	 * @since 2.1.0
	 */
	public function get_storage_info() {
		return $this->storage_info;
	}

	/**
	 * Set a protected property's value.
	 *
	 * @since 2.1.0
	 *
	 * @param string $var   The property name.
	 * @param mixed  $value The property value.
	 */
	public function set( $var, $value ) {
		$this->$var = $value;
	}

	/**
	 * Call a protected method.
	 *
	 * @since 2.1.0
	 *
	 * @param string $method The name of the method.
	 * @param array  $args   The args to pass to the method.
	 *
	 * @return mixed The method's return value.
	 */
	public function call( $method, array $args = array() ) {
		return call_user_func_array( array( $this, $method ), $args );
	}
}

// EOF
