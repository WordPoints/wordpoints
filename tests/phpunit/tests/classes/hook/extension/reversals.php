<?php

/**
 * Test case for WordPoints_Hook_Extension_Reversals.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Hook_Extension_Reversals.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Hook_Extension_Reversals
 */
class WordPoints_Hook_Extension_Reversals_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * The slug of the extension being tested.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $extension_slug = 'reversals';

	/**
	 * The extension class being tested.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $extension_class = 'WordPoints_Hook_Extension_Reversals';

	/**
	 * The extension object being tested.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Extension_Reversals
	 */
	protected $extension;

	/**
	 * @since 2.1.0
	 */
	public function setUp() {

		parent::setUp();

		$this->extension = new $this->extension_class();
	}

	/**
	 * Test that an event should hit the target by default.
	 *
	 * @since 2.1.0
	 */
	public function test_should_hit_no_settings() {

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );

		$this->assertTrue( $this->extension->should_hit( $fire ) );
	}

	/**
	 * Test checking whether an event should hit the target.
	 *
	 * @since 2.1.0
	 */
	public function test_should_hit() {

		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$reaction->add_meta( $this->extension_slug, array( 'test_reverse' => 'test_fire' ) );

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );
		$fire->hit();

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_reverse' );

		$this->assertTrue( $this->extension->should_hit( $fire ) );
	}

	/**
	 * Test that a fire should not the target when there are no hits to be reversed.
	 *
	 * @since 2.1.0
	 */
	public function test_should_hit_no_unreversed_hits() {

		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$reaction->add_meta( $this->extension_slug, array( 'test_reverse' => 'test_fire' ) );

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_reverse' );

		$this->assertFalse( $this->extension->should_hit( $fire ) );
	}

	/**
	 * Test that it does nothing after a hit by default.
	 *
	 * @since 2.1.0
	 */
	public function test_after_hit_no_settings() {

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );
		$fire->hit();

		$reverse_fire = new WordPoints_Hook_Fire(
			$event_args
			, $reaction
			, 'test_reverse'
		);

		$reverse_fire->hit();

		$this->extension->after_hit( $reverse_fire );

		$this->assertEquals(
			array()
			, get_metadata( 'wordpoints_hook_hit', $fire->hit_id, 'reverse_fired' )
		);

		$this->assertEquals(
			array()
			, get_metadata( 'wordpoints_hook_hit', $reverse_fire->hit_id, 'reverse_fired' )
		);
	}

	/**
	 * Test that it adds the metadata after a hit.
	 *
	 * @since 2.1.0
	 */
	public function test_after_hit() {

		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$reaction->add_meta( $this->extension_slug, array( 'test_reverse' => 'test_fire' ) );

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );
		$fire->hit();

		$reverse_fire = new WordPoints_Hook_Fire(
			$event_args
			, $reaction
			, 'test_reverse'
		);

		$reverse_fire->hit();

		$this->extension->after_hit( $reverse_fire );

		$this->assertEquals(
			array( '1' )
			, get_metadata( 'wordpoints_hook_hit', $fire->hit_id, 'reverse_fired' )
		);

		$this->assertEquals(
			array()
			, get_metadata( 'wordpoints_hook_hit', $reverse_fire->hit_id, 'reverse_fired' )
		);
	}

	/**
	 * Test that it does nothing after a miss by default.
	 *
	 * @since 2.1.0
	 */
	public function test_after_miss_no_settings() {

		$reaction = $this->factory->wordpoints->hook_reaction->create();

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );
		$fire->hit();

		$reverse_fire = new WordPoints_Hook_Fire(
			$event_args
			, $reaction
			, 'test_reverse'
		);

		$this->extension->after_miss( $reverse_fire );

		$this->assertEquals(
			array()
			, get_metadata( 'wordpoints_hook_hit', $fire->hit_id, 'reverse_fired' )
		);
	}

	/**
	 * Test that it adds the metadata after a miss.
	 *
	 * @since 2.1.0
	 */
	public function test_after_miss() {

		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$reaction->add_meta( $this->extension_slug, array( 'test_reverse' => 'test_fire' ) );

		$event_args = new WordPoints_Hook_Event_Args( array() );

		$fire = new WordPoints_Hook_Fire( $event_args, $reaction, 'test_fire' );
		$fire->hit();

		$reverse_fire = new WordPoints_Hook_Fire(
			$event_args
			, $reaction
			, 'test_reverse'
		);

		$this->extension->after_miss( $reverse_fire );

		$this->assertEquals(
			array( '1' )
			, get_metadata( 'wordpoints_hook_hit', $fire->hit_id, 'reverse_fired' )
		);
	}
}

// EOF
