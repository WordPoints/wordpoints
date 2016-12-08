<?php

/**
 * Entity context base class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Bootstrap for representing an entity context.
 *
 * The entity API makes it possible to encapsulate different sorts of things (like
 * posts and users) so that their data can be accessed through a common set of
 * interfaces. Each entity has an ID—a single piece of data (like an integer or
 * string) that can be used to identify that entity. The ID is unique to that entity,
 * but it is not *globally unique*. That is, it is unique to that entity only within
 * a particular scope or "context". For example, on multisite a post's ID is unique
 * only in the context of the site on which that post was published. That same ID
 * would refer to a different post on each other site on the network. The job of the
 * context API is to encapsulate the different sorts of contexts in which entities
 * can exist. This allows us to, for example, create globally unique IDs (GUIDs) for
 * entities, so that they can be identified specifically even outside of their usual
 * context.
 *
 * The context API can also allows us to switch from one context to another, so that
 * when we see that the GUID for a post indicates that it came from a different site
 * on the network, for example, we can switch to that site before performing an
 * operation involving the database.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Entity_Context {

	/**
	 * The slug of this context.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The slug of the parent of this context, if this is a sub-context.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $parent_slug;

	/**
	 * @since 2.1.0
	 *
	 * @param string $slug The slug of this context.
	 */
	public function __construct( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * Get the slug of this context.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the slug of the parent of this context, if it is a sub-context.
	 *
	 * Most contexts are really children of other contexts. For example, the 'site'
	 * context is a child of the 'network' context, since sites can only exist within
	 * a network.
	 *
	 * @since 2.1.0
	 *
	 * @return string|null The slug of the parent context, or null if none.
	 */
	public function get_parent_slug() {
		return $this->parent_slug;
	}

	/**
	 * Switch to a particular context.
	 *
	 * Child classes should strongly consider implementing this method, even if that
	 * means that they have to build their own context switching logic from scratch
	 * to do so.
	 *
	 * @since 2.2.0
	 *
	 * @param int|string $id The ID of the context to switch to.
	 *
	 * @return bool Whether the context was switched to successfully.
	 */
	public function switch_to( $id ) {
		return false;
	}

	/**
	 * Switch back to the previous context.
	 *
	 * Child classes should strongly consider implementing this method, even if that
	 * means that they have to build their own context switching logic from scratch
	 * to do so.
	 *
	 * @since 2.2.0
	 *
	 * @return bool Whether the previous context was switched back to successfully.
	 */
	public function switch_back() {
		return false;
	}

	/**
	 * Get the current ID of this context.
	 *
	 * @since 2.1.0
	 *
	 * @return int|string|false The ID or slug of the context, or false if not
	 *                          currently in this context.
	 */
	abstract public function get_current_id();
}

// EOF
