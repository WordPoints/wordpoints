<?php

/**
 * Class for apps that are also class registries.
 *
 * @package WordPoints\Apps
 * @since 2.1.0
 */

/**
 * An app that is also a class registry.
 *
 * @since 2.1.0
 */
class WordPoints_App_Registry
	extends WordPoints_App
	implements WordPoints_Class_RegistryI {

	/**
	 * The class registry object.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Class_Registry
	 */
	protected $registry;

	/**
	 * @since 2.1.0
	 */
	public function __construct( $slug ) {

		$this->registry = new WordPoints_Class_Registry();

		parent::__construct( $slug );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_all( array $args = array() ) {
		return $this->registry->get_all( $args );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_all_slugs() {
		return $this->registry->get_all_slugs();
	}

	/**
	 * @since 2.1.0
	 */
	public function get( $slug, array $args = array() ) {
		return $this->registry->get( $slug, $args );
	}

	/**
	 * @since 2.1.0
	 */
	public function register( $slug, $class, array $args = array() ) {
		return $this->registry->register( $slug, $class, $args );
	}

	/**
	 * @since 2.1.0
	 */
	public function deregister( $slug ) {
		$this->registry->deregister( $slug );
	}

	/**
	 * @since 2.1.0
	 */
	public function is_registered( $slug ) {
		return $this->registry->is_registered( $slug );
	}
}

// EOF
