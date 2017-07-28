<?php

/**
 * Options uninstaller factory.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Factory for uninstall routines for options.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Factory_Options
	implements WordPoints_Uninstaller_Factory_SingleI,
		WordPoints_Uninstaller_Factory_SiteI,
		WordPoints_Uninstaller_Factory_NetworkI {

	/**
	 * The options to uninstall, indexed by context.
	 *
	 * @since 2.4.0
	 *
	 * @var string[][]
	 */
	protected $options;

	/**
	 * @since 2.4.0
	 *
	 * @param string[][] $options The options to uninstall, indexed by context.
	 *                            Wildcards are supported.
	 */
	public function __construct( $options ) {

		$this->options = wordpoints_map_context_shortcuts( $options );
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
	 * Gets option uninstall routines for options for a particular context.
	 *
	 * @since 2.4.0
	 *
	 * @param string $context The context to get the uninstall routines for.
	 *
	 * @return WordPoints_RoutineI[] The uninstall routines for this context.
	 */
	protected function get_for_context( $context ) {

		$routines = array();

		if ( ! isset( $this->options[ $context ] ) ) {
			return $routines;
		}

		$options = $this->options[ $context ];

		foreach ( $options as $index => $option ) {

			if ( false === strpos( $option, '%' ) ) {
				continue;
			}

			if ( 'network' === $context ) {
				$routines[] = new WordPoints_Uninstaller_Options_Wildcards_Network( $option );
			} else {
				$routines[] = new WordPoints_Uninstaller_Options_Wildcards( $option );
			}

			unset( $options[ $index ] );
		}

		if ( ! empty( $options ) ) {
			$routines[] = new WordPoints_Uninstaller_Callback(
				'network' === $context ? 'delete_site_option' : 'delete_option'
				, $options
			);
		}

		return $routines;
	}
}

// EOF
