<?php

/**
 * A test case for the points component update to 2.1.4.
 *
 * @package WordPoints\Tests
 * @since 2.1.4
 */

/**
 * Test that the points component updates to 2.1.4 properly.
 *
 * @since 2.1.4
 *
 * @group points
 * @group update
 *
 * @covers WordPoints_Points_Un_Installer::update_site_to_2_1_4
 * @covers WordPoints_Points_Un_Installer::update_single_to_2_1_4
 */
class WordPoints_Points_2_1_4_Update_Test extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * @since 2.1.4
	 */
	protected $previous_version = '2.1.0';

	/**
	 * The ID of the first post author.
	 *
	 * @since 2.1.4
	 *
	 * @var int
	 */
	protected $post_author_1;

	/**
	 * The ID of the second post author.
	 *
	 * @since 2.1.4
	 *
	 * @var int
	 */
	protected $post_author_2;

	/**
	 * The ID of the post the second author is author of.
	 *
	 * @since 2.1.4
	 *
	 * @var int
	 */
	protected $post_id;

	/**
	 * Test that it corrects the logs and restores the user's points.
	 *
	 * @since 2.1.4
	 *
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_reversal_log_types
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_hits_with_multiple_logs
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_original_log_ids
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_delete_logs
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_delete_hits
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_clean_other_logs
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_revert_log
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_mark_unreversed
	 */
	public function test_corrects_logs() {

		$this->create_reaction();
		$this->fire_reaction();

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 7, $logs );

		$original_log = $logs[1];

		$this->assertEquals( $this->post_author_1, $logs[0]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[0]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[1]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[1]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[2]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[2]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[3]->user_id );
		$this->assertEquals( 'reverse-post_publish\post', $logs[3]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[4]->user_id );
		$this->assertEquals( 'reverse-post_publish\post', $logs[4]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[5]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[5]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[6]->user_id );
		$this->assertEquals( 'reverse-post_publish\post', $logs[6]->log_type );

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_1, 'points' )
		);

		$this->assertEquals(
			0
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);

		// Simulate the update.
		$this->update_component();

		$logs = $query->get();

		$this->assertCount( 2, $logs );

		$this->assertEquals( $this->post_author_1, $logs[0]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[0]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[1]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[1]->log_type );

		$this->assertEquals( $original_log, $logs[1] );

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_1, 'points' )
		);

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);

		$this->factory->post->update_object(
			$this->post_id
			, array( 'post_status' => 'publish' )
		);

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);

		$this->factory->post->update_object(
			$this->post_id
			, array( 'post_status' => 'draft' )
		);

		$this->assertEquals(
			0
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);

		$this->factory->post->update_object(
			$this->post_id
			, array( 'post_status' => 'publish' )
		);

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);
	}

	/**
	 * Test that it corrects the logs and restores the user's points.
	 *
	 * @since 2.1.4
	 *
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_reversal_log_types
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_hits_with_multiple_logs
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_original_log_ids
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_delete_logs
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_delete_hits
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_clean_other_logs
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_revert_log
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_mark_unreversed
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_legacy_reactor_logs
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_legacy_points_hook_post_ids
	 */
	public function test_corrects_logs_legacy_reactor() {

		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 100 )
		);

		wordpoints_points_register_legacy_post_publish_events( 'post' );

		$importer = new WordPoints_Points_Legacy_Hook_To_Reaction_Importer(
			'wordpoints_post_points_hook'
			, 'points_legacy_post_publish\post'
			, array(
				'points'       => true,
				'post_type'    => true,
				'auto_reverse' => true,
			)
			, 'post_publish'
			, array( 'post\post', 'author', 'user' )
			, 'post_id'
		);

		$importer->import();

		$hooks = wordpoints_hooks();

		$reaction_store = $hooks->get_reaction_store( 'points' );

		$reactions = $reaction_store->get_reactions();

		$this->assertCount( 1, $reactions );

		$this->post_author_1 = $this->factory->user->create();
		$post_id             = $this->factory->post->create(
			array( 'post_author' => $this->post_author_1 )
		);

		$this->factory->post->update_object(
			$post_id
			, array( 'post_status' => 'publish' )
		);

		$this->post_author_2 = $this->factory->user->create();
		$this->post_id       = $this->factory->post->create(
			array( 'post_author' => $this->post_author_2 )
		);

		$actions = $hooks->get_sub_app( 'actions' );

		$slug = 'post';

		$actions->register(
			"post_publish\\{$slug}"
			, 'WordPoints_Hook_Action_Post_Type'
			, array(
				'action' => 'transition_post_status',
				'data'   => array(
					'arg_index'    => array( "post\\{$slug}" => 2 ),
					'requirements' => array( 0 => 'publish' ),
				),
			)
		);

		$actions->register(
			"post_depublish\\{$slug}"
			, 'WordPoints_Hook_Action_Post_Type'
			, array(
				'action' => 'transition_post_status',
				'data'   => array(
					'arg_index'    => array( "post\\{$slug}" => 2 ),
					'requirements' => array( 1 => 'publish' ),
				),
			)
		);

		$this->factory->post->update_object(
			$this->post_id
			, array( 'post_status' => 'publish' )
		);

		$this->factory->post->update_object(
			$this->post_id
			, array( 'post_status' => 'publish' )
		);

		wordpoints_register_post_type_hook_actions( 'post' );

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 4, $logs );

		$this->assertEquals( $this->post_author_1, $logs[0]->user_id );
		$this->assertEquals( 'points_legacy_post_publish\\post', $logs[0]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[1]->user_id );
		$this->assertEquals( 'points_legacy_post_publish\\post', $logs[1]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[2]->user_id );
		$this->assertEquals( 'points_legacy_post_publish\\post', $logs[2]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[3]->user_id );
		$this->assertEquals( 'points_legacy_post_publish\\post', $logs[3]->log_type );

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_1, 'points' )
		);

		$this->assertEquals(
			300
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);

		// Simulate the update.
		$this->update_component();

		$logs = $query->get();

		$this->assertCount( 2, $logs );

		$this->assertEquals( $this->post_author_1, $logs[0]->user_id );
		$this->assertEquals( 'points_legacy_post_publish\\post', $logs[0]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[1]->user_id );
		$this->assertEquals( 'points_legacy_post_publish\\post', $logs[1]->log_type );

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_1, 'points' )
		);

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);

		$this->factory->post->update_object(
			$this->post_id
			, array( 'post_status' => 'publish' )
		);

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);

		wp_delete_post( $this->post_id, true );

		$this->assertEquals(
			0
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);
	}

	/**
	 * Test that it corrects the logs and restores the user's points.
	 *
	 * @since 2.1.4
	 *
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_reversal_log_types
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_hits_with_multiple_logs
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_original_log_ids
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_delete_logs
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_delete_hits
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_clean_other_logs
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_revert_log
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_mark_unreversed
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_legacy_reactor_logs
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_legacy_points_hook_post_ids
	 */
	public function test_corrects_logs_legacy_reactor_legacy_logs() {

		wordpointstests_add_points_hook(
			'wordpoints_post_points_hook'
			, array( 'points' => 100 )
		);

		$this->post_author_1 = $this->factory->user->create();
		$post_id             = $this->factory->post->create(
			array( 'post_author' => $this->post_author_1 )
		);

		$this->factory->post->update_object(
			$post_id
			, array( 'post_status' => 'publish' )
		);

		$this->post_author_2 = $this->factory->user->create();
		$this->post_id       = $this->factory->post->create(
			array( 'post_author' => $this->post_author_2 )
		);

		wordpoints_points_register_legacy_post_publish_events( 'post' );

		$importer = new WordPoints_Points_Legacy_Hook_To_Reaction_Importer(
			'wordpoints_post_points_hook'
			, 'points_legacy_post_publish\post'
			, array(
				'points'       => true,
				'post_type'    => true,
				'auto_reverse' => true,
			)
			, 'post_publish'
			, array( 'post\post', 'author', 'user' )
			, 'post_id'
		);

		$importer->import();

		$hooks = wordpoints_hooks();

		$reaction_store = $hooks->get_reaction_store( 'points' );

		$reactions = $reaction_store->get_reactions();

		$this->assertCount( 1, $reactions );

		$actions = $hooks->get_sub_app( 'actions' );

		$slug = 'post';

		$actions->register(
			"post_publish\\{$slug}"
			, 'WordPoints_Hook_Action_Post_Type'
			, array(
				'action' => 'transition_post_status',
				'data'   => array(
					'arg_index'    => array( "post\\{$slug}" => 2 ),
					'requirements' => array( 0 => 'publish' ),
				),
			)
		);

		$actions->register(
			"post_depublish\\{$slug}"
			, 'WordPoints_Hook_Action_Post_Type'
			, array(
				'action' => 'transition_post_status',
				'data'   => array(
					'arg_index'    => array( "post\\{$slug}" => 2 ),
					'requirements' => array( 1 => 'publish' ),
				),
			)
		);

		$this->factory->post->update_object(
			$this->post_id
			, array( 'post_status' => 'publish' )
		);

		$this->factory->post->update_object(
			$this->post_id
			, array( 'post_status' => 'publish' )
		);

		wordpoints_register_post_type_hook_actions( 'post' );

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 4, $logs );

		$this->assertEquals( $this->post_author_1, $logs[0]->user_id );
		$this->assertEquals( 'post_publish', $logs[0]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[1]->user_id );
		$this->assertEquals( 'post_publish', $logs[1]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[2]->user_id );
		$this->assertEquals( 'points_legacy_post_publish\\post', $logs[2]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[3]->user_id );
		$this->assertEquals( 'points_legacy_post_publish\\post', $logs[3]->log_type );

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_1, 'points' )
		);

		$this->assertEquals(
			300
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);

		// Simulate the update.
		$this->update_component();

		$logs = $query->get();

		$this->assertCount( 2, $logs );

		$this->assertEquals( $this->post_author_1, $logs[0]->user_id );
		$this->assertEquals( 'post_publish', $logs[0]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[1]->user_id );
		$this->assertEquals( 'post_publish', $logs[1]->log_type );

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_1, 'points' )
		);

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);

		$this->factory->post->update_object(
			$this->post_id
			, array( 'post_status' => 'publish' )
		);

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);

		wp_delete_post( $this->post_id, true );

		$this->assertEquals(
			0
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);
	}

	/**
	 * Test that it there must be duplicate hit IDs.
	 *
	 * @since 2.1.4
	 *
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_hits_with_multiple_logs
	 */
	public function test_requires_duplicate_hit_ids() {

		$this->create_reaction();
		$this->fire_reaction();

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 7, $logs );

		wordpoints_update_points_log_meta( $logs[4]->id, 'hook_hit_id', 'test' );

		// Simulate the update.
		$this->update_component();

		$this->assertLogsMessedUp();
	}

	/**
	 * Test that it the duplicate logs must be of the correct log types.
	 *
	 * @since 2.1.4
	 *
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_reversal_log_types
	 */
	public function test_requires_correct_log_types() {

		$this->create_reaction();
		$this->fire_reaction();

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 7, $logs );

		global $wpdb;

		$wpdb->update(
			$wpdb->wordpoints_points_logs
			, array( 'log_type' => 'reverse-test' )
			, array( 'id' => $logs[4]->id )
		);

		// Simulate the update.
		$this->update_component();

		// For the benefit of the assertions.
		$wpdb->update(
			$wpdb->wordpoints_points_logs
			, array( 'log_type' => 'reverse-post_publish\\post' )
			, array( 'id' => $logs[4]->id )
		);

		$this->assertLogsMessedUp();
	}

	/**
	 * Test that it there must be only two logs sharing the same hit ID.
	 *
	 * @since 2.1.4
	 *
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_reversal_log_types
	 */
	public function test_requires_only_two_logs_per_hit() {

		$this->create_reaction();
		$this->fire_reaction();

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 7, $logs );

		$id = $this->factory->wordpoints_points_log->create(
			array(
				'meta' => array(
					'hook_hit_id' => wordpoints_get_points_log_meta(
						$logs[4]->id
						, 'hook_hit_id'
						, true
					),
				),
			)
		);

		// Simulate the update.
		$this->update_component();

		// For the benefit of the assertions.
		global $wpdb;

		$wpdb->delete(
			$wpdb->wordpoints_points_logs
			, array( 'id' => $id )
		);

		$this->assertLogsMessedUp();
	}

	/**
	 * Test that it the post IDs for the logs must match.
	 *
	 * @since 2.1.4
	 *
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_original_log_ids
	 */
	public function test_requires_matching_post_ids() {

		$this->create_reaction();
		$this->fire_reaction();

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 7, $logs );

		wordpoints_update_points_log_meta( $logs[2]->id, 'post\post', 'test' );

		// Simulate the update.
		$this->update_component();

		$this->assertLogsMessedUp();
	}

	/**
	 * Test that it the extra logs must be of the correct log types.
	 *
	 * @since 2.1.4
	 *
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_reversal_log_types
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_clean_other_logs
	 */
	public function test_extra_requires_correct_log_types() {

		$this->create_reaction();
		$this->fire_reaction();

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 7, $logs );

		global $wpdb;

		$wpdb->update(
			$wpdb->wordpoints_points_logs
			, array( 'log_type' => 'test' )
			, array( 'id' => $logs[5]->id )
		);

		// Simulate the update.
		$this->update_component();

		// For the benefit of the assertions.
		$wpdb->update(
			$wpdb->wordpoints_points_logs
			, array( 'log_type' => 'post_publish\\post' )
			, array( 'id' => $logs[5]->id )
		);

		$this->assertExtraLogsMessedUp();
	}

	/**
	 * Test that it the extra logs must be of the correct log types.
	 *
	 * @since 2.1.4
	 *
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_reversal_log_types
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_clean_other_logs
	 */
	public function test_extra_requires_correct_log_types_reverse() {

		$this->create_reaction();
		$this->fire_reaction();

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 7, $logs );

		global $wpdb;

		$wpdb->update(
			$wpdb->wordpoints_points_logs
			, array( 'log_type' => 'reverse-test' )
			, array( 'id' => $logs[6]->id )
		);

		// Simulate the update.
		$this->update_component();

		// For the benefit of the assertions.
		$wpdb->update(
			$wpdb->wordpoints_points_logs
			, array( 'log_type' => 'reverse-post_publish\\post' )
			, array( 'id' => $logs[6]->id )
		);

		$this->assertExtraLogsMessedUp();
	}

	/**
	 * Test that it the post IDs for the logs must match.
	 *
	 * @since 2.1.4
	 *
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_get_reversal_log_types
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_clean_other_logs
	 */
	public function test_extra_requires_matching_post_id() {

		$this->create_reaction();
		$this->fire_reaction();

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 7, $logs );

		wordpoints_update_points_log_meta( $logs[5]->id, 'post\post', 'test' );

		// Simulate the update.
		$this->update_component();

		$this->assertExtraLogsMessedUp();
	}

	/**
	 * Test that it the log must have been reversed.
	 *
	 * @since 2.1.4
	 *
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_clean_other_logs
	 */
	public function test_extra_requires_reversal() {

		$this->create_reaction();
		$this->fire_reaction();

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 7, $logs );

		wordpoints_delete_points_log_meta( $logs[6]->id, 'original_log_id' );

		// Simulate the update.
		$this->update_component();

		$this->assertExtraLogsMessedUp();
	}

	/**
	 * Test that it the extra logs must be of the correct log types.
	 *
	 * @since 2.1.4
	 *
	 * @covers WordPoints_Points_Un_Installer::_2_1_4_clean_other_logs
	 */
	public function test_extra_requires_simultaneous_dates() {

		$this->create_reaction();
		$this->fire_reaction();

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 7, $logs );

		global $wpdb;

		$wpdb->update(
			$wpdb->wordpoints_points_logs
			, array(
				'date' => date(
					'Y-m-d H:i:s'
					, strtotime( $logs[5]->date ) + HOUR_IN_SECONDS
				),
			)
			, array( 'id' => $logs[6]->id )
		);

		// Simulate the update.
		$this->update_component();

		$this->assertExtraLogsMessedUp();
	}

	//
	// Helpers.
	//

	/**
	 * Asserts that the logs are messed up.
	 *
	 * @since 2.1.4
	 */
	protected function assertLogsMessedUp() {

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 7, $logs );

		$this->assertEquals( $this->post_author_1, $logs[0]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[0]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[1]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[1]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[2]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[2]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[3]->user_id );
		$this->assertEquals( 'reverse-post_publish\post', $logs[3]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[4]->user_id );
		$this->assertEquals( 'reverse-post_publish\post', $logs[4]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[5]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[5]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[6]->user_id );
		$this->assertEquals( 'reverse-post_publish\post', $logs[6]->log_type );

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_1, 'points' )
		);

		$this->assertEquals(
			0
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);
	}

	/**
	 * Assert that the extra logs are messed up.
	 *
	 * @since 2.1.4
	 */
	protected function assertExtraLogsMessedUp() {

		$query = new WordPoints_Points_Logs_Query(
			array( 'order' => 'ASC', 'orderby' => 'id' )
		);

		$logs = $query->get();

		$this->assertCount( 4, $logs );

		$this->assertEquals( $this->post_author_1, $logs[0]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[0]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[1]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[1]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[2]->user_id );
		$this->assertEquals( 'post_publish\post', $logs[2]->log_type );

		$this->assertEquals( $this->post_author_2, $logs[3]->user_id );
		$this->assertEquals( 'reverse-post_publish\post', $logs[3]->log_type );

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_1, 'points' )
		);

		$this->assertEquals(
			100
			, wordpoints_get_points( $this->post_author_2, 'points' )
		);
	}

	/**
	 * Create a reaction to the post publish event.
	 *
	 * @since 2.1.4
	 */
	protected function create_reaction() {

		$reaction = $this->create_points_reaction(
			array(
				'event'   => 'post_publish\\post',
				'target'  => array( 'post\post', 'author', 'user' ),
			)
		);

		$this->assertIsReaction( $reaction );
	}

	/**
	 * Fires the reaction by creating some posts.
	 *
	 * @since 2.1.4
	 */
	protected function fire_reaction() {

		$this->post_author_1 = $this->factory->user->create();
		$post_id             = $this->factory->post->create(
			array( 'post_author' => $this->post_author_1 )
		);

		$this->factory->post->update_object(
			$post_id
			, array( 'post_status' => 'publish' )
		);

		$hooks   = wordpoints_hooks();
		$actions = $hooks->get_sub_app( 'actions' );

		$slug = 'post';

		$actions->register(
			"post_publish\\{$slug}"
			,
			'WordPoints_Hook_Action_Post_Type'
			,
			array(
				'action' => 'transition_post_status',
				'data'   => array(
					'arg_index'    => array( "post\\{$slug}" => 2 ),
					'requirements' => array( 0 => 'publish' ),
				),
			)
		);

		$actions->register(
			"post_depublish\\{$slug}"
			,
			'WordPoints_Hook_Action_Post_Type'
			,
			array(
				'action' => 'transition_post_status',
				'data'   => array(
					'arg_index'    => array( "post\\{$slug}" => 2 ),
					'requirements' => array( 1 => 'publish' ),
				),
			)
		);

		$this->post_author_2 = $this->factory->user->create();
		$this->post_id       = $this->factory->post->create(
			array( 'post_author' => $this->post_author_2 )
		);

		$this->factory->post->update_object(
			$this->post_id
			, array( 'post_status' => 'publish' )
		);

		$this->factory->post->update_object(
			$this->post_id
			, array( 'post_status' => 'publish' )
		);

		wordpoints_register_post_type_hook_actions( 'post' );
	}
}

// EOF
