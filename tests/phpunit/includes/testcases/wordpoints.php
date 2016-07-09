<?php

/**
 * A parent class for the WordPoints unit tests.
 *
 * @package WordPoints\Tests
 * @since 1.5.0
 */

/**
 * Test case parent for the unit tests.
 *
 * @since 1.5.0
 * @deprecated 2.1.0 Use WordPoints_PHPUnit_TestCase instead.
 */
abstract class WordPoints_UnitTestCase extends WordPoints_PHPUnit_TestCase {

	/**
	 * Set the version of the points component.
	 *
	 * Since 1.4.0 This was part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
	 * @deprecated 1.8.0 Use self::set_component_db_version() instead.
	 *
	 * @param string $version The version to set. Defaults to 1.0.0.
	 */
	protected function set_points_db_version( $version = '1.0.0' ) {
		$this->set_component_db_version( 'points', $version );
	}

	/**
	 * Get the version of the points component.
	 *
	 * Since 1.4.0 This was part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
	 * @deprecated 1.8.0 Use self::get_component_db_version() instead.
	 *
	 * @return string The version of the points component.
	 */
	protected function get_points_db_version() {
		return $this->get_component_db_version( 'points' );
	}

	/**
	 * Increments the call count for a filter when it gets called.
	 *
	 * The count won't be incremented if there is a count callback for this filter,
	 * and it returns false.
	 *
	 * Since 1.5.0 This was part of the WordPoints_Points_UnitTestCase.
	 *
	 * @since 1.7.0
	 * @deprecated 2.0.0 No longer used.
	 *
	 * @param mixed $var The value being filtered.
	 *
	 * @return mixed $var.
	 */
	public function filter_listner( $var ) {

		_deprecated_function( __METHOD__, '2.0.0' );

		$filter = current_filter();

		if (
			! isset( $this->watched_filters[ $filter ]->count_callback )
			|| call_user_func( $this->watched_filters[ $filter ]->count_callback, $var )
		) {
			$this->watched_filters[ $filter ]->call_count++;
		}

		return $var;
	}
}

// EOF
