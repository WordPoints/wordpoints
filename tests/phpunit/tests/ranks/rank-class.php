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
class WordPoints_Rank_Test extends WordPoints_Ranks_UnitTestCase {

	/**
	 * Test that the properties are read only.
	 *
	 * @since 1.7.0
	 *
	 * @expectedIncorrectUsage WordPoints_Rank::__set
	 */
	public function test_properties_are_read_only() {

		$rank = $this->factory->wordpoints_rank->create_and_get();

		$this->assertTrue( isset( $rank->name ) );

		$name = $rank->name;
		$rank->name = 'test';
		$this->assertEquals( $name, $rank->name );
	}
}

// EOF
