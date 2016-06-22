<?php

/**
 * Hook event factory class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Factory for hook events, for use in the unit tests.
 *
 * @since 2.1.0
 *
 * @method string create( $args = array(), $generation_definitions = null )
 * @method WordPoints_Hook_EventI create_and_get( $args = array(), $generation_definitions = null )
 * @method string[] create_many( $count, $args = array(), $generation_definitions = null )
 */
class WordPoints_PHPUnit_Factory_For_Hook_Event extends WP_UnitTest_Factory_For_Thing {

	/**
	 * @since 2.1.0
	 */
	public function __construct( $factory = null ) {

		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'slug'  => 'test_event',
			'class' => 'WordPoints_PHPUnit_Mock_Hook_Event',
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function create_object( $args ) {

		if ( ! isset( WordPoints_PHPUnit_TestCase::$backup_app ) ) {
			WordPoints_PHPUnit_TestCase::mock_apps();
		}

		$hooks  = wordpoints_hooks();
		$events = $hooks->get_sub_app( 'events' );

		$slug = $args['slug'];
		$class = $args['class'];

		unset( $args['slug'], $args['class'] );

		if ( ! isset( $args['actions'] ) ) {
			$args['actions'] = array(
				'fire' => 'test_action',
				'reverse' => 'test_reverse_action',
			);
		}

		$actions = $hooks->get_sub_app( 'actions' );

		if ( ! $actions->is_registered( $args['actions']['fire'] ) ) {

			if ( 'test_action' === $args['actions']['fire'] ) {
				$this->factory->hook_action->create();
			} else {
				return false;
			}
		}

		if ( ! $actions->is_registered( $args['actions']['reverse'] ) ) {

			if ( 'test_reverse_action' === $args['actions']['reverse'] ) {
				$this->factory->hook_action->create(
					array( 'slug' => 'test_reverse_action' )
				);
			} else {
				return false;
			}
		}

		if ( ! isset( $args['args'] ) ) {
			$args['args'] = array(
				'test_entity' => 'WordPoints_PHPUnit_Mock_Hook_Arg',
			);

			$entities = wordpoints_entities();

			if ( ! $entities->is_registered( 'test_entity' ) ) {
				$entities->register( 'test_entity', 'WordPoints_PHPUnit_Mock_Entity' );
			}
		}

		$args_registry = $events->get_sub_app( 'args' );

		foreach ( $args['args'] as $arg_slug => $class ) {
			$args_registry->register( $slug, $arg_slug, $class );
		}

		$events->register( $slug, $class, $args );

		return $slug;
	}

	/**
	 * @since 2.1.0
	 */
	public function update_object( $object, $fields ) {
		return $object;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_object_by_id( $object_id ) {
		return wordpoints_hooks()->get_sub_app( 'events' )->get( $object_id );
	}
}

// EOF
