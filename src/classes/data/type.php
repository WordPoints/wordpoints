<?php

/**
 * Data type class.
 *
 * @package WordPoints\Data_Types
 * @since 2.1.0
 */

/**
 * Bootstrap for data type handlers.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Data_Type implements WordPoints_Data_TypeI {

	/**
	 * The slug of this data type.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * @since 2.1.0
	 *
	 * @param string $slug The slug of this data type.
	 */
	public function __construct( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * @since 2.1.0
	 */
	public function get_slug() {
		return $this->slug;
	}
}

// EOF
