<?php

/**
 * A test case for the ranks screen Ajax actions.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the ranks screen Ajax callbacks work correctly.
 *
 * @since 1.7.0
 */
abstract class WordPoints_Ranks_Ajax_UnitTestCase extends WordPoints_Ajax_UnitTestCase {

	/**
	 * The slug of the rank group used in the tests.
	 *
	 * @since 1.7.0
	 *
	 * @type string $rank_group
	 */
	protected $rank_group = 'ajax_test_group';

	/**
	 * The slug of the rank type used in the tests.
	 *
	 * @since 1.7.0
	 *
	 * @type string $rank_type
	 */
	protected $rank_type = 'ajax_test_type';

	/**
	 * Set up before the test by including the Ajax callbacks.
	 *
	 * @since 1.7.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		/**
		 * The Ajax callbacks for the ranks component.
		 *
		 * @since 1.7.0
		 */
		require_once( WORDPOINTS_DIR . '/components/ranks/admin/includes/ajax.php' );
	}

	/**
	 * Set up for each test by creating a new rank group.
	 *
	 * @since 1.7.0
	 */
	public function setUp() {

		parent::setUp();

		// Make sure that the hooks are set up.
		WordPoints_Ranks_Admin_Screen_Ajax::instance()->hooks();

		WordPoints_Rank_Types::register_type(
			$this->rank_type
			, 'WordPoints_Test_Rank_Type'
		);

		WordPoints_Rank_Groups::register_group( $this->rank_group, array() );
		WordPoints_Rank_Groups::register_type_for_group(
			$this->rank_type
			, $this->rank_group
		);
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.7.0
	 */
	public function tearDown() {

		WordPoints_Rank_Types::deregister_type( $this->rank_type );
		WordPoints_Rank_Groups::deregister_group( $this->rank_group );

		parent::tearDown();
	}
}

// EOF
