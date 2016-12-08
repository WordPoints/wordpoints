<?php

/**
 * Mock points logs viewing restriction class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.2.0
 */

/**
 * Mock points logs viewing restriction.
 *
 * @since 2.2.0
 */
class WordPoints_PHPUnit_Mock_Points_Logs_Viewing_Restriction
	implements WordPoints_Points_Logs_Viewing_RestrictionI {

	/**
	 * Whether the restriction applies to this log.
	 *
	 * @since 2.2.0
	 *
	 * @var bool
	 */
	public $applies = true;

	/**
	 * Whether the current user can view this log.
	 *
	 * @since 2.2.0
	 *
	 * @var bool
	 */
	public $user_can = false;

	/**
	 * Whether to listen for the current ID of in user_can().
	 *
	 * @since 2.2.0
	 *
	 * @var bool
	 */
	public $listen_for_site;

	/**
	 * The IDs of the site recorded in user_can().
	 *
	 * @since 2.2.0
	 *
	 * @var int[]
	 */
	public $site = array();

	/**
	 * Whether to record the current site's ID of in user_can() and the constructor.
	 *
	 * @since 2.2.0
	 *
	 * @var bool
	 */
	public static $listen_for_sites;

	/**
	 * The IDs of the site recorded in user_can().
	 *
	 * @since 2.2.0
	 *
	 * @var array
	 */
	public static $sites = array();

	/**
	 * The IDs of the sites recorded in the constructor.
	 *
	 * @since 2.2.0
	 *
	 * @var array
	 */
	public static $sites_construct = array();

	/**
	 * @since 2.2.0
	 *
	 * @param object $log      The points log.
	 * @param bool   $user_can Whether the user can.
	 * @param bool   $applies  Whether this restriction applies.
	 */
	public function __construct( $log, $user_can = null, $applies = null ) {

		if ( isset( $user_can ) ) {
			$this->user_can = $user_can;
		}

		if ( isset( $applies ) ) {
			$this->applies = $applies;
		}

		if ( self::$listen_for_sites ) {
			self::$sites_construct[] = array(
				'site_id' => get_current_blog_id(),
				'log' => $log,
			);
		}
	}

	/**
	 * @since 2.2.0
	 */
	public function get_description() {
		return 'Test points log viewing restriction.';
	}

	/**
	 * @since 2.2.0
	 */
	public function applies() {
		return $this->applies;
	}

	/**
	 * @since 2.2.0
	 */
	public function user_can( $user_id ) {

		if ( $this->listen_for_site ) {
			$this->site[] = get_current_blog_id();
		}

		if ( self::$listen_for_sites ) {
			self::$sites[] = get_current_blog_id();
		}

		return $this->user_can;
	}
}

// EOF
