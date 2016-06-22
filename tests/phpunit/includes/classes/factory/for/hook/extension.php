<?php

/**
 * Hook extension factory class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.1.0
 */

/**
 * Factory for hook extensions, for use in the unit tests.
 *
 * @since 2.1.0
 *
 * @method string create( $args = array(), $generation_definitions = null )
 * @method WordPoints_Hook_ExtensionI create_and_get( $args = array(), $generation_definitions = null )
 * @method string[] create_many( $count, $args = array(), $generation_definitions = null )
 */
class WordPoints_PHPUnit_Factory_For_Hook_Extension extends WP_UnitTest_Factory_For_Thing {

	/**
	 * @since 2.1.0
	 */
	public function __construct( $factory = null ) {

		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'slug'  => 'test_extension',
			'class' => 'WordPoints_PHPUnit_Mock_Hook_Extension',
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function create_object( $args ) {

		if ( ! isset( WordPoints_PHPUnit_TestCase::$backup_app ) ) {
			WordPoints_PHPUnit_TestCase::mock_apps();
		}

		$extensions = wordpoints_hooks()->get_sub_app( 'extensions' );

		$slug = $args['slug'];
		$class = $args['class'];

		unset( $args['slug'], $args['class'] );

		$extensions->register( $slug, $class, $args );

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
		return wordpoints_hooks()->get_sub_app( 'extensions' )->get( $object_id );
	}
}

// EOF
