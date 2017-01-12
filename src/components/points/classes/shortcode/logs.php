<?php

/**
 * Points logs shortcode class.
 *
 * @package WordPoints\Points
 * @since   2.3.0
 */

/**
 * Handler for the points logs shortcode.
 *
 * @since 1.8.0 As WordPoints_Points_Logs_Shortcode.
 * @since 2.3.0
 */
class WordPoints_Points_Shortcode_Logs extends WordPoints_Points_Shortcode {

	/**
	 * @since 1.8.0 As part of WordPoints_Points_Logs_Shortcode.
	 * @since 2.3.0
	 */
	protected $shortcode = 'wordpoints_points_logs';

	/**
	 * @since 1.8.0 As part of WordPoints_Points_Logs_Shortcode.
	 * @since 2.3.0
	 */
	protected $pairs = array(
		'points_type' => '',
		'query'       => 'default',
		'paginate'    => 1,
		'searchable'  => 1,
		'datatables'  => null,
		'show_users'  => 1,
	);

	/**
	 * @since 1.8.0 As part of WordPoints_Points_Logs_Shortcode.
	 * @since 2.3.0
	 */
	protected function verify_atts() {

		if ( ! wordpoints_is_points_logs_query( $this->atts['query'] ) ) {
			return sprintf(
				// translators: 1. Attribute name; 2. Shortcode name; 3. Example of proper usage.
				__( 'The &#8220;%1$s&#8221; attribute of the %2$s shortcode must be the slug of a registered points log query. Example: %3$s.', 'wordpoints' )
				, 'query'
				, '<code>[' . sanitize_key( $this->shortcode ) . ']</code>'
				, '<code>[' . sanitize_key( $this->shortcode ) . ' <b>query="default"</b> points_type="points"]</code>'
			);
		}

		if ( false === wordpoints_int( $this->atts['paginate'] ) ) {
			$this->atts['paginate'] = 1;
		}

		// Back-compat. Needs to stay here "forever" for legacy installs.
		if ( isset( $this->atts['datatables'] ) ) {
			$this->atts['paginate'] = wordpoints_int( $this->atts['datatables'] );
		}

		if ( false === wordpoints_int( $this->atts['show_users'] ) ) {
			$this->atts['show_users'] = 1;
		}

		return parent::verify_atts();
	}

	/**
	 * @since 1.8.0 As part of WordPoints_Points_Logs_Shortcode.
	 * @since 2.3.0
	 */
	protected function generate() {

		ob_start();
		wordpoints_show_points_logs_query(
			$this->atts['points_type']
			, $this->atts['query']
			, array(
				'paginate'   => $this->atts['paginate'],
				'show_users' => $this->atts['show_users'],
				'searchable' => $this->atts['searchable'],
			)
		);

		return ob_get_clean();
	}
}

// EOF
