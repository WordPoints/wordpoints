<?php

/**
 * Updater factory class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Factory for update routines.
 *
 * @since 2.4.0
 */
class WordPoints_Updater_Factory implements WordPoints_Updater_FactoryI {

	/**
	 * The version these update routines are for.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * The update routines, grouped by context.
	 *
	 * @since 2.4.0
	 *
	 * @var array[]
	 */
	protected $updates;

	/**
	 * @since 2.4.0
	 *
	 * @param string  $version The version the updates are for.
	 * @param array[] $updates The updates, grouped by context. Context shortcuts are
	 *                         supported. Each update should be represented either by
	 *                         a string which is the name of the update routine, or
	 *                         an array with the 'class' key whose value is the name
	 *                         of the update routine, and an array of 'args` that the
	 *                         class should be constructed with.
	 */
	public function __construct( $version, $updates ) {

		$this->version = $version;
		$this->updates = wordpoints_map_context_shortcuts( $updates );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_for_single() {
		return $this->get_for_context( 'single' );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_for_site() {
		return $this->get_for_context( 'site' );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_for_network() {
		return $this->get_for_context( 'network' );
	}

	/**
	 * Gets the update routines for a given context.
	 *
	 * @since 2.4.0
	 *
	 * @param string $context The context to get the routines for.
	 *
	 * @return WordPoints_RoutineI[] The update routines for the given context.
	 */
	protected function get_for_context( $context ) {

		$routines = array();

		if ( empty( $this->updates[ $context ] ) ) {
			return $routines;
		}

		foreach ( $this->updates[ $context ] as $data ) {

			if ( $data instanceof WordPoints_RoutineI ) {
				$routines[] = $data;
				continue;
			}

			if ( is_string( $data ) ) {
				$class = $data;
				$args  = array();
			} else {
				$class = $data['class'];
				$args  = $data['args'];
			}

			$routines[] = wordpoints_construct_class_with_args( $class, $args );
		}

		return $routines;
	}
}

// EOF
