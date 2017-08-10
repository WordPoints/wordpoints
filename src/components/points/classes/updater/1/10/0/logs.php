<?php

/**
 * Logs 1.10.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates the points logs to 1.10.0.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Updater_1_10_0_Logs implements WordPoints_RoutineI {

	/**
	 * Whether to update network or regular logs.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $network;

	/**
	 * @since 2.4.0
	 *
	 * @param bool $network Whether to update network or regular logs.
	 */
	public function __construct( $network = false ) {

		$this->network = $network;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		$query_args = array(
			'log_type'   => 'post_delete',
			'meta_query' => array(
				array( 'key' => 'post_title', 'compare' => 'EXISTS' ),
			),
		);

		if ( $this->network ) {
			$query_args['blog_id'] = false;
		}

		$query = new WordPoints_Points_Logs_Query( $query_args );

		$logs = $query->get();

		foreach ( $logs as $log ) {
			wordpoints_delete_points_log_meta( $log->id, 'post_title' );
		}

		wordpoints_regenerate_points_logs( $logs );
	}
}

// EOF
