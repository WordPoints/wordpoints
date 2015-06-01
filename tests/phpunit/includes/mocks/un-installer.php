<?php

/**
 * Class for mocking an un/installer object.
 *
 * @package WordPoints
 * @since 2.0.0
 */

/**
 * Mock un/installer.
 *
 * Allows access to all protected methods and properties.
 *
 * @since 2.0.0
 */
class WordPoints_Un_Installer_Mock extends WordPoints_Un_Installer_Base {

	/**
	 * The calls to inaccessible methods.
	 *
	 * @since 2.0.0
	 *
	 * @var array[]
	 */
	protected $method_calls = array();

	/**
	 * @since 2.0.0
	 */
	public function &__get( $var ) {
		return $this->$var;
	}

	/**
	 * @since 2.0.0
	 */
	public function __set( $var, $value ) {
		$this->$var = $value;
	}

	/**
	 * @since 2.0.0
	 */
	public function __isset( $var ) {
		return isset( $this->$var );
	}

	/**
	 * @since 2.0.0
	 */
	public function __unset( $var ) {
		unset( $this->$var );
	}

	/**
	 * @since 2.0.0
	 */
	public function __call( $method, $args ) {

		$this->method_calls[] = array( 'method' => $method, 'args' => $args );

		return call_user_func_array( array( $this, $method ), $args );
	}
}

// EOF
