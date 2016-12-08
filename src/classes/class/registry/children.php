<?php

/**
 * Class for a class registry of classes grouped as children of different parents.
 *
 * @package WordPoints
 * @since 2.1.0
 */

/**
 * A class registry where the classes are grouped together under arbitrary "parents".
 *
 * Objects are created on-the-fly, and are not reused.
 *
 * @since 2.1.0
 *
 * @see WordPoints_Class_Registry
 */
class WordPoints_Class_Registry_Children
	implements WordPoints_Class_Registry_ChildrenI {

	/**
	 * The class groups, indexed by "parent" slug.
	 *
	 * Each group is indexed by the class slugs.
	 *
	 * @since 2.1.0
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
	 * @since 2.1.0
	 */
	public function get_all( array $args = array() ) {

		$items = array();

		if ( $this->settings['pass_slugs'] ) {
			array_unshift( $args, null );
		}

		foreach ( $this->classes as $parent_slug => $classes ) {
			$items[ $parent_slug ] = WordPoints_Class_Registry::construct_with_args(
				$classes
				, $this->settings['pass_slugs'] ? array( $parent_slug ) + $args : $args
				, $this->settings
			);
		}

		return $items;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_all_slugs() {
		return array_map( 'array_keys', $this->classes );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_children( $parent_slug, array $args = array() ) {

		$items = array();

		if ( isset( $this->classes[ $parent_slug ] ) ) {

			if ( $this->settings['pass_slugs'] ) {
				array_unshift( $args, $parent_slug );
			}

			$items = WordPoints_Class_Registry::construct_with_args(
				$this->classes[ $parent_slug ]
				, $args
				, $this->settings
			);
		}

		return $items;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_children_slugs( $parent_slug ) {

		$slugs = array();

		if ( isset( $this->classes[ $parent_slug ] ) ) {
			$slugs = array_keys( $this->classes[ $parent_slug ] );
		}

		return $slugs;
	}

	/**
	 * @since 2.1.0
	 */
	public function get( $parent_slug, $slug, array $args = array() ) {

		if ( ! isset( $this->classes[ $parent_slug ][ $slug ] ) ) {
			return false;
		}

		$class = $this->classes[ $parent_slug ][ $slug ];

		if ( empty( $args ) ) {

			if ( $this->settings['pass_slugs'] ) {
				return new $class( $slug, $parent_slug );
			} else {
				return new $class();
			}

		} else {

			if ( $this->settings['pass_slugs'] ) {
				array_unshift( $args, $slug, $parent_slug );
			}

			return wordpoints_construct_class_with_args( $class, $args );
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function register( $parent_slug, $slug, $class, array $args = array() ) {

		$this->classes[ $parent_slug ][ $slug ] = $class;

		return true;
	}

	/**
	 * @since 2.1.0
	 */
	public function deregister( $parent_slug, $slug ) {
		unset( $this->classes[ $parent_slug ][ $slug ] );
	}

	/**
	 * @since 2.1.0
	 */
	public function deregister_children( $parent_slug ) {
		unset( $this->classes[ $parent_slug ] );
	}

	/**
	 * @since 2.1.0
	 */
	public function is_registered( $parent_slug, $slug = null ) {

		if ( isset( $slug ) ) {
			return isset( $this->classes[ $parent_slug ][ $slug ] );
		} else {
			return isset( $this->classes[ $parent_slug ] );
		}
	}
}

// EOF
