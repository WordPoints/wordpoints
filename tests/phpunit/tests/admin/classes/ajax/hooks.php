<?php

/**
 * Test case for WordPoints_Admin_Ajax_Hooks.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests the WordPoints_Admin_Ajax_Hooks class.
 *
 * @since 2.1.0
 *
 * @group ajax
 *
 * @covers WordPoints_Admin_Ajax_Hooks
 */
class WordPoints_Admin_Ajax_Hooks_Test extends WordPoints_PHPUnit_TestCase_Ajax {

	/**
	 * Specs for a request to create a hook reaction.
	 *
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	protected $create_request_spec = array(
		'am_administrator',
		'posts_valid_create_nonce',
		'posts_valid_reaction_store',
		'posts_valid_event',
		'posts_valid_reactor',
		'posts_valid_target',
	);

	/**
	 * Specs for a request to update a hook reaction.
	 *
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	protected $update_request_spec = array(
		'am_administrator',
		'posts_valid_update_nonce',
		'posts_valid_id',
		'posts_valid_reaction_store',
		'posts_valid_event',
		'posts_valid_reactor',
		'posts_valid_target',
	);

	/**
	 * Specs for a request to delete a hook reaction.
	 *
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	protected $delete_request_spec = array(
		'am_administrator',
		'posts_valid_delete_nonce',
		'posts_valid_id',
		'posts_valid_reaction_store',
	);

	/**
	 * Hook reaction store to use in the test.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Reaction_StoreI
	 */
	protected $reaction_store;

	/**
	 * Hook reaction to use in the test.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_ReactionI
	 */
	protected $reaction;

	/**
	 * @since 2.1.0
	 */
	public function setUp() {

		parent::setUp();

		new WordPoints_Admin_Ajax_Hooks();
	}

	/**
	 * Test preparing a hook reaction.
	 *
	 * @since 2.1.0
	 */
	public function test_prepare_hook_reaction() {

		$this->mock_apps();

		$reaction = $this->factory->wordpoints->hook_reaction->create();
		$reaction->add_meta( 'test', 'value' );

		$reaction_guid = wp_json_encode( $reaction->get_guid() );

		$this->assertSameSetsWithIndex(
			array(
				'id'             => $reaction->get_id(),
				'event'          => $reaction->get_event_slug(),
				'reaction_store' => $reaction->get_store_slug(),
				'reactor'        => $reaction->get_reactor_slug(),
				'nonce'          => wp_create_nonce(
					"wordpoints_update_hook_reaction|{$reaction_guid}"
				),
				'delete_nonce'   => wp_create_nonce(
					"wordpoints_delete_hook_reaction|{$reaction_guid}"
				),
				'test'           => 'value',
				'target'         => array( 'test_entity' ),
			)
			, WordPoints_Admin_Ajax_Hooks::prepare_hook_reaction( $reaction )
		);
	}

	/**
	 * Test creating a hook reaction.
	 *
	 * @since 2.1.0
	 */
	public function test_create_hook_reaction() {

		$this->mock_apps();

		$this->reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get();

		$this->generate_request( $this->create_request_spec );

		$response = $this->assertJSONSuccessResponse(
			'wordpoints_admin_create_hook_reaction'
		);

		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertInternalType( 'object', $response->data );

		$this->assertObjectHasAttribute( 'id', $response->data );

		$this->assertObjectHasAttribute( 'event', $response->data );
		$this->assertSame( $_POST['event'], $response->data->event );

		$this->assertObjectHasAttribute( 'reactor', $response->data );
		$this->assertSame( $_POST['reactor'], $response->data->reactor );

		$this->assertObjectHasAttribute( 'target', $response->data );
		$this->assertSame( $_POST['target'], $response->data->target );

		$this->assertObjectHasAttribute( 'reaction_store', $response->data );
		$this->assertSame(
			$_POST['reaction_store']
			, $response->data->reaction_store
		);

		$reaction      = $this->reaction_store->get_reaction( $response->data->id );
		$reaction_guid = wp_json_encode( $reaction->get_guid() );

		$this->assertObjectHasAttribute( 'nonce', $response->data );
		$this->assertSame(
			wp_create_nonce(
				"wordpoints_update_hook_reaction|{$reaction_guid}"
			)
			, $response->data->nonce
		);

		$this->assertObjectHasAttribute( 'delete_nonce', $response->data );
		$this->assertSame(
			wp_create_nonce(
				"wordpoints_delete_hook_reaction|{$reaction_guid}"
			)
			, $response->data->delete_nonce
		);
	}

	/**
	 * Test creating a hook reaction requires valid requests.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_invalid_create_requests
	 *
	 * @param array $request_spec The specs for an invalid request.
	 */
	public function test_create_hook_reaction_invalid_request( $request_spec ) {

		$this->mock_apps();

		$this->reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get();

		$this->generate_request( $request_spec );

		$this->assertJSONErrorResponse( 'wordpoints_admin_create_hook_reaction' );
	}

	/**
	 * Provides specs for invalid reaction create requests.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] A list of invalid create request specs.
	 */
	public function data_provider_invalid_create_requests() {
		return $this->generate_invalid_request_specs( $this->create_request_spec );
	}

	/**
	 * Test creating a hook reaction requires valid reaction settings.
	 *
	 * @since 2.1.0
	 */
	public function test_create_hook_reaction_invalid_reaction_settings() {

		$this->mock_apps();

		$this->reaction_store = $this->factory->wordpoints->hook_reaction_store->create_and_get();

		$request    = $this->create_request_spec;
		$request[3] = 'posts_invalid_event';

		$this->generate_request( $request );

		$response = $this->assertJSONErrorResponse(
			'wordpoints_admin_create_hook_reaction'
		);

		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertObjectHasAttribute( 'errors', $response->data );
		$this->assertCount( 1, $response->data->errors );
		$this->assertSameProperties(
			(object) array(
				'message' => 'Event is invalid.',
				'field'   => array( 'event' ),
			)
			, $response->data->errors[0]
		);
	}

	/**
	 * Test updating a hook reaction.
	 *
	 * @since 2.1.0
	 */
	public function test_update_hook_reaction() {

		$this->mock_apps();

		$this->reaction = $this->factory->wordpoints->hook_reaction->create();

		wordpoints_hooks()->get_sub_app( 'events' )->get_sub_app( 'args' )->register(
			$this->reaction->get_event_slug()
			, 'current:test_entity'
			, 'WordPoints_Hook_Arg'
		);

		$this->reaction->update_meta( 'target', array( 'current:test_entity' ) );

		$this->assertSame(
			array( 'current:test_entity' )
			, $this->reaction->get_meta( 'target' )
		);

		$this->generate_request( $this->update_request_spec );

		$response = $this->assertJSONSuccessResponse(
			'wordpoints_admin_update_hook_reaction'
		);

		$this->assertObjectNotHasAttribute( 'data', $response );

		$this->assertSame(
			array( 'test_entity' )
			, $this->reaction->get_meta( 'target' )
		);
	}

	/**
	 * Test updating a hook reaction requires valid requests.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_invalid_update_requests
	 *
	 * @param array $request_spec The specs for an invalid request.
	 */
	public function test_update_hook_reaction_invalid_request( $request_spec ) {

		$this->mock_apps();

		$this->reaction = $this->factory->wordpoints->hook_reaction->create_and_get();

		wordpoints_hooks()->get_sub_app( 'events' )->get_sub_app( 'args' )->register(
			$this->reaction->get_event_slug()
			, 'current:test_entity'
			, 'WordPoints_Hook_Arg'
		);

		$this->reaction->update_meta( 'target', array( 'current:test_entity' ) );

		$this->assertSame(
			array( 'current:test_entity' )
			, $this->reaction->get_meta( 'target' )
		);

		$this->generate_request( $request_spec );

		$this->assertJSONErrorResponse( 'wordpoints_admin_update_hook_reaction' );

		// The value shouldn't have been updated.
		$this->assertSame(
			array( 'current:test_entity' )
			, $this->reaction->get_meta( 'target' )
		);
	}

	/**
	 * Provides specs for invalid reaction update requests.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] A list of invalid update request specs.
	 */
	public function data_provider_invalid_update_requests() {
		return $this->generate_invalid_request_specs( $this->update_request_spec );
	}

	/**
	 * Test updating a hook reaction requires valid reaction settings.
	 *
	 * @since 2.1.0
	 */
	public function test_update_hook_reaction_invalid_reaction_settings() {

		$this->mock_apps();

		$this->reaction = $this->factory->wordpoints->hook_reaction->create_and_get();

		wordpoints_hooks()->get_sub_app( 'events' )->get_sub_app( 'args' )->register(
			$this->reaction->get_event_slug()
			, 'current:test_entity'
			, 'WordPoints_Hook_Arg'
		);

		$this->reaction->update_meta( 'target', array( 'current:test_entity' ) );

		$this->assertSame(
			array( 'current:test_entity' )
			, $this->reaction->get_meta( 'target' )
		);

		$request    = $this->update_request_spec;
		$request[4] = 'posts_invalid_event';

		$this->generate_request( $request );

		$response = $this->assertJSONErrorResponse(
			'wordpoints_admin_update_hook_reaction'
		);

		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertObjectHasAttribute( 'errors', $response->data );
		$this->assertCount( 1, $response->data->errors );
		$this->assertSameProperties(
			(object) array(
				'message' => 'Event is invalid.',
				'field'   => array( 'event' ),
			)
			, $response->data->errors[0]
		);

		$this->assertSame(
			array( 'current:test_entity' )
			, $this->reaction->get_meta( 'target' )
		);
	}

	/**
	 * Test deleting a hook reaction.
	 *
	 * @since 2.1.0
	 */
	public function test_delete_hook_reaction() {

		$this->mock_apps();

		$this->reaction = $this->factory->wordpoints->hook_reaction->create();

		$this->generate_request( $this->delete_request_spec );

		$this->assertJSONSuccessResponse( 'wordpoints_admin_delete_hook_reaction' );

		$reaction_store = wordpoints_hooks()->get_reaction_store(
			$this->reaction->get_store_slug()
		);

		$this->assertFalse(
			$reaction_store->reaction_exists( $this->reaction->get_id() )
		);
	}

	/**
	 * Test deleting a hook reaction requires valid requests.
	 *
	 * @since 2.1.0
	 *
	 * @dataProvider data_provider_invalid_delete_requests
	 *
	 * @param array $request_spec The specs for an invalid request.
	 */
	public function test_delete_hook_reaction_invalid_request( $request_spec ) {

		$this->mock_apps();

		$this->reaction = $this->factory->wordpoints->hook_reaction->create_and_get();

		$this->generate_request( $request_spec );

		$this->assertJSONErrorResponse( 'wordpoints_admin_delete_hook_reaction' );

		$reaction_store = wordpoints_hooks()->get_reaction_store(
			$this->reaction->get_store_slug()
		);

		$this->assertTrue(
			$reaction_store->reaction_exists( $this->reaction->get_id() )
		);
	}

	/**
	 * Provides specs for invalid reaction delete requests.
	 *
	 * @since 2.1.0
	 *
	 * @return array[] A list of invalid delete request specs.
	 */
	public function data_provider_invalid_delete_requests() {
		return $this->generate_invalid_request_specs( $this->delete_request_spec );
	}

	/**
	 * @since 2.1.0
	 */
	public function fulfill_posts_requirement( $requirement_parts ) {

		if ( isset( $requirement_parts[3] ) && 'nonce' === $requirement_parts[3] ) {

			if ( 'invalid' === $requirement_parts[1] ) {
				$_POST['nonce'] = 'invalid';
				return;
			}

			switch ( $requirement_parts[2] ) {

				case 'create':
					$_POST['nonce'] = WordPoints_Admin_Ajax_Hooks::get_create_nonce(
						$this->reaction_store
					);
				break;

				case 'update':
					$_POST['nonce'] = WordPoints_Admin_Ajax_Hooks::get_update_nonce(
						$this->reaction
					);
				break;

				case 'delete':
					$_POST['nonce'] = WordPoints_Admin_Ajax_Hooks::get_delete_nonce(
						$this->reaction
					);
				break;
			}

		} else {

			parent::fulfill_posts_requirement( $requirement_parts );
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function get_valid_posts_value( $query_arg ) {

		switch ( $query_arg ) {

			case 'event':
				return ( $this->reaction )
					? $this->reaction->get_event_slug()
					: $this->factory->wordpoints->hook_event->create();

			case 'target':
				return array( $this->factory->wordpoints->entity->create() );

			case 'reactor':
				return ( $this->reaction )
					? $this->reaction->get_reactor_slug()
					: $this->factory->wordpoints->hook_reactor->create();

			case 'reaction_store':
				return ( $this->reaction )
					? $this->reaction->get_store_slug()
					: $this->reaction_store->get_slug();

			case 'id':
				return $this->reaction->get_id();
		}

		return parent::get_valid_posts_value( $query_arg );
	}
}

// EOF
