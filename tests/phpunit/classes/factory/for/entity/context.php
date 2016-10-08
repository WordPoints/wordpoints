<?php

/**
 * Entity context factory class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.2.0
 */

/**
 * Factory for entity contexts, for use in the unit tests.
 *
 * @since 2.2.0
 *
 * @method string create( $args = array(), $generation_definitions = null )
 * @method WordPoints_Entity_Context create_and_get( $args = array(), $generation_definitions = null )
 * @method string[] create_many( $count, $args = array(), $generation_definitions = null )
 */
class WordPoints_PHPUnit_Factory_For_Entity_Context extends WP_UnitTest_Factory_For_Thing {

	/**
	 * @since 2.2.0
	 */
	public function __construct( $factory = null ) {

		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'slug'  => new WP_UnitTest_Generator_Sequence( 'test_context_%s' ),
			'class' => 'WordPoints_PHPUnit_Mock_Entity_Context',
		);
	}

	/**
	 * @since 2.2.0
	 */
	public function create_object( $args ) {

		if ( ! isset( WordPoints_PHPUnit_TestCase::$backup_app ) ) {
			WordPoints_PHPUnit_TestCase::mock_apps();
		}

		wordpoints_entities()->get_sub_app( 'contexts' )->register(
			$args['slug']
			, $args['class']
		);

		return $args['slug'];
	}

	/**
	 * @since 2.2.0
	 */
	public function update_object( $object, $fields ) {
		return $object;
	}

	/**
	 * @since 2.2.0
	 */
	public function get_object_by_id( $object_id ) {
		return wordpoints_entities()->get_sub_app( 'contexts' )->get( $object_id );
	}
}

// EOF
