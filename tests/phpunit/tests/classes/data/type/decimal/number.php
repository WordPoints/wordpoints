<?php

/**
 * Test case for WordPoints_Data_Type_Decimal_Number.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.3.0
 */

/**
 * Tests WordPoints_Data_Type_Decimal_Number.
 *
 * @since 2.3.0
 *
 * @covers WordPoints_Data_Type_Decimal_Number
 */
class WordPoints_Data_Type_Decimal_Number_Test extends WP_UnitTestCase {

	/**
	 * Test validating the value.
	 *
	 * @since 2.3.0
	 *
	 * @dataProvider provider_valid_values
	 */
	public function test_validate_value( $value, $expected ) {

		$data_type = new WordPoints_Data_Type_Decimal_Number( 'test' );

		$this->assertSame( $expected, $data_type->validate_value( $value ) );
	}

	/**
	 * Provides valid values.
	 *
	 * @since 2.3.0
	 *
	 * @return array[]
	 */
	public function provider_valid_values() {
		return array(
			array( 15, 15.0 ),
			array( 0, 0.0 ),
			array( -53, -53.0 ),
			array( '15', 15.0 ),
			array( '0', 0.0 ),
			array( '-53', -53.0 ),
			array( 15.0, 15.0 ),
			array( '15.0', 15.0 ),
			array( '75.55', 75.55 ),
			array( '.55', 0.55 ),
			array( '0.55', 0.55 ),
		);
	}

	/**
	 * Test validating the value when it is invalid.
	 *
	 * @since 2.3.0
	 *
	 * @dataProvider provider_invalid_values
	 */
	public function test_validate_value_invalid( $value ) {

		$data_type = new WordPoints_Data_Type_Decimal_Number( 'test' );

		$this->assertWPError( $data_type->validate_value( $value ) );
	}

	/**
	 * Provides valid values.
	 *
	 * @since 2.3.0
	 *
	 * @return array[]
	 */
	public function provider_invalid_values() {
		return array(
			array( false ),
			array( true ),
			array( '10%' ),
			array( array( 2 ) ),
			array( array() ),
			array( new stdClass() ),
			array( '4.5.3' ),
			array( '4,005.3' ),
		);
	}
}

// EOF
