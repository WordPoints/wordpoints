<?php

/**
 * Class to represent a rank group.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Represents a rank group.
 *
 * @since 1.7.0
 *
 * @property-read string $slug
 * @property-read string $description
 * @property-read string $name
 */
final class WordPoints_Rank_Group {

	/**
	 * The slug for this group.
	 *
	 * @since 1.7.0
	 *
	 * @type string $slug
	 */
	private $slug;

	/**
	 * The group's data.
	 *
	 * @since 1.7.0
	 *
	 * @type array $data
	 */
	private $data;

	/**
	 * The name of the option in the database for the list of ranks in this group.
	 *
	 * @since 1.7.0
	 *
	 * @type string $option_name
	 */
	private $option_name;

	/**
	 * The types of rank allowed in this group.
	 *
	 * @since 1.7.0
	 *
	 * @type string[] $types
	 */
	private $types = array( 'base' => 'base' );

	/**
	 * Construct the group with its slug and other data.
	 *
	 * @since 1.7.0
	 *
	 * @param string $slug The slug of this group.
	 * @param array  $data {
	 *        Other data for the group.
	 *
	 *        @type string $name        The name for this group.
	 *        @type string $description A description of this group.
	 * }
	 */
	public function __construct( $slug, $data ) {

		$this->slug = $slug;
		$this->data = $data;
		$this->option_name = "wordpoints_rank_group-{$this->slug}";
	}

	/**
	 * Magic getter for the group's data.
	 *
	 * @since 1.7.0
	 */
	public function __get( $key ) {

		if ( isset( $this->$key ) ) {
			return $this->$key;
		} elseif ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}
	}

	/**
	 * Get the slug of this group.
	 *
	 * @since 1.7.0
	 *
	 * @return string The group's slug.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the types of rank registered for this group.
	 *
	 * @since 1.7.0
	 *
	 * @return string[] The slugs of the rank types supported by this group.
	 */
	public function get_types() {

		return $this->types;
	}

	/**
	 * Check if this group is allowed to contain a given rank type.
	 *
	 * @since 1.7.0
	 *
	 * @param string $type The rank type to check for.
	 *
	 * @return bool Whether the rank type was registered for this group.
	 */
	public function has_type( $type ) {

		if ( ! WordPoints_Rank_Types::is_type_registered( $type ) ) {
			return false;
		}

		return isset( $this->types[ $type ] );
	}

	/**
	 * Add a rank type to the list allowed in this group.
	 *
	 * @since 1.7.0
	 *
	 * @param string $type The rank type to add.
	 *
	 * @return bool Whether the type was added successfully.
	 */
	public function add_type( $type ) {

		if ( ! WordPoints_Rank_Types::is_type_registered( $type ) ) {
			return false;
		}

		if ( isset( $this->types[ $type ] ) ) {
			return false;
		}

		$this->types[ $type ] = $type;

		return true;
	}

	/**
	 * Remove a rank type from the list allowed in this group.
	 *
	 * Note that the 'base' rank type cannot be removed.
	 *
	 * @since 1.7.0
	 *
	 * @param string $type The rank type to remove.
	 *
	 * @return bool Whether the type was removed successfully.
	 */
	public function remove_type( $type ) {

		if ( 'base' === $type ) {
			return false;
		}

		if ( ! WordPoints_Rank_Types::is_type_registered( $type ) ) {
			return false;
		}

		if ( ! isset( $this->types[ $type ] ) ) {
			return false;
		}

		unset( $this->types[ $type ] );

		return true;
	}

	/**
	 * Add a rank to the group.
	 *
	 * If the position is after the last rank, it will be added as the last rank. So
	 * if there are 4 ranks, and you try to add a rank in position 7, it will be the
	 * 5th rank in the group.
	 *
	 * @since 1.7.0
	 *
	 * @param int $rank_id  The ID of the rank to add to this group.
	 * @param int $position The position of this rank in the rank list.
	 *
	 * @return bool Whether the rank was added successfully.
	 */
	public function add_rank( $rank_id, $position ) {

		if (
			! wordpoints_posint( $rank_id )
			|| false === wordpoints_int( $position )
			|| $position < 0
		) {
			return false;
		}

		// Don't allow a rank to be added to this group more than once.
		if ( false !== $this->get_rank_position( $rank_id ) ) {
			return false;
		}

		$ranks = $this->_insert_rank( $this->get_ranks(), $rank_id, $position );

		if ( ! $this->save_ranks( $ranks ) ) {
			return false;
		}

		// If there is a rank before this one, check if any of the users who have it
		// can increase to this new one.
		$this->_maybe_increase_users_with_previous_rank( $rank_id );

		return true;
	}

	/**
	 * Move a rank from one position to another.
	 *
	 * Calling this method saves us from having to call remove and then add causing
	 * two database writes. This way we only write to the database once.
	 *
	 * @since 1.7.0
	 *
	 * @param int $rank_id  The ID of the rank to add to this group.
	 * @param int $position The position of this rank in the rank list.
	 *
	 * @return bool Whether the rank was moved successfully.
	 */
	public function move_rank( $rank_id, $position ) {

		$current_position = $this->get_rank_position( $rank_id );

		if ( $current_position === $position ) {
			return false;
		}

		$ranks = $this->get_ranks();

		unset( $ranks[ $current_position ] );

		ksort( $ranks );

		$ranks = $this->_insert_rank( array_values( $ranks ), $rank_id, $position );

		if ( ! $this->save_ranks( $ranks ) ) {
			return false;
		}

		// Users of the rank's former position should be moved to the previous ranks
		// position.
		$this->_move_users_from_rank_to_rank(
			$rank_id
			, $this->get_rank( $current_position - 1 )
		);

		// The users of the rank previous to this one's new position should maybe be
		// increased to this rank.
		$this->_maybe_increase_users_with_previous_rank( $rank_id );

		return true;
	}

	/**
	 * Remove a rank from the group.
	 *
	 * @since 1.7.0
	 *
	 * @param int $rank_id The ID of the rank to remove from this group.
	 *
	 * @return bool Whether the rank was removed successfully.
	 */
	public function remove_rank( $rank_id ) {

		global $wpdb;

		$position = $this->get_rank_position( $rank_id );

		if ( false === $position ) {
			return false;
		}

		$ranks = $this->get_ranks();

		unset( $ranks[ $position ] );

		if ( ! $this->save_ranks( $ranks ) ) {
			return false;
		}

		// Assign the previous rank to users who have this rank.
		if ( isset( $ranks[ $position - 1 ] ) ) {

			$this->_move_users_from_rank_to_rank( $rank_id, $ranks[ $position - 1 ] );

		} else {

			// If there is no previous rank, just delete the rows.
			$wpdb->delete(
				$wpdb->wordpoints_user_ranks
				, array( 'rank_id' => $rank_id )
				, '%d'
			);
		}

		return true;
	}

	/**
	 * Save the list of ranks.
	 *
	 * The save is aborted and false is returned if there are duplicate entries.
	 *
	 * @since 1.7.0
	 *
	 * @param int[] $rank_ids The IDs of the ranks in this group, in correct order.
	 *
	 * @return bool Whether the ranks were saved successfully.
	 */
	public function save_ranks( $rank_ids ) {

		if ( count( array_unique( $rank_ids ) ) !== count( $rank_ids ) ) {
			return false;
		}

		ksort( $rank_ids );

		$rank_ids = array_values( $rank_ids );

		return update_option( $this->option_name, $rank_ids );
	}

	/**
	 * Get a list of ranks in this group.
	 *
	 * @since 1.7.0
	 *
	 * @return int[] The IDs of the ranks in this group, indexed by order.
	 */
	public function get_ranks() {

		return wordpoints_get_array_option( $this->option_name );
	}

	/**
	 * Get the ID of the default rank.
	 *
	 * The default rank is the lowest rank in the group.
	 *
	 * @since 1.7.0
	 *
	 * @return int|false The ID of the rank, or false if not found.
	 */
	public function get_base_rank() {

		return $this->get_rank( 0 );
	}

	/**
	 * Get the ID of a rank from its position.
	 *
	 * @since 1.7.0
	 *
	 * @param int $position The position of the rank to get the ID of.
	 *
	 * @return int|false The ID of the rank, or false if not found.
	 */
	public function get_rank( $position ) {

		$ranks = $this->get_ranks();

		if ( ! isset( $ranks[ $position ] ) ) {
			return false;
		}

		return $ranks[ $position ];
	}

	/**
	 * Get a rank's position by its ID.
	 *
	 * @since 1.7.0
	 *
	 * @param int $rank_id The ID of the rank to get the position of.
	 *
	 * @return int|false The rank's position, or false.
	 */
	public function get_rank_position( $rank_id ) {

		return array_search(
			wordpoints_posint( $rank_id )
			, $this->get_ranks()
			, true
		);
	}

	//
	// Private Methods.
	//

	/**
	 * Insert a rank into a list of ranks at a given position.
	 *
	 * @since 1.7.0
	 *
	 * @param int[] $ranks    The list of rank IDs to insert this rank ID into.
	 * @param int   $rank_id  The rank to insert into the list.
	 * @param int   $position The position in the list to insert it.
	 *
	 * @return int[] The list of ranks with the rank insterted.
	 */
	private function _insert_rank( $ranks, $rank_id, $position ) {

		$count = count( $ranks );

		if ( $count === $position ) {

			$ranks[ $position ] = $rank_id;

		} elseif ( $count < $position ) {

			$ranks[] = $rank_id;

		} else {

			$lower_ranks  = array_slice( $ranks, 0, $position, true );
			$higher_ranks = array_slice( $ranks, $position, null, true );

			$ranks = array_merge(
				$lower_ranks
				, array( $position => $rank_id )
				, $higher_ranks
			);
		}

		return $ranks;
	}

	/**
	 * Assign all users who have a given rank a different rank instead.
	 *
	 * @since 1.7.0
	 *
	 * @param int $rank_from_id The ID of the rank the users have now.
	 * @param int $rank_to_id   The ID of the rank to give the users instead.
	 */
	private function _move_users_from_rank_to_rank( $rank_from_id, $rank_to_id ) {

		global $wpdb;

		if ( ! wordpoints_posint( $rank_from_id ) || ! wordpoints_posint( $rank_to_id ) ) {
			return;
		}

		$wpdb->update(
			$wpdb->wordpoints_user_ranks
			, array( 'rank_id' => $rank_to_id )
			, array( 'rank_id' => $rank_from_id )
			, '%d'
			, '%d'
		);
	}

	private function _maybe_increase_users_with_previous_rank( $rank_id ) {

		$rank = wordpoints_get_rank( $rank_id );
		$previous_rank = $rank->get_adjacent( -1 );

		if ( ! $previous_rank ) {
			return;
		}

		$users = wordpoints_get_users_with_rank( $previous_rank->ID );

		if ( empty( $users ) ) {
			return;
		}

		$rank_type = WordPoints_Rank_Types::get_type( $rank->type );

		foreach ( $users as $user_id ) {

			$new_rank = $rank_type->maybe_increase_user_rank(
				$user_id
				, $previous_rank
			);

			if ( $new_rank->ID === $previous_rank->ID ) {
				continue;
			}

			wordpoints_update_user_rank( $user_id, $new_rank->ID );
		}
	}
}

// EOF
