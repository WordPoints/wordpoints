<?php

/**
 * Deep class registry interface.
 *
 * @package WordPoints
 * @since 2.2.0
 */

/**
 * Defines a registry for classes arranged in a hierarchy of arbitrary depth.
 *
 * Parent and child relationships are arbitrary, and not related to the actual
 * structure of the class hierarchy in the code.
 *
 * @since 2.2.0
 *
 * @see WordPoints_Class_RegistryI
 */
interface WordPoints_Class_Registry_DeepI {

	/**
	 * Get all of the direct children of a given parent within the hierarchy.
	 *
	 * @since 2.2.0
	 *
	 * @param string[] $parent_slugs The parent slug(s).
	 *
	 * @return object[] The child objects, indexed by slug.
	 */
	public function get_children( array $parent_slugs = array() );

	/**
	 * Get the slugs of all of the direct children of a certain parent.
	 *
	 * @since 2.2.0
	 *
	 * @param string[] $parent_slugs The parent slug(s).
	 *
	 * @return string[] The child slugs.
	 */
	public function get_children_slugs( array $parent_slugs = array() );

	/**
	 * Get an object by its slug.
	 *
	 * @since 2.2.0
	 *
	 * @param string   $slug         The slug of the type of object to get.
	 * @param string[] $parent_slugs The slug(s) of the object's parent(s) in the
	 *                               hierarchy.
	 *
	 * @return false|object The object or false on failure.
	 */
	public function get( $slug, array $parent_slugs = array() );

	/**
	 * Register a type of object.
	 *
	 * @since 2.2.0
	 *
	 * @param string   $slug         The slug for this type of object.
	 * @param string[] $parent_slugs The hierarchy for this object.
	 * @param string   $class        The class name.
	 * @param array    $args         Other arguments.
	 *
	 * @return bool Whether the class was registered successfully.
	 */
	public function register( $slug, array $parent_slugs, $class, array $args = array() );

	/**
	 * Deregister a type of object.
	 *
	 * @since 2.2.0
	 *
	 * @param string   $slug         The slug of the class to deregister.
	 * @param string[] $parent_slugs The slug(s) of the class's parent(s) in the
	 *                               hierarchy.
	 */
	public function deregister( $slug, array $parent_slugs = array() );

	/**
	 * Deregister all children of a particular parent in the hierarchy.
	 *
	 * @since 2.2.0
	 *
	 * @param string[] $parent_slugs The hierarchy of the parent.
	 */
	public function deregister_children( array $parent_slugs = array() );

	/**
	 * Check if a type of object is registered by its slug.
	 *
	 * If the $slug is omitted, it will check if any children of the parent are
	 * registered.
	 *
	 * @since 2.2.0
	 *
	 * @param string   $slug         The slug of the class to check for.
	 * @param string[] $parent_slugs The hierarchy for the class to check for.
	 *
	 * @return bool Whether the class is registered.
	 */
	public function is_registered( $slug, array $parent_slugs = array() );
}

// EOF
