<?php

/**
 * Mock entity attribute class for the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Mock entity attribute class for the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Mock_Entity_Attr extends WordPoints_Entity_Attr_Field {

	/**
	 * @since 2.1.0
	 */
	protected $storage_type = 'test';

	/**
	 * @since 2.1.0
	 */
	protected $field = 'test_attr';

	/**
	 * @since 2.1.0
	 */
	protected $data_type = 'text';

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return 'Test Attribute';
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
