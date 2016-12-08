<?php

/**
 * Post type factory class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Factory for post types, for use in the unit tests.
 *
 * @since 2.1.0
 *
 * @method string create( $args = array(), $generation_definitions = null )
 * @method object create_and_get( $args = array(), $generation_definitions = null )
 * @method string[] create_many( $count, $args = array(), $generation_definitions = null )
 */
class WordPoints_PHPUnit_Factory_For_Post_Type extends WP_UnitTest_Factory_For_Thing {

	/**
	 * @since 2.1.0
	 */
	public function __construct( $factory = null ) {

		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'name' => new WP_UnitTest_Generator_Sequence(
				'post_type_%s'
			),
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function create_object( $args ) {

		$object = register_post_type( $args['name'], $args );

		if ( ! isset( $object->name ) ) {
			return $object;
		}

		return $object->name;
	}

	/**
	 * @since 2.1.0
	 */
	public function update_object( $object, $fields ) {
		return false;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_object_by_id( $object_id ) {
		return get_post_type_object( $object_id );
	}
}

// EOF
