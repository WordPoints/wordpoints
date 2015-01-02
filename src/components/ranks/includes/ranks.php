<?php

/**
 * Rank API functions.
 *
 * Includes the ranks meta API.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

//
// Ranks.
//

/**
 * Create a new rank.
 *
 * @since 1.7.0
 *
 * @param string $name     The name of the rank.
 * @param string $type     The rank type slug.
 * @param string $group    The slug of the group to which this rank should be added.
 * @param int    $position The position this rank should have in the group.
 * @param array  $meta     Optional metadata for this rank.
 *
 * @return int|false|WP_Error The ID of the inserted rank, or false or WP_Error on failure.
 */
function wordpoints_add_rank( $name, $type, $group, $position, array $meta = array() ) {

	global $wpdb;

	if ( ! WordPoints_Rank_Groups::is_type_registered_for_group( $type, $group ) ) {
		return false;
	}

	$rank_type = WordPoints_Rank_Types::get_type( $type );

	if ( ! $rank_type ) {
		return false;
	}

	$meta = $rank_type->validate_rank_meta( $meta );
	if ( false === $meta || is_wp_error( $meta ) ) {
		return $meta;
	}

	$inserted = $wpdb->insert(
		$wpdb->wordpoints_ranks
		, array(
			'name'       => $name,
			'type'       => $type,
			'rank_group' => $group,
			'blog_id'    => $wpdb->blogid,
			'site_id'    => $wpdb->siteid,
		)
	);

	if ( ! $inserted ) {
		return false;
	}

	$rank_id = (int) $wpdb->insert_id;

	foreach ( $meta as $meta_key => $meta_value ) {
		wordpoints_add_rank_meta( $rank_id, $meta_key, $meta_value );
	}

	WordPoints_Rank_Groups::get_group( $group )->add_rank( $rank_id, $position );

	/**
	 * Perform actions when a rank is added.
	 *
	 * @since 1.7.0
	 *
	 * @param int $rank_id The ID of the rank that was added.
	 */
	do_action( 'wordpoints_add_rank', $rank_id );

	return $rank_id;
}

/**
 * Delete a rank.
 *
 * @since 1.7.0
 *
 * @param int $id The ID of the rank to delete.
 *
 * @return bool True if the rank was deleted, false otherwise.
 */
function wordpoints_delete_rank( $id ) {

	global $wpdb;

	$rank = wordpoints_get_rank( $id );

	if ( ! $rank ) {
		return false;
	}

	/**
	 * Perform actions before a rank is deleted.
	 *
	 * @since 1.7.0
	 *
	 * @param WordPoints_Rank $rank The rank that is being deleted.
	 */
	do_action( 'wordpoints_pre_delete_rank', $rank );

	$deleted = $wpdb->delete(
		$wpdb->wordpoints_ranks
		, array( 'id' => $id )
		, array( '%d' )
	);

	if ( ! $deleted ) {
		return false;
	}

	$rank_meta_ids = $wpdb->get_col(
		$wpdb->prepare(
			"
				SELECT meta_id
				FROM {$wpdb->wordpoints_rankmeta}
				WHERE wordpoints_rank_id = %d
			"
			, $id
		)
	);

	WordPoints_Rank_Groups::get_group( $rank->rank_group )->remove_rank( $id );

	wp_cache_delete( $id, 'wordpoints_ranks' );

	foreach ( $rank_meta_ids as $mid ) {
		delete_metadata_by_mid( 'wordpoints_rank', $mid );
	}

	/**
	 * Perform actions when a rank is deleted.
	 *
	 * @since 1.7.0
	 *
	 * @param int $rank_id The ID of the rank that was deleted.
	 */
	do_action( 'wordpoints_delete_rank', $id );

	return true;
}

/**
 * Update a rank.
 *
 * Existing metadata will not be deleted, but the meta fields passed in $meta
 * will be updated.
 *
 * @since 1.7.0
 *
 * @param int    $id       The ID of a rank.
 * @param string $name     The new name for the rank.
 * @param string $type     The type of the rank.
 * @param string $group    The slug of the group.
 * @param int    $position The position this rank should have in the group.
 * @param array  $meta     The new metadata for the rank.
 *
 * @return bool|WP_Error True the rank was updated successfully, or false/WP_Error on failure.
 */
function wordpoints_update_rank( $id, $name, $type, $group, $position, array $meta = array() ) {

	global $wpdb;

	$rank = wordpoints_get_rank( $id );

	if ( ! $rank ) {
		return false;
	}

	if ( ! WordPoints_Rank_Groups::is_type_registered_for_group( $type, $group ) ) {
		return false;
	}

	$rank_type = WordPoints_Rank_Types::get_type( $type );

	if ( ! $rank_type ) {
		return false;
	}

	$meta = $rank_type->validate_rank_meta( $meta );
	if ( false === $meta || is_wp_error( $meta ) ) {
		return $meta;
	}

	/**
	 * Perform actions before a rank is updated.
	 *
	 * @since 1.7.0
	 *
	 * @param WordPoints_Rank $rank     The rank that is being updated.
	 * @param string          $name     The new name for the rank.
	 * @param string          $type     The new type of the rank.
	 * @param string          $group    The slug of the new group.
	 * @param int             $position The new position this rank should have in the group.
	 * @param array           $meta     The new metadata for the rank.
	 */
	do_action( 'wordpoints_pre_update_rank', $rank, $name, $type, $group, $position, $meta );

	$updated = $wpdb->update(
		$wpdb->wordpoints_ranks
		, array( 'name' => $name, 'type' => $type, 'rank_group' => $group )
		, array( 'id' => $id )
		, '%s'
		, '%d'
	);

	if ( false === $updated ) {
		return false;
	}

	wp_cache_delete( $id, 'wordpoints_ranks' );

	foreach ( $meta as $meta_key => $meta_value ) {
		wordpoints_update_rank_meta( $id, $meta_key, $meta_value );
	}

	$rank_group = WordPoints_Rank_Groups::get_group( $group );

	if ( $rank->rank_group !== $group ) {

		$previous_group = WordPoints_Rank_Groups::get_group( $rank->rank_group );
		if ( $previous_group ) {
			$previous_group->remove_rank( $rank->ID );
		}

		$rank_group->add_rank( $rank->ID, $position );

	} else {

		if ( $position !== $rank_group->get_rank_position( $rank->ID ) ) {
			$rank_group->move_rank( $rank->ID, $position );
		} else {
			// If the position doesn't change, we still need refresh the ranks of
			// users who have this rank, if the metadata or type has changed.
			if ( $meta || $type !== $rank->type ) {
				wordpoints_refresh_rank_users( $rank->ID );
			}
		}
	}

	/**
	 * Perform actions when a rank is updated.
	 *
	 * @since 1.7.0
	 *
	 * @param int $rank_id The ID of the rank that was updated.
	 */
	do_action( 'wordpoints_update_rank', $id );

	return true;
}

/**
 * Get the data for a rank.
 *
 * @since 1.7.0
 *
 * @param int $id The ID of the rank whose data to get.
 *
 * @return WordPoints_Rank|false The rank object, or false if it doesn't exist.
 */
function wordpoints_get_rank( $id ) {

	$rank = new WordPoints_Rank( $id );

	if ( ! $rank->exists() ) {
		return false;
	}

	return $rank;
}

/**
 * Format a rank name for display.
 *
 * @since 1.7.0
 *
 * @param int    $rank_id The ID of the rank to format.
 * @param string $context The context in which the rank will be displayed.
 * @param array  $args    {
 *        Other optional arguments.
 *
 *        @type int $user_id ID of the user the rank is being displayed with.
 * }
 *
 * @return string The integer value of $points formatted for display.
 */
function wordpoints_format_rank( $rank_id, $context, array $args = array() ) {

	$rank = wordpoints_get_rank( $rank_id );

	if ( ! $rank ) {
		return false;
	}

	$formatted = '<span class="wordpoints-rank">' . $rank->name . '</span>';

	/**
	 * Format a rank for display.
	 *
	 * @since 1.7.0
	 *
	 * @param string          $formatted The formatted rank name.
	 * @param WordPoints_Rank $rank      The rank object.
	 * @param string          $context   The context in which the rank will be displayed.
	 * @param array           $args      {
	 *        Other arguments (all optional, may be empty).
	 *
	 *        @type int $user_id The ID of the user the rank is being displayed with.
	 * }
	 */
	return apply_filters( 'wordpoints_format_rank', $formatted, $rank, $context, $args );
}

//
// Rank Meta.
//

/**
 * Add a meta field for a rank of this type.
 *
 * @since 1.7.0
 *
 * @see add_metadata() For fuller explanation of args and return value.
 *
 * @param int    $rank_id    The ID of the rank to add the meta field for.
 * @param string $meta_key   The key for this meta field.
 * @param mixed  $meta_value The value for this meta field.
 * @param bool   $unique     Whether this meta field must be unique for this rank.
 *
 * @return int|bool The meta ID on success, false on failure.
 */
function wordpoints_add_rank_meta( $rank_id, $meta_key, $meta_value, $unique = false ) {

	return add_metadata( 'wordpoints_rank', $rank_id, $meta_key, $meta_value, $unique );
}

/**
 * Update a meta field for a rank of this type.
 *
 * @since 1.7.0
 *
 * @see update_metadata() For fuller explanation of args and return value.
 *
 * @param int    $rank_id    The ID of the rank to add the meta field for.
 * @param string $meta_key   The key for this meta field.
 * @param mixed  $meta_value The new value for this meta field.
 * @param mixed  $prev_value The previous value for this meta field.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function wordpoints_update_rank_meta( $rank_id, $meta_key, $meta_value, $prev_value = '' ) {

	return update_metadata( 'wordpoints_rank', $rank_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Delete meta fields for a rank of this type.
 *
 * @since 1.7.0
 *
 * @see delete_metadata() For fuller explanation of args and return value.
 *
 * @param int    $rank_id    The ID of the rank to delete metadata of.
 * @param string $meta_key   The key for this meta field.
 * @param mixed  $meta_value The value for this meta field. Default is ''.
 * @param bool   $delete_all Ignore the rank ID and delete for all ranks. Default: false.
 *
 * @return bool True on successful delete, false on failure.
 */
function wordpoints_delete_rank_meta( $rank_id, $meta_key, $meta_value = '', $delete_all = false ) {

	return delete_metadata( 'wordpoints_rank', $rank_id, $meta_key, $meta_value, $delete_all );
}

/**
 * Get metadata for a rank.
 *
 * @since 1.7.0
 *
 * @see get_metadata() For fuller explanation of args and return value.
 *
 * @param int    $rank_id  The ID of the rank to get the meta field for.
 * @param string $meta_key The meta key whose value to get.
 * @param bool   $single   Whether to retrieve a single value for this key.
 *
 * @return string|array Single metadata value or array of metadata values.
 */
function wordpoints_get_rank_meta( $rank_id, $meta_key = '', $single = false ) {

	return get_metadata( 'wordpoints_rank', $rank_id, $meta_key, $single );
}

//
// User Ranks.
//

/**
 * Get a user's rank.
 *
 * @since 1.7.0
 *
 * @param int    $user_id The ID of the user whose rank to get.
 * @param string $group   The rank group to get the rank from.
 *
 * @return int|false The ID of the rank this user has, or false for invalid args.
 */
function wordpoints_get_user_rank( $user_id, $group ) {

	global $wpdb;

	if ( ! wordpoints_posint( $user_id ) ) {
		return false;
	}

	$rank_group = WordPoints_Rank_Groups::get_group( $group );

	if ( ! $rank_group ) {
		return false;
	}

	$group_ranks = wp_cache_get( $group, 'wordpoints_user_ranks' );

	foreach ( (array) $group_ranks as $_rank_id => $user_ids ) {
		if ( isset( $user_ids[ $user_id ] ) ) {
			  $rank_id = $_rank_id;
			  break;
		}
	}

	if ( ! isset( $rank_id ) ) {

		$rank_id = $wpdb->get_var(
			$wpdb->prepare(
				"
					SELECT user_ranks.rank_id
					FROM {$wpdb->wordpoints_user_ranks} user_ranks
					LEFT JOIN {$wpdb->wordpoints_ranks} AS ranks
						ON ranks.id = user_ranks.rank_id
							AND ranks.rank_group = %s
					WHERE user_ranks.user_id = %d
						AND ranks.blog_id = %d
						AND ranks.site_id = %d
				"
				, $group
				, $user_id
				, $wpdb->blogid
				, $wpdb->siteid
			)
		);

		if ( ! $rank_id ) {
			$rank_id = $rank_group->get_base_rank();
		}

		$group_ranks[ $rank_id ][ $user_id ] = $user_id;

		wp_cache_set( $group, $group_ranks, 'wordpoints_user_ranks' );
	}

	return (int) $rank_id;
}

/**
 * Get the rank of a user formatted for display.
 *
 * @since 1.7.0
 *
 * @param int    $user_id The ID of the user.
 * @param string $group   The rank group.
 * @param string $context The context in which this rank is being displayed.
 * @param array  $args    Other arguments.
 *
 * @return string|false The rank of this user formatted for dispay, or false.
 */
function wordpoints_get_formatted_user_rank( $user_id, $group, $context, array $args = array() ) {

	$rank_id = wordpoints_get_user_rank( $user_id, $group );

	if ( ! $rank_id ) {
		return false;
	}

	$args = array_merge( $args, array( 'user_id' => $user_id ) );

	return wordpoints_format_rank( $rank_id, $context, $args );
}

/**
 * Update a user's rank.
 *
 * @since 1.7.0
 *
 * @param int $user_id The ID of the user.
 * @param int $rank_id The ID of the rank to give the user.
 *
 * @return bool True if the update was successful. False otherwise.
 */
function wordpoints_update_user_rank( $user_id, $rank_id ) {

	global $wpdb;

	if ( ! wordpoints_posint( $rank_id ) || ! wordpoints_posint( $user_id ) ) {
		return false;
	}

	$rank = wordpoints_get_rank( $rank_id );

	if ( ! $rank ) {
		return false;
	}

	$old_rank_id = wordpoints_get_user_rank( $user_id, $rank->rank_group );

	if ( $rank_id === $old_rank_id ) {
		return true;
	}

	$old_rank = wordpoints_get_rank( $old_rank_id );

	switch ( $old_rank->type ) {

		case 'base':
			// If this is a base rank, it's possible that the user will not have
			// the rank ID assigned in the database, it is just assumed by default.
			$has_rank = $wpdb->get_var(
				$wpdb->prepare(
					"
						SELECT COUNT(`id`)
						FROM `{$wpdb->wordpoints_user_ranks}`
						WHERE `rank_id` = %d
							AND `user_id` = %d
					"
					, $old_rank_id
					, $user_id
				)
			);

			// If the user rank isn't in the database, we can't run an update query,
			// and need to do this insert instead.
			if ( ! $has_rank ) {

				// This user doesn't yet have a rank in this group.
				$result = $wpdb->insert(
					$wpdb->wordpoints_user_ranks
					, array(
						'user_id' => $user_id,
						'rank_id' => $rank_id,
					)
					, '%d'
				);

				break;
			}

			// If the rank was in the database, we can use the regular update method.
			// fallthru

		default:
			$result = $wpdb->update(
				$wpdb->wordpoints_user_ranks
				, array( 'rank_id' => $rank_id )
				, array(
					'user_id' => $user_id,
					'rank_id' => $old_rank_id,
				)
				, '%d'
				, '%d'
			);
	}

	if ( false === $result ) {
		return false;
	}

	$group_ranks = wp_cache_get( $rank->rank_group, 'wordpoints_user_ranks' );

	foreach ( $group_ranks as $_rank_id => $user_ids ) {
		unset( $group_ranks[ $_rank_id ][ $user_id ] );
	}

	wp_cache_set( $rank->rank_group, $group_ranks, 'wordpoints_user_ranks' );

	unset( $group_ranks );

	wp_cache_delete( $rank_id, 'wordpoints_users_with_rank' );
	wp_cache_delete( $old_rank_id, 'wordpoints_users_with_rank' );

	/**
	 * Perform actions when a user rank is updated.
	 *
	 * @since 1.7.0
	 *
	 * @param int $user_id     The ID of the user.
	 * @param int $new_rank_id The ID of the new rank the user has.
	 * @param int $old_rank_id The ID of the old rank the user used to have.
	 */
	do_action( 'wordpoints_update_user_rank', $user_id, $rank_id, $old_rank_id );

	return true;
}

/**
 * Get an array of all the users who have a given rank.
 *
 * @since 1.7.0
 *
 * @param int $rank_id The ID of the rank.
 *
 * @return int[]|false Array of user IDs or false if the $rank_id is invalid.
 */
function wordpoints_get_users_with_rank( $rank_id ) {

	global $wpdb;

	$rank = wordpoints_get_rank( $rank_id );

	if ( ! $rank ) {
		return false;
	}

	$user_ids = wp_cache_get( $rank_id, 'wordpoints_users_with_rank' );

	if ( false === $user_ids ) {

		$user_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
					SELECT `user_id`
					FROM `{$wpdb->wordpoints_user_ranks}`
					WHERE `rank_id` = %d
				"
				, $rank_id
			)
		);

		if ( 'base' === $rank->type ) {

			$other_user_ids = $wpdb->get_col(
				$wpdb->prepare(
					"
						SELECT users.`ID`
						FROM `{$wpdb->users}` AS users
						WHERE users.`ID` NOT IN (
							SELECT user_ranks.`user_id`
							FROM `{$wpdb->wordpoints_user_ranks}` AS user_ranks
							INNER JOIN `{$wpdb->wordpoints_ranks}` AS ranks
								ON ranks.`id` = user_ranks.`rank_id`
							WHERE ranks.`rank_group` = %s
						)
					"
					, $rank->rank_group
				)
			);

			$user_ids = array_merge( $user_ids, $other_user_ids );
		}

		wp_cache_set( $rank_id, $user_ids, 'wordpoints_users_with_rank' );
	}

	return $user_ids;
}

/**
 * Refresh the standings of users who have a certain rank.
 *
 * This function is called when a rank is updated to reset the user standings.
 *
 * @since 1.7.0
 *
 * @param int $rank_id The ID of the rank to refresh.
 */
function wordpoints_refresh_rank_users( $rank_id ) {

	$rank = wordpoints_get_rank( $rank_id );

	if ( ! $rank || 'base' === $rank->type ) {
		return;
	}

	$prev_rank = $rank->get_adjacent( -1 );

	if ( ! $prev_rank ) {
		return;
	}

	// Get a list of users who have this rank.
	$users = wordpoints_get_users_with_rank( $rank->ID );

	// Also get users who have the previous rank.
	$prev_rank_users = wordpoints_get_users_with_rank( $prev_rank->ID );

	// If there are some users who have this rank, check if any of them need to
	// decrease to that rank.
	if ( ! empty( $users ) ) {

		$rank_type = WordPoints_Rank_Types::get_type( $rank->type );

		foreach ( $users as $user_id ) {

			$new_rank = $rank_type->maybe_decrease_user_rank( $user_id, $rank );

			if ( $new_rank->ID === $rank->ID ) {
				continue;
			}

			wordpoints_update_user_rank( $user_id, $new_rank->ID );
		}
	}

	// If there were some users with the previous rank, check if any of them can now
	// increase to this rank.
	if ( ! empty( $prev_rank_users ) ) {

		$rank_type = WordPoints_Rank_Types::get_type( $rank->type );

		foreach ( $prev_rank_users as $user_id ) {

			$new_rank = $rank_type->maybe_increase_user_rank( $user_id, $prev_rank );

			if ( $new_rank->ID === $prev_rank->ID ) {
				continue;
			}

			wordpoints_update_user_rank( $user_id, $new_rank->ID );
		}
	}
}

// EOF
