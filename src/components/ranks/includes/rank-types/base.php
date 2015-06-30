<?php

/**
 * Base rank type class.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Rank type for bottom rank in rank groups.
 *
 * @since 1.7.0
 */
class WordPoints_Base_Rank_Type extends WordPoints_Rank_Type {

	//
	// Public Methods.
	//

	/**
	 * @since 1.7.0
	 */
	public function destruct() {}

	/**
	 * Validate the metadata for a rank of this type.
	 *
	 * @since 1.7.0
	 *
	 * @param array $meta The metadata to validate.
	 *
	 * @return array|false The validated metadata or false if it should't be saved.
	 */
	public function validate_rank_meta( array $meta ) {
		return $meta;
	}

	//
	// Protected Methods.
	//

	/**
	 * Check if a user can transition to a rank of this type.
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
	protected function can_transition_user_rank( $user_id, $rank, array $args ) {
		return true;
	}
}

// EOF
