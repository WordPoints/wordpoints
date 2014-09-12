<?php

/**
 * Class for a mock rank to use in the tests.
 *
 * @package WordPoints\Tests
 * @since 1.7.0
 */

/**
 * A mock rank to use in the tests.
 *
 * @since 1.7.0
 */
class WordPoints_Test_Rank_Type extends WordPoints_Rank_Type {

	protected $slug = 'test';

	//
	// Public Methods.
	//

	/**
	 * Hook up actions when the rank type is constructed.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {}

	/**
	 * Destroy the rank type hanlder when this rank type is deregistered.
	 *
	 * @since 1.7.0
	 */
	public function destruct() {}

	/**
	 * Transition the rank when the user earns more points.
	 *
	 * @since 1.7.0
	 *
	 * @param int    $user_id     The ID of the user.
	 * @param int    $points      The number of points.
	 * @param string $points_type The type of points.
	 */
	public function hook( $user_id, $points, $points_type ) {

		$this->maybe_transition_user_rank(
			$user_id
			, compact( $points, $points_type )
		);
	}

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

		if ( ! isset( $meta['test_meta'] ) ) {
			return false;
		}

		return $meta;
	}

	/**
	 * Display form fields for the metadata of a rank of this type.
	 *
	 * @since 1.7.0
	 *
	 * @param array $meta The metadata for a rank of this type.
	 */
	public function display_rank_meta_form_fields( array $meta ) {}

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

		if ( $rank->points_type !== $points_type ) {
			return false;
		}

		if ( $rank->points > wordpoints_get_points( $user_id, $points_type ) ) {
			return false;
		}

		return true;
	}
}

// EOF
