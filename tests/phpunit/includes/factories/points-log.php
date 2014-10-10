<?php

/**
 * A points log factory for use in the unit tests.
 *
 * @package WordPoints\Tests
 * @since 1.6.0
 */

/**
 * Factory for points logs.
 *
 * @since 1.6.0
 */
class WordPoints_UnitTest_Factory_For_Points_Log extends WP_UnitTest_Factory_For_Thing {

	//
	// Private Vars.
	//

	/**
	 * Whether we are listening for the insert ID of a query.
	 *
	 * @since 1.6.0
	 *
	 * @var bool $listen_for_insert_id
	 */
	private $listen_for_insert_id = false;

	/**
	 * The insert ID of the last points log.
	 *
	 * @since 1.6.0
	 *
	 * @var int $insert_id
	 */
	private $insert_id;

	//
	// Standard Methods.
	//

	/**
	 * Construct the factory with a factory object.
	 *
	 * Sets up the default generation definitions.
	 *
	 * @since 1.6.0
	 */
	public function __construct( $factory = null ) {

		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'points'      => 10,
			'points_type' => 'points',
			'log_type'    => 'test',
		);
	}

	/**
	 * Create a points log.
	 *
	 * @since 1.6.0
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

		global $wpdb;

		if ( ! isset( $args['user_id'] ) ) {
			$args['user_id'] = $this->factory->user->create();
		}

		if ( ! isset( $args['log_meta'] ) ) {
			$args['log_meta'] = array();
		}

		if ( empty( $args['log_meta'] ) ) {
			$this->listen_for_insert_id = false;
			add_action( 'query', array( $this, 'get_log_id' ) );
		}

		wordpoints_alter_points(
			$args['user_id']
			, $args['points']
			, $args['points_type']
			, $args['log_type']
			, $args['log_meta']
		);

		if ( empty( $args['log_meta'] ) ) {
			remove_action( 'query', array( $this, 'get_log_id' ) );
			$this->log_id = $wpdb->insert_id;
		}

		if ( isset( $args['text'] ) ) {
			$this->update_object( $this->log_id, array( 'text' => $args['text'] ) );
		}

		return $this->log_id;
	}

	/**
	 * Update a points log.
	 *
	 * @since 1.6.0
	 *
	 * @param int $id The ID of the points log.
	 * @param array $fields The fields to update.
	 *
	 * @return
	 */
	public function update_object( $id, $fields ) {

		global $wpdb;

		return $wpdb->update(
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
	 * @since 1.6.0
	 *
	 * @pram int $id The points log ID.
	 *
	 * @return stdClass The points log object.
	 */
	public function get_object_by_id( $id ) {

		$query = new WordPoints_Points_Log_Query( array( 'id' => $id ) );

		return $query->get( 'row' );
	}

	//
	// Non-standard Methods.
	//

	/**
	 * Helps get the log ID of an inserted hook when meta is also being inserted.
	 *
	 * @since 1.6.0
	 *
	 * @WordPress\filter query Added by self::create_object().
	 *
	 * @param string $sql The SQL for a query.
	 *
	 * @return string The query SQL.
	 */
	public function get_log_id( $sql ) {

		global $wpdb;

		if (
			! $this->listen_for_insert_id
			&& false !== strpos( $sql, "INSERT INTO {$wpdb->wordpoints_points_logs}" )
		) {
			$this->listen_for_insert_id = true;
		} elseif ( $this->listen_for_insert_id ) {
			$this->insert_id = $wpdb->insert_id;
		}

		return $sql;
	}
}

// EOF
