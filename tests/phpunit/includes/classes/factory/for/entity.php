<?php

/**
 * Entity factory class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Factory for entities, for use in the unit tests.
 *
 * @since 2.1.0
 *
 * @method string create( $args = array(), $generation_definitions = null )
 * @method WordPoints_Entity create_and_get( $args = array(), $generation_definitions = null )
 * @method string[] create_many( $count, $args = array(), $generation_definitions = null )
 */
class WordPoints_PHPUnit_Factory_For_Entity extends WP_UnitTest_Factory_For_Thing {

	/**
	 * @since 2.1.0
	 */
	public function __construct( $factory = null ) {

		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'slug'  => 'test_entity',
			'class' => 'WordPoints_PHPUnit_Mock_Entity_Contexted',
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function create_object( $args ) {

		if ( ! isset( WordPoints_PHPUnit_TestCase::$backup_app ) ) {
			WordPoints_PHPUnit_TestCase::mock_apps();
		}

		$entities = wordpoints_entities();

		if ( $args === $this->default_generation_definitions ) {
			$entities->get_sub_app( 'contexts' )->register(
				'test_context'
				, 'WordPoints_PHPUnit_Mock_Entity_Context'
			);
		}

		$slug = $args['slug'];
		$class = $args['class'];

		unset( $args['slug'], $args['class'] );

		$entities->register( $slug, $class, $args );

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
		return wordpoints_entities()->get( $object_id );
	}
}

// EOF
