<?php

/**
 * Extension installable class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Base installable bootstrap for an extension.
 *
 * @since 2.4.0
 */
abstract class WordPoints_Installable_Extension extends WordPoints_Installable {

	/**
	 * @since 2.4.0
	 */
	protected $type = 'module';

	/**
	 * Constructs the installable.
	 *
	 * @since 2.4.0
	 *
	 * @param string $slug The slug of the extension.
	 */
	public function __construct( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * @since 2.4.0
	 */
	public function get_version() {
		return WordPoints_Modules::get_data( $this->get_slug(), 'version' );
	}
}

// EOF
