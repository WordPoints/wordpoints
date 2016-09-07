<?php

/**
 * A points log factory for use in the unit tests.
 *
 * @package WordPoints\PHPUnit
 * @since 2.2.0
 */

/**
 * Factory for points logs.
 *
 * @since 1.6.0 As WordPoints_UnitTest_Factory_For_Points_Log.
 * @since 2.2.0
 *
 * @method int create( $args = array(), $generation_definitions = null )
 * @method object create_and_get( $args = array(), $generation_definitions = null )
 * @method int[] create_many( $count, $args = array(), $generation_definitions = null )
 */
class WordPoints_PHPUnit_Factory_For_Points_Log extends WP_UnitTest_Factory_For_Thing {

	/**
	 * Construct the factory with a factory object.
	 *
	 * Sets up the default generation definitions.
	 *
	 * @since 1.6.0 As part of WordPoints_UnitTest_Factory_For_Points_Log.
	 * @since 2.2.0
	 */
	public function __construct( $factory = null ) {

		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'points'      => 10,
			'points_type' => 'points',
			'log_type'    => 'test',
			'text'        => new WP_UnitTest_Generator_Sequence( 'Log text %s' ),
		);
	}

	/**
	 * Create a points log.
	 *
	 * @since 1.6.0 As part of WordPoints_UnitTest_Factory_For_Points_Log.
	 * @since 2.2.0
	 *
	 * @param array $args {
	 *        Optional arguments to use.
	 *
	 *        @type int    $points      The number of points.
	 *        @type string $points_type The type of points.
	 *        @type string $log_type    The type of log.
	 *        @type array  $log_meta    Metadata for the log.
	 *        @type int    $user_id     The ID of the user the log is for.
	 *        @type string $text        The text for the log.
	 * }
	 *
	 * @return int The ID of the points log
	 */
	public function create_object( $args ) {

		if ( ! isset( $args['user_id'] ) ) {
			$args['user_id'] = $this->factory->user->create();
		}

		if ( ! isset( $args['log_meta'] ) ) {
			$args['log_meta'] = array();
		}

		$log_id = wordpoints_alter_points(
			$args['user_id']
			, $args['points']
			, $args['points_type']
			, $args['log_type']
			, $args['log_meta']
			, $args['text']
		);

		return $log_id;
	}

	/**
	 * Update a points log.
	 *
	 * @since 1.6.0 As part of WordPoints_UnitTest_Factory_For_Points_Log.
	 * @since 2.2.0
	 *
	 * @param int   $id     The ID of the points log.
	 * @param array $fields The fields to update.
	 *
	 * @return bool Whether the points log was saved successfully.
	 */
	public function update_object( $id, $fields ) {

		global $wpdb;

		return (bool) $wpdb->update(
			$wpdb->wordpoints_points_logs
			, $fields
			, array( 'id' => $id )
			, '%s'
			, array( '%d' )
		);
	}

	/**
	 * Get a points log by ID.
	 *
	 * @since 1.6.0 As part of WordPoints_UnitTest_Factory_For_Points_Log.
	 * @since 2.2.0
	 *
	 * @param int $id The points log ID.
	 *
	 * @return stdClass The points log object.
	 */
	public function get_object_by_id( $id ) {

		$query = new WordPoints_Points_Logs_Query(
			array( 'id__in' => array( $id ) )
		);

		return $query->get( 'row' );
	}
}

// EOF
