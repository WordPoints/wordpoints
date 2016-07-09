<?php

/**
 * Hook reaction storage interface.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Interface for hook reaction storage methods.
 *
 * This allows hook reactions to be create/updated/deleted through a common interface
 * regardless of where the reaction data is stored.
 *
 * @since 2.1.0
 */
interface WordPoints_Hook_Reaction_StoreI {

	/**
	 * Get the slug of this reaction store.
	 *
	 * This isn't the slug of the storage method itself, but the identifier for the
	 * group of reactions a particular object happens to be storing.
	 *
	 * @since 2.1.0
	 *
	 * @return string The slug of this store.
	 */
	public function get_slug();

	/**
	 * Get the slug of the hook mode that this store relates to.
	 *
	 * @since 2.1.0
	 *
	 * @return string The slug of the hook mode this store relates to.
	 */
	public function get_mode_slug();

	/**
	 * Get the ID of the current context in which reactions are being stored.
	 *
	 * @since 2.1.0
	 *
	 * @see wordpoints_entities_get_current_context_id()
	 *
	 * @return array|false The ID of the context in which this method is currently
	 *                     storing reactions, or false if out of context.
	 */
	public function get_context_id();

	/**
	 * Check whether a reaction exists.
	 *
	 * @since 2.1.0
	 *
	 * @param int $id The reaction ID.
	 *
	 * @return bool Whether the reaction exists.
	 */
	public function reaction_exists( $id );

	/**
	 * Get an reaction object.
	 *
	 * @since 2.1.0
	 *
	 * @param int $id The ID of an reaction.
	 *
	 * @return WordPoints_Hook_ReactionI|false The reaction, or false if nonexistent.
	 */
	public function get_reaction( $id );

	/**
	 * Create an reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param array $settings The settings for the reaction.
	 *
	 * @return WordPoints_Hook_ReactionI|false|WordPoints_Hook_Reaction_Validator
	 *         The reaction object if created successfully. False or a validator
	 *         instance if not.
	 */
	public function create_reaction( array $settings );

	/**
	 * Update an reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param int   $id       The ID of the reaction to update.
	 * @param array $settings The settings for the reaction.
	 *
	 * @return WordPoints_Hook_ReactionI|false|WordPoints_Hook_Reaction_Validator
	 *         The reaction object if updated successfully. False or a validator
	 *         instance if not.
	 */
	public function update_reaction( $id, array $settings );

	/**
	 * Delete an reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param int $id The ID of the reaction.
	 *
	 * @return bool Whether the reaction was deleted successfully.
	 */
	public function delete_reaction( $id );

	/**
	 * Get all hook reactions for the reactor.
	 *
	 * Only standard or network-wide reactions should be returned, depending on
	 * whether network mode is on or off.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Hook_ReactionI[]
	 */
	public function get_reactions();

	/**
	 * Get all hook reactions to a given event for the reactor.
	 *
	 * Both standard and network-wide reactions should be returned.
	 *
	 * @since 2.1.0
	 *
	 * @return WordPoints_Hook_ReactionI[]
	 */
	public function get_reactions_to_event( $event_slug );
}

// EOF
