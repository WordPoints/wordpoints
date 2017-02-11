<?php

/**
 * Test case for WordPoints_Data_Type.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Data_Type.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Data_Type
 */
class WordPoints_Data_Type_Test extends WP_UnitTestCase {

	/**
	 * Test getting the slug.
	 *
	 * @since 2.1.0
	 */
	public function test_get_slug() {

		$data_type = new WordPoints_PHPUnit_Mock_Data_type( 'test' );

		$this->assertSame( 'test', $data_type->get_slug() );
	}
}

// EOF
