<?php

/**
 * Entityish class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Bootstrap for representing an entity-like object.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Entityish implements WordPoints_EntityishI {

	/**
	 * The slug of this entity/entity-child.
	 *
	 * You must either set this or override the get_slug() method.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The value of this entity/entity-child.
	 *
	 * @since 2.1.0
	 *
	 * @var mixed
	 */
	protected $the_value;

	/**
	 * Construct the entity/entity-child with a slug.
	 *
	 * @param string $slug The slug of the entity/entity-child.
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

	/**
	 * @since 2.1.0
	 */
	public function get_the_value() {
		return $this->the_value;
	}

	/**
	 * @since 2.1.0
	 */
	public function set_the_value( $value ) {

		$this->the_value = $value;

		return true;
	}
}

// EOF
