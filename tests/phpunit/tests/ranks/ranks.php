<?php

/**
 * A test case for the ranks API.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * Test that the ranks API works.
 *
 * @since 1.7.0
 *
 * @group ranks
 */
class WordPoints_Ranks_Test extends WordPoints_PHPUnit_TestCase_Ranks {

	/**
	 * The ID of the user used in the test.
	 *
	 * @since 1.9.0
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * Set up before the tests.
	 *
	 * @since 1.7.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		WordPoints_Rank_Types::register_type(
			__CLASS__
			, 'WordPoints_PHPUnit_Mock_Rank_Type'
		);

		WordPoints_Rank_Groups::register_group(
			__CLASS__
			, array( 'name' => __CLASS__ )
		);

		WordPoints_Rank_Groups::register_type_for_group( __CLASS__, __CLASS__ );
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.7.0
	 */
	public static function tearDownAfterClass() {

		WordPoints_Rank_Groups::deregister_group( __CLASS__ );
		WordPoints_Rank_Types::deregister_type( __CLASS__ );

		parent::tearDownAfterClass();
	}

	/**
	 * Test that a valid rank type must be passed to add a rank.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_add_rank
	 */
	public function test_add_requires_valid_type() {

		$rank = wordpoints_add_rank(
			'Test Rank'
			, 'not_a_type'
			, 'test_group'
			, 1
			, array( 'test_meta' => 'ranks' )
		);

		$this->assertFalse( $rank );
	}

	/**
	 * Test that a valid rank group must be passed to add a rank.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_add_rank
	 */
	public function test_add_requires_valid_group() {

		$rank = wordpoints_add_rank(
			'Test Rank'
			, 'test_type'
			, 'not_a_group'
			, 1
			, array( 'test_meta' => 'ranks' )
		);

		$this->assertFalse( $rank );
	}

	/**
	 * Test that valid metadata must be passed to add a rank.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_add_rank
	 */
	public function test_add_requires_valid_metadata() {

		$rank = wordpoints_add_rank(
			'Test Rank'
			, 'test_type'
			, 'test_group'
			, 1
			, array( 'not' => 'ranks' )
		);

		$this->assertFalse( $rank );
	}

	/**
	 * Test adding a rank.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_add_rank
	 */
	public function test_add_rank() {

		$rank = wordpoints_add_rank(
			'Test Rank'
			, 'test_type'
			, 'test_group'
			, 1
			, array( 'test_meta' => 'ranks' )
		);

		$this->assertInternalType( 'int', $rank );

		$this->assertSame(
			'ranks'
			, wordpoints_get_rank_meta( $rank, 'test_meta', true )
		);
	}

	/**
	 * Test adding a rank with emojis.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_add_rank
	 */
	public function test_add_rank_with_emoji() {

		global $wpdb;

		if ( 'utf8mb4' !== $wpdb->charset ) {
			$this->markTestSkipped( 'wpdb database charset must be utf8mb4.' );
		}

		$name = "\xf0\x9f\x98\x8e Smiler";

		$rank = wordpoints_add_rank(
			$name
			, 'test_type'
			, 'test_group'
			, 1
			, array( 'test_meta' => 1 )
		);

		$this->assertInternalType( 'int', $rank );

		$rank = wordpoints_get_rank( $rank );

		$this->assertSame( $name, $rank->name );
	}

	/**
	 * Test adding a rank encodes emojis in the name if needed.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_add_rank
	 */
	public function test_add_rank_with_emoji_utf8() {

		$filter = new WordPoints_PHPUnit_Mock_Filter( 'utf8' );
		add_filter( 'pre_get_col_charset', array( $filter, 'filter' ) );

		$rank = wordpoints_add_rank(
			"\xf0\x9f\x98\x8e Smiler"
			, 'test_type'
			, 'test_group'
			, 1
			, array( 'test_meta' => 1 )
		);

		$this->assertInternalType( 'int', $rank );

		$rank = wordpoints_get_rank( $rank );

		$this->assertSame( '&#x1f60e; Smiler', $rank->name );
	}

	/**
	 * Test that updating a rank requires a valid rank ID.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_update_rank
	 */
	public function test_update_rank_requires_valid_id() {

		$rank_id = $this->factory->wordpoints->rank->create();

		$result = wordpoints_update_rank(
			$rank_id + 5
			, 'A test'
			, __CLASS__
			, __CLASS__
			, 0
			, array( 'test_meta' => __CLASS__ )
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test that updating a rank requires a valid rank type.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_update_rank
	 */
	public function test_update_rank_requires_valid_type() {

		$rank_id = $this->factory->wordpoints->rank->create();

		$result = wordpoints_update_rank(
			$rank_id
			, 'A test'
			, 'not_a_type'
			, __CLASS__
			, 0
			, array( 'test_meta' => __CLASS__ )
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test that updating a rank requires a valid rank group.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_update_rank
	 */
	public function test_update_rank_requires_valid_group() {

		$rank_id = $this->factory->wordpoints->rank->create();

		$result = wordpoints_update_rank(
			$rank_id
			, 'A test'
			, __CLASS__
			, 'not_a_group'
			, 0
			, array( 'test_meta' => __CLASS__ )
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test that updating a rank requires valid meta.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_update_rank
	 */
	public function test_update_rank_requires_valid_meta() {

		$rank_id = $this->factory->wordpoints->rank->create();

		$result = wordpoints_update_rank(
			$rank_id
			, 'A test'
			, __CLASS__
			, __CLASS__
			, 0
			, array( 'not' => 'correct' )
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test updating a rank.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_update_rank
	 * @covers ::wordpoints_get_rank
	 */
	public function test_update_rank() {

		$this->factory->wordpoints->rank->create_many(
			2
			, array( 'group' => __CLASS__, 'type' => __CLASS__ )
		);

		$rank_id = $this->factory->wordpoints->rank->create(
			array( 'group' => __CLASS__, 'type' => __CLASS__ )
		);

		$result = wordpoints_update_rank(
			$rank_id
			, 'A test'
			, __CLASS__
			, __CLASS__
			, 1
			, array( 'test_meta' => __CLASS__ )
		);

		$this->assertTrue( $result );

		$rank = wordpoints_get_rank( $rank_id );

		$rank_group = WordPoints_Rank_Groups::get_group( $rank->rank_group );

		$this->assertSame( $rank_id, $rank->id );
		$this->assertSame( 'A test', $rank->name );
		$this->assertSame( __CLASS__, $rank->type );
		$this->assertSame( __CLASS__, $rank->rank_group );
		$this->assertSame( 1, $rank_group->get_rank_position( $rank_id ) );
	}

	/**
	 * Test updating a rank with emojis.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_update_rank
	 */
	public function test_update_rank_with_emoji() {

		global $wpdb;

		if ( 'utf8mb4' !== $wpdb->charset ) {
			$this->markTestSkipped( 'wpdb database charset must be utf8mb4.' );
		}

		$name = "\xf0\x9f\x98\x8e Smiler";

		$rank_id = $this->factory->wordpoints->rank->create();

		$result = wordpoints_update_rank(
			$rank_id
			, $name
			, __CLASS__
			, __CLASS__
			, 0
			, array( 'test_meta' => 1 )
		);

		$this->assertTrue( $result );

		$rank = wordpoints_get_rank( $rank_id );

		$this->assertSame( $name, $rank->name );
	}

	/**
	 * Test updating a rank encodes emojis in the name if needed.
	 *
	 * @since 2.0.0
	 *
	 * @covers ::wordpoints_update_rank
	 */
	public function test_update_rank_with_emoji_utf8() {

		$filter = new WordPoints_PHPUnit_Mock_Filter( 'utf8' );
		add_filter( 'pre_get_col_charset', array( $filter, 'filter' ) );

		$rank_id = $this->factory->wordpoints->rank->create();

		$result = wordpoints_update_rank(
			$rank_id
			, "\xf0\x9f\x98\x8e Smiler"
			, __CLASS__
			, __CLASS__
			, 0
			, array( 'test_meta' => 1 )
		);

		$this->assertTrue( $result );

		$rank = wordpoints_get_rank( $rank_id );

		$this->assertSame( '&#x1f60e; Smiler', $rank->name );
	}

	/**
	 * Test deleting a rank.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_delete_rank
	 */
	public function test_delete_rank() {

		$rank_id = $this->factory->wordpoints->rank->create();

		$result = wordpoints_delete_rank( $rank_id );

		$this->assertTrue( $result );
		$this->assertSame( array(), wordpoints_get_rank_meta( $rank_id ) );
	}

	/**
	 * Test formatting a rank for display.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_format_rank
	 */
	public function test_format_rank() {

		$rank = $this->factory->wordpoints->rank->create_and_get();

		$this->listen_for_filter( 'wordpoints_format_rank' );

		$this->assertSame(
			'<span class="wordpoints-rank">' . $rank->name . '</span>'
			, wordpoints_format_rank( $rank->ID, 'unittests' )
		);

		$this->assertSame( 1, $this->filter_was_called( 'wordpoints_format_rank' ) );
	}

	/**
	 * Test formatting a rank with an invalid ID.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_format_rank
	 */
	public function test_format_invalid_rank() {

		$rank_id = $this->factory->wordpoints->rank->create();

		wordpoints_delete_rank( $rank_id );

		$this->assertFalse( wordpoints_format_rank( $rank_id, 'unittests' ) );
	}

	/**
	 * Test rank caching.
	 *
	 * @since 1.7.0
	 *
	 * @covers ::wordpoints_get_rank
	 * @covers ::wordpoints_update_rank
	 * @covers ::wordpoints_delete_rank
	 */
	public function test_ranks_are_cached() {

		// Listen for get-rank database queries.
		$this->listen_for_filter( 'query', array( $this, 'is_wordpoints_get_rank_query' ) );

		$rank_id = $this->factory->wordpoints->rank->create();

		// Get the rank.
		wordpoints_get_rank( $rank_id );

		// The database should have been queried once.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// Get the rank again.
		$rank = wordpoints_get_rank( $rank_id );

		// The database should still have been called only once.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// The cache should be invalidated when the rank is updated.
		wordpoints_update_rank(
			$rank_id
			, $rank->name
			, $rank->type
			, $rank->rank_group
			, 1
			, array( 'test_meta' => true )
		);

		// Get the rank again.
		wordpoints_get_rank( $rank_id );

		// The database should have been queried again.
		$this->assertSame( 2, $this->filter_was_called( 'query' ) );

		// Delete the rank.
		wordpoints_delete_rank( $rank_id );

		// Get the rank again.
		wordpoints_get_rank( $rank_id );

		// The database should have been queried again.
		$this->assertSame( 3, $this->filter_was_called( 'query' ) );
	}

	/**
	 * Test user rank caching.
	 *
	 * @since 1.9.0
	 *
	 * @covers ::wordpoints_update_user_rank
	 * @covers ::wordpoints_get_user_rank
	 * @covers ::wordpoints_delete_rank
	 * @covers WordPoints_Rank_Group::move_rank
	 */
	public function test_user_ranks_cached() {

		$this->user_id = $this->factory->user->create();
		$rank_id       = $this->factory->wordpoints->rank->create();
		$rank_id_2     = $this->factory->wordpoints->rank->create(
			array( 'position' => 2 )
		);

		wordpoints_update_user_rank( $this->user_id, $rank_id );

		// Listen for get user rank database queries.
		$this->listen_for_filter(
			'query'
			, array( $this, 'is_wordpoints_user_rank_query' )
		);

		// Get the user's rank.
		$rank__id = wordpoints_get_user_rank( $this->user_id, $this->rank_group );

		$this->assertSame( $rank_id, $rank__id );

		// The database should have been queried once.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// Get the user's rank again.
		wordpoints_get_user_rank( $this->user_id, $this->rank_group );

		// The database should still have been called only once.
		$this->assertSame( 1, $this->filter_was_called( 'query' ) );

		// The cache should be invalidated when the user's rank is updated.
		wordpoints_update_user_rank( $this->user_id, $rank_id_2 );

		// Get the user's rank again.
		$rank__id = wordpoints_get_user_rank( $this->user_id, $this->rank_group );

		// The database should have been queried again.
		$this->assertSame( 2, $this->filter_was_called( 'query' ) );

		$this->assertSame( $rank_id_2, $rank__id );

		// Move the rank.
		$rank_id_3 = $this->factory->wordpoints->rank->create(
			array( 'position' => 3 )
		);

		WordPoints_Rank_Groups::get_group( $this->rank_group )
			->move_rank( $rank_id_2, 3 );

		// Get the rank again.
		$rank__id = wordpoints_get_user_rank( $this->user_id, $this->rank_group );

		// The user will end up on the same rank again, because the maybe increase
		// user rank function will always return true. This pushes them all the way
		// to the top, which will be this rank again.
		$this->assertSame( $rank_id_2, $rank__id );

		// The database should have been queried again, since the cache is empty
		// from updating the users rank back to $rank_id_2.
		$this->assertSame( 3, $this->filter_was_called( 'query' ) );

		// Delete the rank.
		wordpoints_delete_rank( $rank_id_2 );

		// Get the rank again.
		$rank__id = wordpoints_get_user_rank( $this->user_id, $this->rank_group );

		$this->assertSame( $rank_id_3, $rank__id );

		// The database should have been queried again.
		$this->assertSame( 4, $this->filter_was_called( 'query' ) );
	}

	/**
	 * @since 1.9.0
	 */
	public function is_wordpoints_user_rank_query( $sql ) {

		global $wpdb;

		return false !== strpos(
			$sql
			, "
					SELECT `rank_id`
					FROM `{$wpdb->wordpoints_user_ranks}`
					WHERE `user_id` = {$this->user_id}
						AND `rank_group` = '{$this->rank_group}'"
		);
	}

	/**
	 * @since 1.9.0
	 */
	public function is_wordpoints_users_with_rank_query( $sql ) {

		global $wpdb;

		return 0 === strpos( $sql, "SELECT `user_id`\nFROM `{$wpdb->wordpoints_user_ranks}`" );
	}
}

// EOF
