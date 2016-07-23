<?php

/**
 * Class registry class.
 *
 * @package WordPoints
 * @since 2.1.0
 */

/**
 * A class registry which creates new objects on-the-fly as they are requested.
 *
 * In other words, each time get() is called, a new object will be returned.
 *
 * Objects are passed the slug as the first parameter when they are constructed.
 *
 * @since 2.1.0
 */
class WordPoints_Class_Registry implements WordPoints_Class_RegistryI {

	/**
	 * The registered classes, indexed by slug.
	 *
	 * @since 2.1.0
	 *
	 * @var string[]
	 */
	protected $classes = array();

	/**
	 * @since 2.1.0
	 */
	public function get_all( array $args = array() ) {
		return self::construct_with_args( $this->classes, $args );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_all_slugs() {
		return array_keys( $this->classes );
	}

	/**
	 * @since 2.1.0
	 */
	public function get( $slug, array $args = array() ) {

		if ( ! isset( $this->classes[ $slug ] ) ) {
			return false;
		}

		if ( ! empty( $args ) ) {

			array_unshift( $args, $slug );

			return wordpoints_construct_class_with_args(
				$this->classes[ $slug ]
				, $args
			);

		} else {
			return new $this->classes[ $slug ]( $slug );
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function register( $slug, $class, array $args = array() ) {

		$this->classes[ $slug ] = $class;

		return true;
	}

	/**
	 * @since 2.1.0
	 */
	public function deregister( $slug ) {

		unset( $this->classes[ $slug ] );
	}

	/**
	 * @since 2.1.0
	 */
	public function is_registered( $slug ) {

		return isset( $this->classes[ $slug ] );
	}

	/**
	 * Construct an array of classes with the given arguments.
	 *
	 * @since 2.1.0
	 *
	 * @param string[] $classes The classes, indexed by slug (which will be the first
	 *                          arg passed to the constructor before the other args).
	 * @param array    $args    An array of args to pass to the class constructor.
	 *
	 * @return object[] An array of the constructed objects.
	 */
	public static function construct_with_args( array $classes, array $args ) {

		$objects = array();

		if ( empty( $args ) ) {

			foreach ( $classes as $slug => $class ) {
				$objects[ $slug ] = new $class( $slug );
			}

		} else {

			array_unshift( $args, null );

			foreach ( $classes as $slug => $class ) {
				$objects[ $slug ] = wordpoints_construct_class_with_args(
					$class
					, array( $slug ) + $args
				);
			}
		}

		return $objects;
	}
}

// EOF
