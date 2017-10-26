<?php

/**
 * A test case for the WordPoints_Rank class.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test the WordPoints_Rank class.
 *
 * @since 1.7.0
 *
 * @group ranks
 */
class WordPoints_Rank_Test extends WordPoints_PHPUnit_TestCase_Ranks {

	/**
	 * Test that the properties are read only.
	 *
	 * @since 1.7.0
	 *
	 * @expectedIncorrectUsage WordPoints_Rank::__set
	 *
	 * @covers WordPoints_Rank::__set
	 * @covers WordPoints_Rank::__isset
	 */
	public function test_properties_are_read_only() {

		$rank = $this->factory->wordpoints->rank->create_and_get();

		$this->assertTrue( isset( $rank->name ) );

		$name       = $rank->name;
		$rank->name = 'test';
		$this->assertSame( $name, $rank->name );
	}
}

// EOF
