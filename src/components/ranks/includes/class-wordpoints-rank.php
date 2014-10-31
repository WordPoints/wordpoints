<?php

/**
 * Class to represent a rank.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Represents a rank.
 *
 * This class is in the line of the WP_User class, and implements some of the same
 * methods. There are differences, of course, one of the most obvious being that
 * ranks don't have roles and capabilities. Other differences include the following:
 *
 * - The __set() magic method is not implemented, because it doesn't actually affect
 *   the database value, and is therefore confusing. Attempting to set object vars
 *   will result in a _doing_it_wrong() notice.
 * - The get() and has_prop() methods aren't implemented either. They aren't really
 *   needed since they are just wrappers for __get() and __isset() respectively.
 * - Ranks have only a single unique identifier, their ID, and so can't be retreived
 *   by name like users can be. Therefore the get_data() is implemented instead of
 *   get_data_by().
 * - Ranks are tied to a single blog_id and site_id, and so there is no need to pass
 *   a blog_id in when retrieving a rank, either to the contructor or get_data().
 *
 * There is one other important thing to note: the ID property is made available, but
 * the database column is lowercase (id). Both are therefore valid, but it is
 * recommended to use ID for consistency with WordPress' objects.
 *
 * @since 1.7.0
 *
 * @property-read int    $ID
 * @property-read string $name
 * @property-read string $type
 * @property-read string $rank_group
 * @property-read int    $blog_id
 * @property-read int    $site_id
 */
final class WordPoints_Rank {

	//
	// Private Vars.
	//

	/**
	 * The ID of the rank.
	 *
	 * Note that this is read only.
	 *
	 * @since 1.7.0
	 *
	 * @type int $ID
	 */
	private $ID;

	/**
	 * The data fields for this rank.
	 *
	 * @since 1.7.0
	 *
	 * @type stdClass $data
	 */
	private $data;

	//
	// Public Methods.
	//

	/**
	 * Construct the class for a rank.
	 *
	 * @since 1.7.0
	 *
	 * @param int|WordPoints_Rank $id The ID of a rank.
	 */
	public function __construct( $id ) {

		if ( is_a( $id, __CLASS__ ) ) {
			$this->init( $id->data );
			return;
		}

		$id = wordpoints_int( $id );

		if ( $id ) {
			$this->init( self::get_data( $id ) );
		}
	}

	/**
	 * Magic method for checking the existence of a certain custom field.
	 *
	 * @since 1.7.0
	 */
	public function __isset( $key ) {

		if ( isset( $this->data->$key ) ) {
			return true;
		}

		return metadata_exists( 'wordpoints_rank', $this->ID, $key );
	}

	/**
	 * Magic method for fetching of rank fields and metadata.
	 *
	 * @since 1.7.0
	 */
	public function __get( $key ) {

		if ( 'ID' === $key ) {
			return $this->ID;
		} elseif ( isset( $this->data->$key ) ) {
			$value = $this->data->$key;
		} else {
			$value = wordpoints_get_rank_meta( $this->ID, $key, true );
		}

		return $value;
	}

	/**
	 * Magic method for telling users that they can't set rank fields or metadata.
	 *
	 * @since 1.7.0
	 */
	public function __set( $key, $value ) {

		if ( 'ID' !== $key && 'data' !== $key ) {

			_doing_it_wrong(
				__METHOD__
				, 'Rank objects are read-only, you cannot modify them directly.'
				, '1.7.0'
			);
		}
	}

	/**
	 * Initializes the class with the rank's data.
	 *
	 * @since 1.7.0
	 */
	public function init( $data ) {

		if ( ! isset( $data->id ) ) {
			return;
		}

		$this->data = $data;
		$this->ID = $data->id;
	}

	/**
	 * Check if this rank exists.
	 *
	 * @since 1.7.0
	 *
	 * @return bool Whether the rank exists.
	 */
	public function exists() {

		return ! empty( $this->ID );
	}

	/**
	 * Get the object of a rank adjacent to this one in its rank group.
	 *
	 * Passing a positive integer will get a rank higher than this one, and a
	 * negative integer a lower rank. For example, passing 1 will get the next rank,
	 * and passing -2 will get the rank 2 levels below this one.
	 *
	 * @since 1.7.0
	 *
	 * @param int $relative_position The position of the rank to get relative to this
	 *                               one.
	 *
	 * @return WordPoints_Rank|false The adjacent rank, or false.
	 */
	public function get_adjacent( $relative_position ) {

		if ( 0 === wordpoints_int( $relative_position ) ) {
			return $this;
		}

		$group = WordPoints_Rank_Groups::get_group( $this->rank_group );

		$position = $group->get_rank_position( $this->ID );

		$adjacent_rank_id = $group->get_rank( $position + $relative_position );

		if ( ! $adjacent_rank_id ) {
			return false;
		}

		return new WordPoints_Rank( $adjacent_rank_id );
	}

	//
	// Public Static Methods.
	//

	/**
	 * Get the data for a rank by ID.
	 *
	 * @since 1.7.0
	 *
	 * @param int $id The ID of the rank to get.
	 *
	 * @return stdClass|false The rank's data, or false if not found.
	 */
	public static function get_data( $id ) {

		$rank_data = wp_cache_get( $id, 'wordpoints_ranks' );

		if ( false !== $rank_data ) {
			return $rank_data;
		}

		global $wpdb;

		$rank_data = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT id, name, type, rank_group, blog_id, site_id
					FROM {$wpdb->wordpoints_ranks}
					WHERE id = %d
				"
				, $id
			)
		);

		if ( null === $rank_data ) {
			return false;
		}

		wp_cache_add( $rank_data->id, $rank_data, 'wordpoints_ranks' );

		return $rank_data;
	}
}

// EOF
