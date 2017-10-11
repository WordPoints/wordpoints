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

	if ( 'utf8' === $wpdb->get_col_charset( $wpdb->wordpoints_ranks, 'name' ) ) {
		$name = wp_encode_emoji( $name );
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
	); // WPCS: cache pass.

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

	if ( 'utf8' === $wpdb->get_col_charset( $wpdb->wordpoints_ranks, 'name' ) ) {
		$name = wp_encode_emoji( $name );
	}

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
 * @since 2.1.0 $meta_key and $meta_value are no longer expected slashed.
 *
 * @see add_metadata() For fuller explanation of args and return value.
 *
 * @param int    $rank_id    The ID of the rank to add the meta field for.
 * @param string $meta_key   The key for this meta field. Not expected slashed.
 * @param mixed  $meta_value The value for this meta field. Not expected slashed.
 * @param bool   $unique     Whether this meta field must be unique for this rank.
 *
 * @return int|bool The meta ID on success, false on failure.
 */
function wordpoints_add_rank_meta( $rank_id, $meta_key, $meta_value, $unique = false ) {

	return add_metadata(
		'wordpoints_rank'
		, $rank_id
		, wp_slash( $meta_key )
		, wp_slash( $meta_value )
		, $unique
	);
}

/**
 * Update a meta field for a rank of this type.
 *
 * @since 1.7.0
 * @since 2.1.0 $meta_key and $meta_value are no longer expected slashed.
 *
 * @see update_metadata() For fuller explanation of args and return value.
 *
 * @param int    $rank_id    The ID of the rank to add the meta field for.
 * @param string $meta_key   The key for this meta field. Not expected slashed.
 * @param mixed  $meta_value The new value for this meta field. Not expected slashed.
 * @param mixed  $prev_value The previous value for this meta field.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function wordpoints_update_rank_meta( $rank_id, $meta_key, $meta_value, $prev_value = '' ) {

	return update_metadata(
		'wordpoints_rank'
		, $rank_id
		, wp_slash( $meta_key )
		, wp_slash( $meta_value )
		, $prev_value
	);
}

/**
 * Delete meta fields for a rank of this type.
 *
 * @since 1.7.0
 * @since 2.1.0 $meta_key and $meta_value are no longer expected slashed.
 *
 * @see delete_metadata() For fuller explanation of args and return value.
 *
 * @param int    $rank_id    The ID of the rank to delete metadata of.
 * @param string $meta_key   The key for this meta field. Not expected slashed.
 * @param mixed  $meta_value The value for this meta field. Default is ''. Not expected slashed.
 * @param bool   $delete_all Ignore the rank ID and delete for all ranks. Default: false.
 *
 * @return bool True on successful delete, false on failure.
 */
function wordpoints_delete_rank_meta( $rank_id, $meta_key, $meta_value = '', $delete_all = false ) {

	return delete_metadata(
		'wordpoints_rank'
		, $rank_id
		, wp_slash( $meta_key )
		, wp_slash( $meta_value )
		, $delete_all
	);
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
					SELECT `rank_id`
					FROM `{$wpdb->wordpoints_user_ranks}`
					WHERE `user_id` = %d
						AND `rank_group` = %s
						AND `blog_id` = %d
						AND `site_id` = %d
				"
				, $user_id
				, $group
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

	$result = $wpdb->query(
		$wpdb->prepare(
			"
				INSERT INTO `{$wpdb->wordpoints_user_ranks}`
					(`user_id`, `rank_id`, `rank_group`, `blog_id`, `site_id`)
				VALUES 
					(%d, %d, %s, %d, %d)
				ON DUPLICATE KEY
					UPDATE `rank_id` = %d
			"
			, $user_id
			, $rank_id
			, $rank->rank_group
			, $wpdb->blogid
			, $wpdb->siteid
			, $rank_id
		)
	);

	if ( false === $result ) {
		return false;
	}

	$group_ranks = wp_cache_get( $rank->rank_group, 'wordpoints_user_ranks' );

	foreach ( $group_ranks as $_rank_id => $user_ids ) {
		unset( $group_ranks[ $_rank_id ][ $user_id ] );
	}

	wp_cache_set( $rank->rank_group, $group_ranks, 'wordpoints_user_ranks' );

	unset( $group_ranks );

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
 * Updates a set of users to a new rank.
 *
 * All of the users must have previously had the same rank. This is mainly necessary
 * so that we can pass the old rank ID to the 'wordpoints_update_user_rank' action,
 * and for purposes of clearing the cache.
 *
 * @since 2.4.0
 *
 * @param int[] $user_ids     The IDs of the users to update.
 * @param int   $to_rank_id   The ID of the rank to give the users.
 * @param int   $from_rank_id The ID of the rank the users previously had.
 *
 * @return bool True if the update was successful. False otherwise.
 */
function wordpoints_update_users_to_rank( array $user_ids, $to_rank_id, $from_rank_id ) {

	global $wpdb;

	if ( ! wordpoints_posint( $to_rank_id ) || ! wordpoints_posint( $to_rank_id ) ) {
		return false;
	}

	$rank = wordpoints_get_rank( $to_rank_id );

	if ( ! $rank ) {
		return false;
	}

	if ( $to_rank_id === $from_rank_id ) {
		return true;
	}

	$prepared = $wpdb->prepare(
		', %d, %s, %d, %d'
		, $to_rank_id
		, $rank->rank_group
		, $wpdb->blogid
		, $wpdb->siteid
	);

	$result = $wpdb->query( // WPCS: unprepared SQL OK.
		$wpdb->prepare( // WPCS: unprepared SQL OK.
			"
				INSERT INTO `{$wpdb->wordpoints_user_ranks}`
					(`user_id`, `rank_id`, `rank_group`, `blog_id`, `site_id`)
				VALUES 
					(" . implode( array_map( 'absint', $user_ids ), "{$prepared}),\n\t\t\t\t\t(" ) . "{$prepared})
				ON DUPLICATE KEY
					UPDATE `rank_id` = %d
			"
			, $to_rank_id
		)
	);

	if ( false === $result ) {
		return false;
	}

	$group_ranks = wp_cache_get( $rank->rank_group, 'wordpoints_user_ranks' );

	unset( $group_ranks[ $from_rank_id ], $group_ranks[ $to_rank_id ] );

	wp_cache_set( $rank->rank_group, $group_ranks, 'wordpoints_user_ranks' );

	unset( $group_ranks );

	if ( has_action( 'wordpoints_update_user_rank' ) ) {
		foreach ( $user_ids as $user_id ) {
			/**
			 * Perform actions when a user rank is updated.
			 *
			 * @since 1.7.0
			 *
			 * @param int $user_id The ID of the user.
			 * @param int $new_rank_id The ID of the new rank the user has.
			 * @param int $old_rank_id The ID of the old rank the user used to have.
			 */
			do_action(
				'wordpoints_update_user_rank'
				, $user_id
				, $to_rank_id
				, $from_rank_id
			);
		}
	}

	return true;
}

/**
 * Get an array of all the users who have a given rank.
 *
 * @since 1.7.0
 * @deprecated 2.4.0 Use the WordPoints_User_Ranks_Query class instead.
 *
 * @param int $rank_id The ID of the rank.
 *
 * @return int[]|false Array of user IDs or false if the $rank_id is invalid.
 */
function wordpoints_get_users_with_rank( $rank_id ) {

	_deprecated_function( __FUNCTION__, '2.4.0', 'WordPoints_User_Ranks_Query class' );

	$rank = wordpoints_get_rank( $rank_id );

	if ( ! $rank ) {
		return false;
	}

	$query = new WordPoints_User_Ranks_Query(
		array( 'fields' => 'user_id', 'rank_id' => $rank_id )
	);

	$user_ids = $query->get( 'col' );

	if ( false === $user_ids ) {
		return false;
	}

	$user_ids = array_map( 'intval', $user_ids );

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

	// Maybe decrease users who have this rank.
	$maybe_decrease = new WordPoints_User_Ranks_Maybe_Decrease( $rank );
	$maybe_decrease->run();

	// Also maybe increase users who have the previous rank.
	$maybe_increase = new WordPoints_User_Ranks_Maybe_Increase( $prev_rank );
	$maybe_increase->run();
}

/**
 * Sets the rank of a new user when they become a member of the site.
 *
 * @since 2.4.0
 *
 * @WordPress\action user_register
 * @WordPress\action add_user_to_blog
 *
 * @param int $user_id The ID of the user.
 */
function wordpoints_set_new_user_ranks( $user_id ) {

	foreach ( WordPoints_Rank_Groups::get() as $rank_group ) {

		$base_rank = wordpoints_get_rank( $rank_group->get_base_rank() );

		if ( ! $base_rank ) {
			continue;
		}

		$rank_type = WordPoints_Rank_Types::get_type( $base_rank->type );

		if ( ! $rank_type ) {
			continue;
		}

		$new_rank = $rank_type->maybe_increase_user_rank(
			$user_id
			, $base_rank
		);

		// If the user should have the base rank we can't use the update function
		// because it will check the user's current rank, which will be inferred as
		// the base rank even if it isn't set in the database.
		if ( $base_rank->ID !== $new_rank->ID ) {

			wordpoints_update_user_rank( $user_id, $new_rank->ID );

		} else {

			global $wpdb;

			$wpdb->query(
				$wpdb->prepare(
					"
						INSERT INTO `{$wpdb->wordpoints_user_ranks}`
							(`user_id`, `rank_id`, `rank_group`, `blog_id`, `site_id`)
						VALUES 
							(%d, %d, %s, %d, %d)
						ON DUPLICATE KEY
							UPDATE `rank_id` = %d
					"
					, $user_id
					, $base_rank->ID
					, $base_rank->rank_group
					, $wpdb->blogid
					, $wpdb->siteid
					, $base_rank->ID
				)
			); // WPCS: cache OK.
		}
	}
}

/**
 * Deletes all ranks for a user on this site.
 *
 * @since 2.4.0
 *
 * @WordPress\action user_deleted          On single-site installs.
 * @WordPress\action remove_user_from_blog On multisite.
 *
 * @param int $user_id The ID of the user whose ranks to delete.
 */
function wordpoints_delete_user_ranks( $user_id ) {

	global $wpdb;

	$wpdb->delete(
		$wpdb->wordpoints_user_ranks
		, array(
			'user_id' => $user_id,
			'blog_id' => $wpdb->blogid,
			'site_id' => $wpdb->siteid,
		)
		, '%d'
	);

	foreach ( WordPoints_Rank_Groups::get() as $rank_group ) {

		$group_ranks = wp_cache_get( $rank_group->slug, 'wordpoints_user_ranks' );

		if ( ! is_array( $group_ranks ) ) {
			continue;
		}

		foreach ( $group_ranks as $rank_id => $user_ids ) {
			unset( $group_ranks[ $rank_id ][ $user_id ] );
		}

		wp_cache_set( $rank_group->slug, $group_ranks, 'wordpoints_user_ranks' );
	}
}

/**
 * Register the included rank types.
 *
 * @since 1.7.0
 *
 * @WordPress\action wordpoints_ranks_register
 */
function wordpoints_register_core_ranks() {}

// EOF
