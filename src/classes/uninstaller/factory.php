<?php

/**
 * Uninstaller factory class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Factory for uninstall routines.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Factory
	implements WordPoints_Uninstaller_Factory_SingleI,
		WordPoints_Uninstaller_Factory_NetworkI,
		WordPoints_Uninstaller_Factory_SiteI {

	/**
	 * The uninstall routines, grouped by context.
	 *
	 * @since 2.4.0
	 *
	 * @var array[]
	 */
	protected $uninstallers;

	/**
	 * @since 2.4.0
	 *
	 * @param array[] $uninstallers The uninstallers, grouped by context. Context
	 *                              shortcuts are supported. Each uninstaller should
	 *                              be represented either by a string which is the
	 *                              name of the uninstall routine class, or an array
	 *                              with the 'class' key whose value is the name of
	 *                              the uninstall routine, and an array of 'args`
	 *                              that the class should be constructed with.
	 */
	public function __construct( $uninstallers ) {

		$this->uninstallers = wordpoints_map_context_shortcuts( $uninstallers );
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
	 * Gets the uninstall routines for a given context.
	 *
	 * @since 2.4.0
	 *
	 * @param string $context The context to get the routines for.
	 *
	 * @return WordPoints_RoutineI[] The uninstall routines for the given context.
	 */
	protected function get_for_context( $context ) {

		$routines = array();

		if ( empty( $this->uninstallers[ $context ] ) ) {
			return $routines;
		}

		foreach ( $this->uninstallers[ $context ] as $data ) {

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
