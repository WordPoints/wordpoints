<?php

/**
 * Mock entity context class for the PHPUnit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Mock entity context class for the PHPUnit tests.
 *
 * @since 2.1.0
 */
class WordPoints_PHPUnit_Mock_Entity_Context extends WordPoints_Entity_Context {

	/**
	 * The ID of the current context.
	 *
	 * Note that this is mainly here for backward compatibility, and it is not affected
	 * by context switching.
	 *
	 * @since 2.1.0
	 *
	 * @var string|int
	 */
	public static $current_id = 1;

	/**
	 * The IDs of the current contexts.
	 *
	 * @since 2.2.0
	 *
	 * @var (string|int)[]
	 */
	public static $current_ids = array( 'test_context' => 1 );

	/**
	 * The switch stack.
	 *
	 * @since 2.2.0
	 *
	 * @var (string|int)[][]
	 */
	public static $stack = array();

	/**
	 * Whether switching should fail.
	 *
	 * @since 2.2.0
	 *
	 * @var bool[]
	 */
	public static $fail_switching;

	/**
	 * @since 2.1.0
	 */
	public function get_current_id() {

		if ( isset( self::$current_ids[ $this->slug ] ) ) {
			return self::$current_ids[ $this->slug ];
		}

		return self::$current_id;
	}

	/**
	 * @since 2.2.0
	 */
	public function switch_to( $id ) {

		if ( ! empty( self::$fail_switching[ $this->slug ] ) ) {
			return false;
		}

		self::$stack[ $this->slug ][] = $this->get_current_id();
		self::$current_ids[ $this->slug ] = $id;

		return true;
	}

	/**
	 * @since 2.2.0
	 */
	public function switch_back() {

		if ( empty( self::$stack[ $this->slug ] ) ) {
			return false;
		}

		self::$current_ids[ $this->slug ] = array_pop(
			self::$stack[ $this->slug ]
		);

		return true;
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
}

// EOF
