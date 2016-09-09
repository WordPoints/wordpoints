<?php

/**
 * Hook condition factory class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Factory for hook conditions, for use in the unit tests.
 *
 * @since 2.1.0
 *
 * @method WordPoints_Hook_ConditionI create( $args = array(), $generation_definitions = null )
 * @method WordPoints_Hook_ConditionI create_and_get( $args = array(), $generation_definitions = null )
 * @method WordPoints_Hook_ConditionI[] create_many( $count, $args = array(), $generation_definitions = null )
 */
class WordPoints_PHPUnit_Factory_For_Hook_Condition extends WP_UnitTest_Factory_For_Thing {

	/**
	 * @since 2.1.0
	 */
	public function __construct( $factory = null ) {

		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'slug'      => new WP_UnitTest_Generator_Sequence( 'test_condition_%s' ),
			'class'     => 'WordPoints_PHPUnit_Mock_Hook_Condition',
			'data_type' => 'test_data_type',
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function create_object( $args ) {

		if ( ! isset( WordPoints_PHPUnit_TestCase::$backup_app ) ) {
			WordPoints_PHPUnit_TestCase::mock_apps();
		}

		if ( 'unmet' === $args['slug'] ) {
			$args['class'] = 'WordPoints_PHPUnit_Mock_Hook_Condition_Unmet';
		}

		$conditions = wordpoints_hooks()->get_sub_app( 'conditions' );
		$conditions->register( $args['data_type'], $args['slug'], $args['class'] );
		return $conditions->get( $args['data_type'], $args['slug'] );
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
		return $object_id;
	}
}

// EOF
