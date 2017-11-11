<?php

/**
 * Points logs view class.
 *
 * @package WordPoints\Points
 * @since   2.2.0
 */

/**
 * Extended by classes that provide a view for the points logs.
 *
 * @since 2.2.0
 */
abstract class WordPoints_Points_Logs_View {

	/**
	 * The slug of this view.
	 *
	 * @since 2.2.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The default settings.
	 *
	 * @since 2.2.0
	 *
	 * @var array
	 */
	protected $defaults = array(
		'paginate'   => true,
		'searchable' => true,
		'show_users' => true,
	);

	/**
	 * The current settings.
	 *
	 * @since 2.2.0
	 *
	 * @var array
	 */
	protected $args;

	/**
	 * The query that the logs to display will be retrieved from.
	 *
	 * @since 2.2.0
	 *
	 * @var WordPoints_Points_Logs_Query
	 */
	protected $logs_query;

	/**
	 * The logs being displayed.
	 *
	 * @since 2.2.0
	 *
	 * @var object[]
	 */
	protected $logs;

	/**
	 * The incrementer holding the number of the log currently being displayed.
	 *
	 * For the first log this is 1, for the second log 2, etc.
	 *
	 * @since 2.2.0
	 *
	 * @var int
	 */
	protected $i;

	/**
	 * The viewing restriction object for the log currently being displayed.
	 *
	 * @since 2.2.0
	 *
	 * @var WordPoints_Points_Logs_Viewing_RestrictionI
	 */
	protected $restriction;

	//
	// Public Methods.
	//

	/**
	 * @since 2.2.0
	 *
	 * @param string                       $slug       The slug of this view.
	 * @param WordPoints_Points_Logs_Query $logs_query The query to display the logs from.
	 * @param array                        $args        {
	 *        Other args.
	 *
	 *        @type bool $paginate   Whether to paginate the results. Default true.
	 *        @type bool $searchable Whether to provide a search option. Default true.
	 *        @type bool $show_users Whether to show the users for each of the logs.
	 *                               Default true.
	 * }
	 */
	public function __construct(
		$slug,
		WordPoints_Points_Logs_Query $logs_query,
		$args = array()
	) {

		$this->slug       = $slug;
		$this->logs_query = $logs_query;
		$this->args       = array_merge( $this->defaults, $args );
	}

	/**
	 * Displays the logs view.
	 *
	 * @since 2.2.0
	 */
	public function display() {

		$this->logs = $this->get_logs();

		$this->before();

		if ( empty( $this->logs ) ) {
			$this->no_logs();
		} else {
			$this->logs();
		}

		$this->after();
	}

	//
	// Protected Methods.
	//

	/**
	 * Gets the logs to display.
	 *
	 * @since 2.2.0
	 *
	 * @return false|object[]
	 */
	protected function get_logs() {

		if ( $this->args['searchable'] ) {

			$search_term = $this->get_search_term();

			if ( $search_term ) {

				global $wpdb;

				$escaped_search_term = $wpdb->esc_like( $search_term );

				$this->logs_query->set_args(
					array( 'text' => "%{$escaped_search_term}%" )
				);
			}
		}

		if ( $this->args['paginate'] ) {

			$page     = $this->get_page_number();
			$per_page = $this->get_per_page();

			$logs = $this->logs_query->get_page( $page, $per_page );

		} else {

			$logs = $this->logs_query->get();
		}

		return $logs;
	}

	/**
	 * Displays the logs.
	 *
	 * Loops through all of the available logs and calls the {@see self::log()}
	 * method to display each one, if the current user is allowed to view it.
	 *
	 * @since 2.2.0
	 */
	protected function logs() {

		/** @var WordPoints_Points_Logs_Viewing_Restrictions $viewing_restrictions */
		$viewing_restrictions = wordpoints_component( 'points' )
			->get_sub_app( 'logs' )
			->get_sub_app( 'viewing_restrictions' );

		$current_user_id = get_current_user_id();

		if ( is_multisite() ) {
			$ms_switched_state = new WordPoints_Multisite_Switched_State();
			$current_site_id   = $ms_switched_state->backup();
		}

		$this->i = 0;

		foreach ( $this->logs as $log ) {

			if ( isset( $current_site_id ) && $current_site_id !== (int) $log->blog_id ) {
				switch_to_blog( $log->blog_id );
				$current_site_id = (int) $log->blog_id;
			}

			$this->restriction = $viewing_restrictions->get_restriction( $log );

			if ( ! $this->restriction->user_can( $current_user_id ) ) {
				continue;
			}

			if (
				! $viewing_restrictions->apply_legacy_filters( $current_user_id, $log )
			) {
				continue;
			}

			$this->i++;

			$this->log( $log );
		}

		if ( isset( $ms_switched_state ) ) {
			$ms_switched_state->restore();
		}
	}

	/**
	 * Displays content before the logs loop.
	 *
	 * It will be called even when there are no logs to loop through.
	 *
	 * @since 2.2.0
	 */
	abstract protected function before();

	/**
	 * Displays content when there are no longs to display.
	 *
	 * @since 2.2.0
	 */
	abstract protected function no_logs();

	/**
	 * Displays a log.
	 *
	 * Called for each log in the loop.
	 *
	 * @since 2.2.0
	 *
	 * @param object $log The log object.
	 */
	abstract protected function log( $log );

	/**
	 * Displays content after the logs loop.
	 *
	 * It will be called even when there were no logs to loop through.
	 *
	 * @since 2.2.0
	 */
	abstract protected function after();

	/**
	 * Gets the term currently being searched for, if any.
	 *
	 * @since 2.2.0
	 *
	 * @return string The string currently being searched for.
	 */
	abstract protected function get_search_term();

	/**
	 * Gets the number of the page currently being displayed.
	 *
	 * @since 2.2.0
	 *
	 * @return int The current page number.
	 */
	abstract protected function get_page_number();

	/**
	 * Gets the number of logs to show per page.
	 *
	 * @since 2.2.0
	 *
	 * @return int The number of logs to show per page.
	 */
	abstract protected function get_per_page();
}

// EOF
