<?php

/**
 * Maybe change user ranks class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Bootstrap for changing users of a rank to different ranks in bulk.
 *
 * @since 2.4.0
 */
abstract class WordPoints_User_Ranks_Maybe_Change {

	/**
	 * The rank whose users are being processed.
	 *
	 * @since 2.4.0
	 *
	 * @var WordPoints_Rank
	 */
	protected $rank;

	/**
	 * @since 2.4.0
	 *
	 * @param WordPoints_Rank $rank The rank whose users to maybe change ranks for.
	 */
	public function __construct( WordPoints_Rank $rank ) {

		$this->rank = $rank;
	}

	/**
	 * Runs the bulk rank change.
	 *
	 * @since 2.4.0
	 */
	public function run() {

		wordpoints_prevent_interruptions();

		$batch_size = 1000;
		$offset     = 0;

		$query = new WordPoints_User_Ranks_Query(
			array(
				'fields'   => 'user_id',
				'rank_id'  => $this->rank->ID,
				'offset'   => $offset,
				'limit'    => $batch_size,
				'order_by' => 'id',
			)
		);

		$user_ids = $query->get( 'col' );

		while ( $user_ids ) {

			$new_ranks = $this->get_new_ranks_for_users( $user_ids );

			$count = count( $user_ids );

			unset( $user_ids );

			$new_rank_users = array();

			foreach ( $new_ranks as $user_id => $new_rank ) {
				$new_rank_users[ $new_rank ][] = $user_id;
			}

			unset( $new_ranks );

			foreach ( $new_rank_users as $new_rank => $user_ids ) {
				wordpoints_update_users_to_rank(
					$user_ids
					, $new_rank
					, $this->rank->ID
				);
			}

			unset( $new_rank_users );

			// No need to keep going if the last query returned less than the limit.
			if ( $count < $batch_size ) {
				break;
			}

			$offset += $batch_size;

			$query->set_args( array( 'offset' => $offset ) );
			$user_ids = $query->get( 'col' );
		}
	}

	/**
	 * Get the new ranks that a set of users of this rank should have.
	 *
	 * Only users that should be moved to a different rank need to be included in the
	 * result.
	 *
	 * @since 2.4.0
	 *
	 * @param int[] $user_ids The ID of users to check.
	 *
	 * @return int[] The new ranks for the users, indexed by user ID.
	 */
	abstract protected function get_new_ranks_for_users( $user_ids );
}

// EOF
