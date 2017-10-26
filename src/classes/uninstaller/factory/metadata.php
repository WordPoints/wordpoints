<?php

/**
 * Metadata uninstaller factory.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Factory for metadata uninstall routines.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Factory_Metadata
	implements WordPoints_Uninstaller_Factory_SingleI,
		WordPoints_Uninstaller_Factory_SiteI,
		WordPoints_Uninstaller_Factory_NetworkI {

	/**
	 * The type of metadata to uninstall.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The meta keys to delete, indexed by context.
	 *
	 * @since 2.4.0
	 *
	 * @var string[][]
	 */
	protected $keys;

	/**
	 * Whether to prefix the keys for site context.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	private $prefix_for_site;

	/**
	 * @since 2.4.0
	 *
	 * @param string     $type            The type of metadata to uninstall, e.g.,
	 *                                    'user', 'post'.
	 * @param string[][] $keys            The metadata keys to delete, indexed by
	 *                                    context. Wildcards are supported.
	 * @param bool       $prefix_for_site Whether to prefix the keys for site
	 *                                    context. The default is true for the user
	 *                                    meta type, and false for all others.
	 */
	public function __construct( $type, $keys, $prefix_for_site = null ) {

		$this->type = $type;
		$this->keys = wordpoints_map_context_shortcuts( $keys );

		if ( ! isset( $prefix_for_site ) ) {
			$prefix_for_site = 'user' === $this->type;
		}

		$this->prefix_for_site = $prefix_for_site;
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
	 * Gets metadata uninstall routines for meta keys for a particular context.
	 *
	 * @since 2.4.0
	 *
	 * @param string $context The context.
	 *
	 * @return WordPoints_RoutineI[] The uninstall routines for metadata.
	 */
	protected function get_for_context( $context ) {

		$routines = array();

		if ( ! isset( $this->keys[ $context ] ) ) {
			return $routines;
		}

		$keys = $this->keys[ $context ];

		foreach ( $keys as $index => $key ) {

			if ( false === strpos( $key, '%' ) ) {
				continue;
			}

			$routines[] = new WordPoints_Uninstaller_Metadata_Wildcards(
				$this->type
				, $key
				, $this->prefix_for_site && 'site' === $context
			);

			unset( $keys[ $index ] );
		}

		if ( ! empty( $keys ) ) {
			$routines[] = new WordPoints_Uninstaller_Metadata(
				$this->type
				, $keys
				, $this->prefix_for_site && 'site' === $context
			);
		}

		return $routines;
	}
}

// EOF
