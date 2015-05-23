<?php

/**
 * Points rank type class.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Rank type for points amount.
 *
 * @since 1.7.0
 */
class WordPoints_Points_Rank_Type extends WordPoints_Rank_Type {

	/**
	 * @since 1.7.0
	 */
	protected $meta_fields = array(
		'points'      => array(
			'default'  => 0,
			'type'     => 'number',
			'in_title' => true,
		),
		'points_type' => array(
			'default' => '',
			'type'    => 'hidden',
		),
	);

	//
	// Public Methods.
	//

	/**
	 * Hook up actions when the rank type is constructed.
	 *
	 * @since 1.7.0
	 */
	public function __construct( array $args ) {

		parent::__construct( $args );

		$this->name = _x( 'Points', 'rank type', 'wordpoints' );

		if ( ! isset( $args['points_type'] ) ) {
			_doing_it_wrong(
				__METHOD__
				, 'WordPoints Error: The "points_type" argument is required.'
				, '1.7.0'
			);
			return;
		}

		$this->meta_fields['points']['label'] = _x( 'Points', 'form label', 'wordpoints' );
		$this->meta_fields['points_type']['default'] = $args['points_type'];

		add_action( 'wordpoints_points_altered', array( $this, 'hook' ), 10, 3 );
	}

	/**
	 * Destroy the rank type hanlder when this rank type is deregistered.
	 *
	 * @since 1.7.0
	 */
	public function destruct() {

		remove_action( 'wordpoints_points_altered', array( $this, 'hook' ), 10, 3 );
	}

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

		if ( $points_type !== $this->meta_fields['points_type']['default'] ) {
			return;
		}

		$this->maybe_transition_user_ranks( $user_id, $points > 0 );
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

		if ( ! isset( $meta['points'] ) || false === wordpoints_int( $meta['points'] ) ) {
			return new WP_Error(
				'wordpoints_points_rank_type_invalid_points'
				, __( 'The amount of points is required, and must be a valid number.', 'wordpoints' )
				, array( 'field' => 'points' )
			);
		}

		if (
			! isset( $meta['points_type'] )
			|| ! wordpoints_is_points_type( $meta['points_type'] )
		) {
			return false;
		}

		$minimum = wordpoints_get_points_minimum( $meta['points_type'] );

		if ( $meta['points'] < $minimum ) {

			return new WP_Error(
				'wordpoints_points_rank_type_points_less_than_minimum'
				, sprintf(
					__( 'The number of points must be more than the minimum (%s).', 'wordpoints' )
					, wordpoints_format_points( $minimum, $meta['points_type'], 'points_rank_error' )
				)
				, array( 'field' => 'points' )
			);
		}

		return $meta;
	}

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

		if ( $rank->points_type !== $this->meta_fields['points_type']['default'] ) {
			return false;
		}

		$user_points = wordpoints_get_points( $user_id, $rank->points_type );

		if ( $rank->points > $user_points ) {
			return false;
		}

		return true;
	}
}

// EOF
