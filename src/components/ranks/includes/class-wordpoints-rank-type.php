<?php

/**
 * Abstract class for representing a rank type.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Parent rank type handler.
 *
 * The ranks provided by WordPoints may be of multiple different types. Each rank
 * type is represented by a child of this class, which may be called a "rank type
 * handler". The handler for a rank type handles adding, updating, and deleting ranks
 * of that type. It also does the same for their metadata.
 *
 * This abstract parent class provides the basic backbone of this API. It also
 * contains several abstract methods, which must be implemented by each child class
 * for each type of rank. These methods hook up the functions to perform the rank
 * change, display the form fields for the metadata for this rank type, and validate
 * the meta when the form is submitted.
 *
 * @since 1.7.0
 */
abstract class WordPoints_Rank_Type {

	//
	// Protected Vars.
	//

	/**
	 * The rank's slug.
	 *
	 * @since 1.7.0
	 *
	 * @type string $slug
	 */
	protected $slug;

	//
	// Abstract Methods.
	//

	/**
	 * Destroy the rank type hanlder when this rank type is deregistered.
	 *
	 * This method is called if the rank type is deregistered, so that it can revert
	 * anything done on construction. For example, it should unhook itself from any
	 * actions, etc.
	 *
	 * @since 1.7.0
	 */
	abstract public function destruct();

	/**
	 * Validate the metadata for a rank of this type.
	 *
	 * @since 1.7.0
	 *
	 * @param array $meta The metadata to validate.
	 *
	 * @return array|false The validated metadata or false if it should't be saved.
	 */
	abstract public function validate_rank_meta( array $meta );

	/**
	 * Display form fields for the metadata of a rank of this type.
	 *
	 * @since 1.7.0
	 *
	 * @param array $meta The metadata for a rank of this type.
	 */
	abstract public function display_rank_meta_form_fields( array $meta );

	/**
	 * Determine if a user meets the requirements for a rank of this type.
	 *
	 * This function is called to determine whether a user should be transitioned to
	 * the rank in question from their current rank.
	 *
	 * @since 1.7.0
	 *
	 * @param int             $user_id The ID of the user to check.
	 * @param WordPoints_Rank $rank    The object for the rank.
	 * @param array           $args    Other arguments from the function which
	 *                                 triggered the check.
	 *
	 * @return bool Whether the user meets the requirements for this rank.
	 */
	abstract protected function can_transition_user_rank( $user_id, $rank, array $args );

	//
	// Final Protected Methods.
	//

	/**
	 * Maybe transition a user's rank.
	 *
	 * @since 1.7.0
	 *
	 * @param int   $user_id The ID of the user whose rank to maybe transition.
	 * @param array $args    Other arguments.
	 */
	final protected function maybe_transition_user_rank( $user_id, $args ) {

		$groups = WordPoints_Rank_Groups::get();

		foreach ( $groups as $group_slug => $group ) {

			if ( ! WordPoints_Rank_Groups::is_type_registered_for_group( $this->slug, $group_slug ) ) {
				continue;
			}

			$rank_id = wordpoints_get_user_rank( $user_id, $group_slug );

			$rank = wordpoints_get_rank( $rank_id );

			if ( ! $rank ) {
				continue;
			}

			$next_rank = $rank->get_next();

			if ( ! $next_rank || $next_rank->type !== $this->slug ) {
				continue;
			}

			if ( ! $this->can_transition_user_rank( $user_id, $next_rank, $args ) ) {
				continue;
			}

			wordpoints_update_user_rank( $user_id, $rank_id, $next_rank->ID );
		}
	}

} // abstract class WordPoints_Rank_Type

// EOF
