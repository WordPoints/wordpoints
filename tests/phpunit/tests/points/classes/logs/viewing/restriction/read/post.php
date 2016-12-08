<?php

/**
 * Test case for WordPoints_Points_Logs_Viewing_Restriction_Read_Post.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.2.0
 */

/**
 * Tests WordPoints_Points_Logs_Viewing_Restriction_Read_Post.
 *
 * @since 2.2.0
 *
 * @covers WordPoints_Points_Logs_Viewing_Restriction_Read_Post
 */
class WordPoints_Points_Logs_Viewing_Restriction_Read_Post_Test
	extends WordPoints_PHPUnit_TestCase_Points {

	/**
	 * Test that it returns a description.
	 *
	 * @since 2.2.0
	 */
	public function test_get_description() {

		$log = $this->factory->wordpoints->points_log->create_and_get( array() );

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Read_Post(
			$log
		);

		$this->assertNotEmpty( $restriction->get_description() );
	}

	/**
	 * Test that it doesn't apply when the post is public.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_public() {

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_meta' => array( 'post_id' => $this->factory->post->create() ),
			)
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Read_Post(
			$log
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it applies when the post is not public.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_not_public() {

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_meta' => array(
					'post_id' => $this->factory->post->create(
						array( 'post_status' => 'draft' )
					),
				),
			)
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Read_Post(
			$log
		);

		$this->assertTrue( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply when the post is nonexistent.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_nonexistent() {

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array( 'log_meta' => array( 'post_id' => 'a' ) )
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Read_Post(
			$log
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that it doesn't apply when the post is nonexistent.
	 *
	 * @since 2.2.0
	 */
	public function test_applies_no_meta() {

		$log = $this->factory->wordpoints->points_log->create_and_get();

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Read_Post(
			$log
		);

		$this->assertFalse( $restriction->applies() );
	}

	/**
	 * Test that the user can when the post is public.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_public() {

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_meta' => array( 'post_id' => $this->factory->post->create() ),
			)
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Read_Post(
			$log
		);

		$this->assertTrue(
			$restriction->user_can( $this->factory->user->create() )
		);
	}

	/**
	 * Test that the user can't when the post is not public.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_not_public() {

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_meta' => array(
					'post_id' => $this->factory->post->create(
						array( 'post_status' => 'draft' )
					),
				),
			)
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Read_Post(
			$log
		);

		$this->assertFalse(
			$restriction->user_can( $this->factory->user->create() )
		);
	}

	/**
	 * Test that the user can when the post is nonexistent.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_nonexistent() {

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array( 'log_meta' => array( 'post_id' => 'a' ) )
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Read_Post(
			$log
		);

		$this->assertTrue(
			$restriction->user_can( $this->factory->user->create() )
		);
	}

	/**
	 * Test that the user can when the post is nonexistent.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_no_meta() {

		$log = $this->factory->wordpoints->points_log->create_and_get();

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Read_Post(
			$log
		);

		$this->assertTrue(
			$restriction->user_can( $this->factory->user->create() )
		);
	}

	/**
	 * Test that the user can when the post is not public if they have the caps.
	 *
	 * @since 2.2.0
	 */
	public function test_user_can_not_public_has_cap() {

		$log = $this->factory->wordpoints->points_log->create_and_get(
			array(
				'log_meta' => array(
					'post_id' => $this->factory->post->create(
						array( 'post_status' => 'draft' )
					),
				),
			)
		);

		$restriction = new WordPoints_Points_Logs_Viewing_Restriction_Read_Post(
			$log
		);

		$this->assertTrue(
			$restriction->user_can(
				$this->factory->user->create( array( 'role' => 'administrator' ) )
			)
		);
	}
}

// EOF
