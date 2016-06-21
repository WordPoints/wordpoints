<?php

/**
 * Interface for class registries.
 *
 * @package WordPoints
 * @since 2.1.0
 */

/**
 * Defines how a class registry API should look.
 *
 * A class registry is an object that can be used to hold a list of classes indexed
 * by unique arbitrary slugs. It can be used to retrieve objects by the slug, without
 * knowing the class name. This allows for classes to be easily replaced, making the
 * whole architecture more decoupled.
 *
 * Internally, the objects may be stored and re-used or new ones may be created on-
 * the-fly as they are requested. This interface does not specify how they should be
 * handled, only how the objects are to be retrieved.
 *
 * @since 2.1.0
 */
interface WordPoints_Class_RegistryI {

	/**
	 * Get all registered objects.
	 *
	 * @since 2.1.0
	 *
	 * @return object[] All of the registered objects, indexed by slug.
	 */
	public function get_all();

	/**
	 * Get all of the slugs of the registered classes.
	 *
	 * @since 2.1.0
	 *
	 * @return string[] The slugs of the registered classes.
	 */
	public function get_all_slugs();

	/**
	 * Get an object by its slug.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The slug of the type of object to retrieve.
	 *
	 * @return object|false The object or false if it is not registered.
	 */
	public function get( $slug );

	/**
	 * Register a type of object.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug  A unique identifier for this type of object.
	 * @param string $class The class name.
	 * @param array  $args  Other arguments.
	 *
	 * @return bool Whether the class was registered successfully.
	 */
	public function register( $slug, $class, array $args = array() );

	/**
	 * Deregister a type of object
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The slug of the class to deregister.
	 */
	public function deregister( $slug );

	/**
	 * Check if a type of object is registered by its slug.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The slug of the class to check for.
	 *
	 * @return bool Whether the class is registered.
	 */
	public function is_registered( $slug );
}

// EOF
