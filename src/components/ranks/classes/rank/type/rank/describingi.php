<?php

/**
 * Rank describing rank type interface.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Interface for rank types that can describe a rank.
 *
 * @since 2.4.0
 */
interface WordPoints_Rank_Type_Rank_DescribingI {

	/**
	 * Gets the description for a rank of this type.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Rank $rank A rank of this type.
	 *
	 * @return string The description of the passed rank.
	 */
	public function get_rank_description( WordPoints_Rank $rank );
}

// EOF
