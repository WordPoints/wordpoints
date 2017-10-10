<?php

/**
 * Test case for WordPoints_Data_Type_Text.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Data_Type_Text.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Data_Type_Text
 */
class WordPoints_Data_Type_Text_Test extends WP_UnitTestCase {

	/**
	 * Test validating the value.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider provider_valid_values
	 */
	public function test_validate_value( $value ) {

		$data_type = new WordPoints_Data_Type_Text( 'test' );

		$this->assertSame( $value, $data_type->validate_value( $value ) );
	}

	/**
	 * Provides valid values.
	 *
	 * @since 2.1.0
	 *
	 * @return array[]
	 */
	public function provider_valid_values() {
		return array(
			'empty' => array( '' ),
			'text'  => array( 'Hello World!' ),
			'slug'  => array( 'test_it' ),
			'int'   => array( '15' ),
			'zero'  => array( '0' ),
		);
	}

	/**
	 * Test validating the value when it is invalid.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider provider_invalid_values
	 */
	public function test_validate_value_invalid( $value ) {

		$data_type = new WordPoints_Data_Type_Text( 'test' );

		$this->assertWPError( $data_type->validate_value( $value ) );
	}

	/**
	 * Provides valid values.
	 *
	 * @since 2.1.0
	 *
	 * @return array[]
	 */
	public function provider_invalid_values() {
		return array(
			array( false ),
			array( true ),
			array( 2 ),
			array( array( 2 ) ),
			array( array() ),
			array( new stdClass() ),
			array( 4.5 ),
		);
	}
}

// EOF
