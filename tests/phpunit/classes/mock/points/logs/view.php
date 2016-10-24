<?php

/**
 * Mock points logs view class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.2.0
 */

/**
 * Mock for the base points logs view class.
 *
 * @since 2.2.0
 */
class WordPoints_PHPUnit_Mock_Points_Logs_View extends WordPoints_Points_Logs_View {

	/**
	 * The methods called.
	 *
	 * @since 2.2.0
	 *
	 * @var array
	 */
	public $calls = array();

	/**
	 * The current search term.
	 *
	 * @since 2.2.0
	 *
	 * @var string
	 */
	public $search_term = '';

	/**
	 * The current page number.
	 *
	 * @since 2.2.0
	 *
	 * @var int
	 */
	public $page_number = 1;

	/**
	 * The current number of logs to display per page.
	 *
	 * @since 2.2.0
	 *
	 * @var int
	 */
	public $per_page = 3;

	/**
	 * @since 2.2.0
	 */
	protected function before() {
		$this->calls[] = array( 'method' => __FUNCTION__, 'args' => array()  );
	}

	/**
	 * @since 2.2.0
	 */
	protected function no_logs() {
		$this->calls[] = array( 'method' => __FUNCTION__, 'args' => array() );
	}

	/**
	 * @since 2.2.0
	 */
	protected function log( $log ) {

		$this->calls[] = array(
			'method'  => __FUNCTION__,
			'args'    => array( $log ),
			'i'       => $this->i,
			'site_id' => get_current_blog_id(),
		);
	}

	/**
	 * @since 2.2.0
	 */
	protected function after() {
		$this->calls[] = array( 'method' => __FUNCTION__, 'args' => array()  );
	}

	/**
	 * @since 2.2.0
	 */
	protected function get_search_term() {

		$this->calls[] = array( 'method' => __FUNCTION__, 'args' => array()  );

		return $this->search_term;
	}

	/**
	 * @since 2.2.0
	 */
	protected function get_page_number() {

		$this->calls[] = array( 'method' => __FUNCTION__, 'args' => array()  );

		return $this->page_number;
	}

	/**
	 * @since 2.2.0
	 */
	protected function get_per_page() {

		$this->calls[] = array( 'method' => __FUNCTION__, 'args' => array()  );

		return $this->per_page;
	}
}

// EOF
