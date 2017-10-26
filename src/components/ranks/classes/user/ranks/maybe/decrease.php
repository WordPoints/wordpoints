<?php

/**
 * Maybe decrease user ranks class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Decreases users of a rank to a lower rank if they should hold a lower rank.
 *
 * @since 2.4.0
 */
class WordPoints_User_Ranks_Maybe_Decrease
	extends WordPoints_User_Ranks_Maybe_Change {

	/**
	 * The rank type object for the rank being processed.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_Rank_Type
	 */
	protected $rank_type;

	/**
	 * @since 2.4.0
	 */
	public function __construct( WordPoints_Rank $rank ) {

		parent::__construct( $rank );

		$this->rank_type = WordPoints_Rank_Types::get_type( $this->rank->type );
	}

	/**
	 * @since 2.4.0
	 */
	protected function get_new_ranks_for_users( $user_ids ) {

		return $this->rank_type->maybe_decrease_user_ranks(
			$user_ids
			, $this->rank
		);
	}
}

// EOF
