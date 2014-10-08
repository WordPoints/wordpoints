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
 * @param string $name The name of the rank.
 * @param string $type The rank type slug.
 * @param array  $meta Optional metadata for this rank.
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

	WordPoints_Rank_Groups::get_group( $group )->add_rank( $rank_id, $position );

	foreach ( $meta as $meta_key => $meta_value ) {
		wordpoints_add_rank_meta( $rank_id, $meta_key, $meta_value );
	}

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
 * @param int    $id   The ID of a rank.
 * @param string $name The new name for the rank.
 * @param array  $meta The new metadata for the rank.
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

	$rank_group = WordPoints_Rank_Groups::get_group( $group );

	if ( $rank->rank_group !== $group ) {

		$previous_group = WordPoints_Rank_Groups::get_group( $rank->rank_group );
		if ( $previous_group ) {
			$previous_group->remove_rank( $rank->ID );
		}

		$rank_group->add_rank( $rank->ID, $position );

	} else {

		$rank_group->move_rank( $rank->ID, $position );
	}

	foreach ( $meta as $meta_key => $meta_value ) {
		wordpoints_update_rank_meta( $id, $meta_key, $meta_value );
	}

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
 * @return int The ID of the rank this user has.
 */
function wordpoints_get_user_rank( $user_id, $group ) {

	global $wpdb;

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
		$rank_group = WordPoints_Rank_Groups::get_group( $group );
		$rank_id = $rank_group->get_base_rank();
	}

	return $rank_id;
}

/**
 * Update a user's rank.
 *
 * @since 1.7.0
 *
 * @param int $user_id The ID of the user.
 * @param int $rank_id The ID of the rank to give the user.
 */
function wordpoints_update_user_rank( $user_id, $old_rank_id, $new_rank_id ) {

	global $wpdb;

	$old_rank = wordpoints_get_rank( $old_rank_id );

	if ( 'base' === $old_rank->type ) {

		// This user doesn't yet have a rank in this group.
		return $wpdb->insert(
			$wpdb->wordpoints_user_ranks
			, array(
				'user_id' => $user_id,
				'rank_id' => $new_rank_id,
			)
			, '%d'
		);
	}

	return $wpdb->update(
		$wpdb->wordpoints_user_ranks
		, array( 'rank_id' => $new_rank_id )
		, array(
			'user_id' => $user_id,
			'rank_id' => $old_rank_id,
		)
		, '%d'
		, '%d'
	);
}

// EOF
