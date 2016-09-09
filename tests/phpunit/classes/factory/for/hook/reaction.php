<?php

/**
 * Hook reaction factory class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Factory for hook reactions, for use in the unit tests.
 *
 * @since 2.1.0
 *
 * @method WordPoints_Hook_ReactionI create( $args = array(), $generation_definitions = null )
 * @method WordPoints_Hook_ReactionI create_and_get( $args = array(), $generation_definitions = null )
 * @method WordPoints_Hook_ReactionI[] create_many( $count, $args = array(), $generation_definitions = null )
 */
class WordPoints_PHPUnit_Factory_For_Hook_Reaction extends WP_UnitTest_Factory_For_Thing {

	/**
	 * @since 2.1.0
	 */
	public function __construct( $factory = null ) {

		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'event'          => 'test_event',
			'reactor'        => 'test_reactor',
			'reaction_store' => 'test_reaction_store',
			'description'    => new WP_UnitTest_Generator_Sequence(
				'Hook reaction description %s'
			),
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function create_object( $args ) {

		if ( ! isset( WordPoints_PHPUnit_TestCase::$backup_app ) ) {
			WordPoints_PHPUnit_TestCase::mock_apps();
		}

		$hooks    = wordpoints_hooks();
		$reactors = $hooks->get_sub_app( 'reactors' );

		if ( ! $reactors->is_registered( $args['reactor'] ) ) {
			$this->factory->hook_reactor->create(
				array( 'slug' => $args['reactor'] )
			);
		}

		if ( ! $hooks->get_sub_app( 'events' )->is_registered( $args['event'] ) ) {

			if ( 'test_event' === $args['event'] || 'another' === $args['event'] ) {
				$this->factory->hook_event->create( array( 'slug' => $args['event'] ) );
			} else {
				return false;
			}
		}

		if ( ! isset( $args['target'] ) ) {
			$args['target'] = array( 'test_entity' );
		}

		$reactions = $hooks->get_reaction_store( $args['reaction_store'] );

		if ( ! $reactions ) {
			$this->factory->hook_reaction_store->create(
				array( 'slug' => $args['reaction_store'] )
			);
		}

		$reactions = $hooks->get_reaction_store( $args['reaction_store'] );

		unset( $args['reaction_store'] );

		$reaction = $reactions->create_reaction( $args );

		if ( ! $reaction ) {
			return $reaction;
		}

		if ( $reaction instanceof WordPoints_Hook_Reaction_Validator ) {
			return new WP_Error(
				'wordpoints_hook_reaction_factor_create'
				, ''
				, $reaction
			);
		}

		return $reaction;
	}

	/**
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_ReactionI $object The reaction object.
	 * @param array                     $fields The new fields.
	 *
	 * @return WordPoints_Hook_ReactionI The reaction object.
	 */
	public function update_object( $object, $fields ) {

		$hooks = wordpoints_hooks();

		$fields = array_merge( $object->get_all_meta(), $fields );

		$reactions = $hooks->get_reaction_store( $object->get_store_slug() );
		$reaction  = $reactions->update_reaction( $object->get_id(), $fields );

		if ( ! $reaction ) {
			return $reaction;
		}

		if ( $reaction instanceof WordPoints_Hook_Reaction_Validator ) {
			return new WP_Error(
				'wordpoints_hook_reaction_factor_create'
				, ''
				, $reaction
			);
		}

		return $reaction;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_object_by_id( $object_id ) {
		return $object_id;
	}
}

// EOF
