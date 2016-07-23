<?php

/**
 * Hook reaction interface.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Defines the API for objects representing a hook reaction.
 *
 * This allows for a reaction to be manipulated regardless of how it's settings are
 * stored.
 */
interface WordPoints_Hook_ReactionI {

	/**
	 * Get the reaction ID.
	 *
	 * @since 2.1.0
	 *
	 * @return int The ID of this reaction.
	 */
	public function get_id();

	/**
	 * Get a Globally Unique ID for this reaction.
	 *
	 * The GUID uniquely identifies this reaction, differentiating from any other
	 * reaction on this multi-network. It is composed of the reaction 'id', the
	 * 'reactor' slug, the reaction 'store', and the reaction 'context_id'.
	 *
	 * @since 2.1.0
	 *
	 * @return array The GUID for this reaction.
	 */
	public function get_guid();

	/**
	 * Get the context of this reaction.
	 *
	 * @since 2.1.0
	 *
	 * @return array|false The context of this reaction, or false if out of context.
	 */
	public function get_context_id();

	/**
	 * Get the slug of the event this reaction is for.
	 *
	 * @since 2.1.0
	 *
	 * @return string The event slug.
	 */
	public function get_event_slug();

	/**
	 * Update the event this reaction is for.
	 *
	 * @since 2.1.0
	 *
	 * @param string $event_slug The event slug.
	 *
	 * @return bool Whether the event was updated successfully.
	 */
	public function update_event_slug( $event_slug );

	/**
	 * Get the slug of the reactor this reaction is for.
	 *
	 * @since 2.1.0
	 *
	 * @return string The reactor slug.
	 */
	public function get_reactor_slug();

	/**
	 * Get the slug of the hook mode that this reaction relates to.
	 *
	 * @since 2.1.0
	 *
	 * @return string The mode slug.
	 */
	public function get_mode_slug();

	/**
	 * Get the slug of the store this reaction is from.
	 *
	 * Each reactor can store reactions in multiple different stores. For example,
	 * there are 'standard' and 'network' reactions. This method returns the slug of
	 * the store which this reaction is from.
	 *
	 * @since 2.1.0
	 *
	 * @return string The store slug.
	 */
	public function get_store_slug();

	/**
	 * Get a piece of metadata for this reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param string $key The meta key.
	 *
	 * @return mixed|false The meta value, or false if not found.
	 */
	public function get_meta( $key );

	/**
	 * Add a piece of metadata for this reaction.
	 *
	 * If this meta key already exists, the value will not be changed.
	 *
	 * @since 2.1.0
	 *
	 * @param string $key   The meta key.
	 * @param mixed  $value The value.
	 *
	 * @return bool Whether the metadata was added successfully.
	 */
	public function add_meta( $key, $value );

	/**
	 * Update a piece of metadata for this reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param string $key   The meta key.
	 * @param mixed  $value The new value.
	 *
	 * @return bool Whether the metadata was updated successfully.
	 */
	public function update_meta( $key, $value );

	/**
	 * Delete a piece of metadata for this reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param string $key The meta key.
	 *
	 * @return bool Whether the metadata was deleted successfully.
	 */
	public function delete_meta( $key );

	/**
	 * Get all of the metadata for this reaction.
	 *
	 * @since 2.1.0
	 *
	 * @return array|false All metadata for this reaction, or false on failure.
	 */
	public function get_all_meta();
}

// EOF
