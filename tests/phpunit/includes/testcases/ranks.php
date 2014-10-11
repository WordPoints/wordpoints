<?php

/**
 * A parent test case class for rank tests.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Parent test case for rank tests.
 *
 * @since 1.7.0
 */
class WordPoints_Ranks_UnitTestCase extends WordPoints_UnitTestCase {

	/**
	 * Set up for the tests.
	 *
	 * @since 1.7.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();
	}

	/**
	 * Set up for each test.
	 *
	 * @since 1.7.0
	 */
	public function setUp() {

		parent::setUp();

		WordPoints_Rank_Types::register_type(
			'test_type'
			, 'WordPoints_Test_Rank_Type'
		);

		WordPoints_Rank_Groups::register_group(
			'test_group'
			, array( 'name' => 'Test Group' )
		);

		WordPoints_Rank_Groups::register_type_for_group( 'test_type', 'test_group' );

		// Rank types will persist, but the ranks themselves are rolled back. So we
		// need to create a base rank each time.
		wordpoints_add_rank( '', 'base', 'test_group', 0 );
	}
}

// EOF
