<?php

/**
 * Interface for an entity hierarchy.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Defines the API for an entity hierarchy.
 *
 * Entities can have children, and these may in turn have their own children, and so
 * on. Sometimes it is helpful to encapsulate this into a hierarchy.
 *
 * @since 2.1.0
 */
interface WordPoints_Entity_HierarchyI {

	/**
	 * Get the entities at the top of this hierarchy.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Entity[]
	 */
	public function get_entities();

	/**
	 * Add an entity to the top of the hierarchy.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Entity $entity An entity.
	 */
	public function add_entity( WordPoints_Entity $entity );

	/**
	 * Remove an entity from the top of the hierarchy.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug The slug of the entity to remove.
	 */
	public function remove_entity( $slug );

	/**
	 * Descend to a child of the current entity/entity child.
	 *
	 * @since 2.1.0
	 *
	 * @param string $child_slug The child slug.
	 *
	 * @return bool Whether the descent was successful.
	 */
	public function descend( $child_slug );

	/**
	 * Ascend to the parent of the current entity/entity child.
	 *
	 * @since 2.1.0
	 *
	 * @return bool Whether the ascent was successful.
	 */
	public function ascend();

	/**
	 * Get the current entity/entity child.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_EntityishI|null The current entity/child, or null if unset.
	 */
	public function get_current();

	/**
	 * Get an entity/entity child from a hierarchy of slugs.
	 *
	 * @since 2.1.0
	 *
	 * @param string[] $hierarchy A list of slugs in hierarchical order.
	 *
	 * @return WordPoints_EntityishI|null The entity/child, or null if not found.
	 */
	public function get_from_hierarchy( array $hierarchy );
}

// EOF
