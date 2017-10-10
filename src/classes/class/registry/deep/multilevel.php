<?php

/**
 * Multilevel deep class registry class.
 *
 * @package WordPoints
 * @since 2.2.0
 */

/**
 * A registry where classes are grouped together at any level in a deep hierarchy.
 *
 * It is multilevel, meaning that classes can be registered at multiple levels of
 * depth; there can be classes in a child group and also in several grandchild groups
 * descended from that same child group.
 *
 * Objects are created on-the-fly, and are not reused.
 *
 * @since 2.2.0
 *
 * @see WordPoints_Class_Registry
 */
class WordPoints_Class_Registry_Deep_Multilevel
	implements WordPoints_Class_Registry_DeepI {

	/**
	 * The class hierarchy, indexed by "parent" slugs.
	 *
	 * The classes registered for each parent slug are under the `'_classes'` key,
	 * which holds an array indexed by the class slugs.
	 *
	 * @since 2.2.0
	 *
	 * @var array[]
	 */
	protected $classes = array();

	/**
	 * Settings for this registry.
	 *
	 * @since 2.2.0
	 *
	 * @var array {
	 *      Other arguments.
	 *
	 *      @type bool $pass_slugs Whether to pass the class slugs to the
	 *                             constructors as the first argument. Default is
	 *                             true.
	 * }
	 */
	protected $settings = array(
		'pass_slugs' => true,
	);

	/**
	 * @since 2.2.0
	 *
	 * @param string[] $parent_slugs The parent slug(s).
	 * @param array    $args         Args to construct each class with.
	 */
	public function get_children(
		array $parent_slugs = array(),
		array $args = array()
	) {

		$classes = $this->get_deep( $this->classes, $parent_slugs );

		if ( ! $classes || ! isset( $classes['_classes'] ) ) {
			return array();
		}

		if ( $this->settings['pass_slugs'] ) {
			array_unshift( $args, $parent_slugs );
		}

		return WordPoints_Class_Registry::construct_with_args(
			$classes['_classes']
			, $args
			, $this->settings
		);
	}

	/**
	 * @since 2.2.0
	 */
	public function get_children_slugs( array $parent_slugs = array() ) {

		$slugs = array();

		$classes = $this->get_deep( $this->classes, $parent_slugs );

		if ( $classes && isset( $classes['_classes'] ) ) {
			$slugs = array_keys( $classes['_classes'] );
		}

		return $slugs;
	}

	/**
	 * @since 2.2.0
	 *
	 * @param string   $slug         The slug of the type of object to get.
	 * @param string[] $parent_slugs The slug(s) of the object's parent(s) in the
	 *                               hierarchy.
	 * @param array    $args         Args to construct the class with.
	 */
	public function get(
		$slug,
		array $parent_slugs = array(),
		array $args = array()
	) {

		$classes = $this->get_deep( $this->classes, $parent_slugs );

		if ( ! $classes || ! isset( $classes['_classes'][ $slug ] ) ) {
			return false;
		}

		$class = $classes['_classes'][ $slug ];

		if ( empty( $args ) ) {

			if ( $this->settings['pass_slugs'] ) {
				return new $class( $slug, $parent_slugs );
			} else {
				return new $class();
			}

		} else {

			if ( $this->settings['pass_slugs'] ) {
				array_unshift( $args, $slug, $parent_slugs );
			}

			return wordpoints_construct_class_with_args( $class, $args );
		}
	}

	/**
	 * @since 2.2.0
	 */
	public function register(
		$slug,
		array $parent_slugs,
		$class,
		array $args = array()
	) {

		$classes = &$this->classes;

		foreach ( $parent_slugs as $parent_slug ) {

			if ( ! isset( $classes[ $parent_slug ] ) ) {
				$classes[ $parent_slug ] = array();
			}

			$classes =& $classes[ $parent_slug ];
		}

		$classes['_classes'][ $slug ] = $class;

		return true;
	}

	/**
	 * @since 2.2.0
	 */
	public function deregister( $slug, array $parent_slugs = array() ) {

		$classes = &$this->get_deep( $this->classes, $parent_slugs );

		if ( ! $classes ) {
			return;
		}

		unset( $classes['_classes'][ $slug ] );
	}

	/**
	 * @since 2.2.0
	 */
	public function deregister_children( array $parent_slugs = array() ) {

		$classes = &$this->get_deep( $this->classes, $parent_slugs );

		if ( ! $classes ) {
			return;
		}

		unset( $classes['_classes'] );
	}

	/**
	 * @since 2.2.0
	 */
	public function is_registered( $slug, array $parent_slugs = array() ) {

		$parent_slugs[] = '_classes';

		if ( null !== $slug ) {
			$parent_slugs[] = $slug;
		}

		return (bool) $this->get_deep( $this->classes, $parent_slugs );
	}

	/**
	 * Get a reference to a deep element in a multilevel array.
	 *
	 * @since 2.2.0
	 *
	 * @param array    $array   The array.
	 * @param string[] $indexes The index(es) of the value to get from the hierarchy.
	 *
	 * @return mixed|false A reference to the value, or false if not found.
	 */
	protected function &get_deep( array &$array, array $indexes ) {

		// Only variable references can be returned by reference.
		$false           = false;
		$false_reference = &$false;

		foreach ( $indexes as $index ) {

			if ( ! isset( $array[ $index ] ) ) {
				return $false_reference;
			}

			$array = &$array[ $index ];
		}

		return $array;
	}
}

// EOF
