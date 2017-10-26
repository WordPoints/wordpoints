<?php

/**
 * Bulk check rank type interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for rank types that support checking if users can hold a rank in bulk.
 *
 * @since 2.4.0
 */
interface WordPoints_Rank_Type_Bulk_CheckI {

	/**
	 * Determines if a set of users meets the requirements for a rank of this type.
	 *
	 * @since 2.4.0
	 *
	 * @param int[]           $user_ids The IDs of the users to check.
	 * @param WordPoints_Rank $rank     The rank object.
	 * @param array           $args     Other arguments from the function that
	 *                                  triggered the check.
	 *
	 * @return int[] The IDs of all of the users in the set who mee the requirements.
	 */
	public function can_transition_user_ranks( array $user_ids, WordPoints_Rank $rank, array $args );
}

// EOF
