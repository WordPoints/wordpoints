<?php

/**
 * Shortcodes.
 *
 * These functions can also be called directly and used as template tags.
 *
 * @package WordPoints\Points
 * @since 1.0.0
 */

/**
 * Display top users.
 *
 * @since 1.0.0
 *
 * @shortcode wordpoints_points_top
 *
 * @param array $atts The shortcode attributes. {
 *        @type int    $users       The number of users to display.
 *        @type string $points_type The type of points.
 * }
 *
 * @return string
 */
function wordpoints_points_top_shortcode( $atts ) {

	$shortcode = WordPoints_Shortcodes::get( 'wordpoints_points_top', $atts );

	return $shortcode->expand();
}
add_shortcode( 'wordpoints_points_top', 'wordpoints_points_top_shortcode' );

/**
 * Points logs shortcode.
 *
 * @since 1.0.0
 * @since 1.6.0 The datatables attribute is deprecated in favor of paginate.
 * @since 1.6.0 The searchable attribute is added.
 *
 * @shortcode wordpoints_points_logs
 *
 * @param array $atts The shortcode attributes. {
 *        @type string $points_type The type of points to display. Required.
 *        @type string $query       The logs query to display.
 *        @type int    $paginate    Whether to paginate the table. 1 or 0.
 *        @type int    $searchable  Whether to display a search form. 1 or 0.
 *        @type int    $datatables  Whether the table should be a datatable. 1 or 0.
 *                                  Deprecated in favor of paginate.
 *        @type int    $show_users  Whether to show the 'Users' column in the table.
 * }
 *
 * @return string
 */
function wordpoints_points_logs_shortcode( $atts ) {

	$shortcode = WordPoints_Shortcodes::get( 'wordpoints_points_logs', $atts );

	return $shortcode->expand();
}
add_shortcode( 'wordpoints_points_logs', 'wordpoints_points_logs_shortcode' );

/**
 * Display the points of a user.
 *
 * @since 1.3.0
 * @since 1.8.0 Added support for the post_author value of the user_id attribute.
 *
 * @shortcode wordpoints_points
 *
 * @param array $atts {
 *        The shortcode attributes.
 *
 *        @type string $points_type The type of points to display.
 *        @type mixed  $user_id     The ID of the user whose points should be
 *                                  displayed. Defaults to the current user. If set
 *                                  to post_author, the author of the current post.
 * }
 *
 * @return string The points for the user.
 */
function wordpoints_points_shortcode( $atts ) {

	$shortcode = WordPoints_Shortcodes::get( 'wordpoints_points', $atts );

	return $shortcode->expand();
}
add_shortcode( 'wordpoints_points', 'wordpoints_points_shortcode' );

/**
 * Display a list of ways users can earch points.
 *
 * @since 1.4.0
 *
 * @shortcode wordpoints_how_to_get_points
 *
 * @param array $atts {
 *        The shortcode attributes.
 *
 *        @type string $points_type The type of points to display the list for.
 * }
 *
 * @return string A list of points hooks describing how the user can earn points.
 */
function wordpoints_how_to_get_points_shortcode( $atts ) {

	$shortcode = WordPoints_Shortcodes::get( 'wordpoints_how_to_get_points', $atts );

	return $shortcode->expand();
}
add_shortcode( 'wordpoints_how_to_get_points', 'wordpoints_how_to_get_points_shortcode' );

/**
 * Base shortcode class for the points component's shortcodes.
 *
 * The points shortcodes can extend this class to get automatic validation of the
 * points_type attribute, if it is used.
 *
 * @since 1.8.0
 */
abstract class WordPoints_Points_Shortcode extends WordPoints_Shortcode {

	/**
	 * @since 1.8.0
	 */
	protected function verify_atts() {

		if ( isset( $this->pairs['points_type'] ) ) {

			$points_type = $this->get_points_type();

			if ( is_wp_error( $points_type ) ) {
				return $points_type;
			}

			$this->atts['points_type'] = $points_type;
		}

		return parent::verify_atts();
	}

	/**
	 * Get the points type from a shortcode's attributes.
	 *
	 * For use with shortcodes which display content relative to a specific points
	 * type. If the points type isn't valid, the default points type will be used if
	 * one is set. In the case that no default points type is set, a WP_Error will
	 * be returned.
	 *
	 * @since 1.8.0
	 *
	 * @return string|WP_Error The points type slug, or a WP_Error on failure.
	 */
	protected function get_points_type() {

		$points_type = $this->atts['points_type'];

		if ( ! wordpoints_is_points_type( $points_type ) ) {

			$points_type = wordpoints_get_default_points_type();

			if ( ! $points_type ) {

				$points_type = new WP_Error(
					'wordpoints_shortcode_no_points_type'
					, sprintf(
						__( 'The &#8220;%s&#8221; attribute of the %s shortcode must be the slug of a points type. Example: %s.', 'wordpoints' )
						, 'points_type'
						, '<code>[' . $this->shortcode . ']</code>'
						, '<code>[' . $this->shortcode . ' points_type="points"]</code>'
					)
				);
			}
		}

		/**
		 * Filter the points type for a shortcode.
		 *
		 * @since 1.8.0
		 *
		 * @param string|WP_Error $points_type The points type, or a WP_Error on failure.
		 * @param array           $atts        The shortcode attributes.
		 * @param string          $shortcode   The shortcode.
		 */
		return apply_filters( 'wordpoints_shortcode_points_type', $points_type, $this->atts, $this->shortcode );
	}
}

/**
 * Handler for the points top shortcode.
 *
 * @since 1.8.0
 */
class WordPoints_Points_Top_Shortcode extends WordPoints_Points_Shortcode {

	/**
	 * @since 1.8.0
	 */
	protected $shortcode = 'wordpoints_points_top';

	/**
	 * @since 1.8.0
	 */
	protected $pairs = array(
		'users'       => 10,
		'points_type' => '',
	);

	/**
	 * @since 1.8.0
	 */
	protected function verify_atts() {

		if ( ! wordpoints_posint( $this->atts['users'] ) ) {
			return __( 'The &#8220;users&#8221; attribute of the <code>[wordpoints_points_top]</code> shortcode must be a positive integer. Example: <code>[wordpoints_points_top <b>users="10"</b> type="points"]</code>.', 'wordpoints' );
		}

		return parent::verify_atts();
	}

	/**
	 * @since 1.8.0
	 */
	protected function generate() {

		ob_start();
		wordpoints_points_show_top_users(
			$this->atts['users']
			, $this->atts['points_type']
			, 'shortcode'
		);

		return ob_get_clean();
	}
}
WordPoints_Shortcodes::register( 'wordpoints_points_top', 'WordPoints_Points_Top_Shortcode' );

/**
 * Handler for the points logs shortcode.
 *
 * @since 1.8.0
 */
class WordPoints_Points_Logs_Shortcode extends WordPoints_Points_Shortcode {

	/**
	 * @since 1.8.0
	 */
	protected $shortcode = 'wordpoints_points_logs';

	/**
	 * @since 1.8.0
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
	 * @since 1.8.0
	 */
	protected function verify_atts() {

		if ( ! wordpoints_is_points_logs_query( $this->atts['query'] ) ) {
			return __( 'The &#8220;query&#8221; attribute of the <code>[wordpoints_points_logs]</code> shortcode must be the slug of a registered points log query. Example: <code>[wordpoints_points_logs <b>query="default"</b> points_type="points"]</code>.', 'wordpoints' );
		}

		if ( false === wordpoints_int( $this->atts['paginate'] ) ) {
			$this->atts['paginate'] = 1;
		}

		// Back-compat.
		if ( isset( $this->atts['datatables'] ) ) {
			$this->atts['paginate'] = wordpoints_int( $this->atts['datatables'] );
		}

		if ( false === wordpoints_int( $this->atts['show_users'] ) ) {
			$this->atts['show_users'] = 1;
		}

		return parent::verify_atts();
	}

	/**
	 * @since 1.8.0
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
WordPoints_Shortcodes::register( 'wordpoints_points_logs', 'WordPoints_Points_Logs_Shortcode' );

/**
 * Handler for the user points shortcode.
 *
 * @since 1.8.0
 */
class WordPoints_User_Points_Shortcode extends WordPoints_Points_Shortcode {

	/**
	 * @since 1.8.0
	 */
	protected $shortcode = 'wordpoints_points';

	/**
	 * @since 1.8.0
	 */
	protected $pairs = array(
		'user_id'     => 0,
		'points_type' => '',
	);

	/**
	 * @since 1.8.0
	 */
	protected function generate() {

		return wordpoints_get_formatted_points(
			$this->atts['user_id']
			, $this->atts['points_type']
			, 'points-shortcode'
		);
	}
}
WordPoints_Shortcodes::register( 'wordpoints_points', 'WordPoints_User_Points_Shortcode' );

/**
 * Handler for the points logs shortcode.
 *
 * @since 1.8.0
 */
class WordPoints_How_To_Get_Points_Shortcode extends WordPoints_Points_Shortcode {

	/**
	 * @since 1.8.0
	 */
	protected $shortcode = 'wordpoints_how_to_get_points';

	/**
	 * @since 1.8.0
	 */
	protected $pairs = array(
		'points_type' => '',
	);

	/**
	 * @since 1.8.0
	 */
	protected function generate() {

		/**
		 * Filter the extra HTML classes for the how-to-get-points table element.
		 *
		 * @since 1.6.0
		 *
		 * @param string[] $extra_classes The extra classes for the table element.
		 * @param array    $atts          The arguments for table display from the shortcode.
		 */
		$extra_classes = apply_filters( 'wordpoints_how_to_get_points_table_extra_classes', array(), $this->atts );

		$html = '<table class="wordpoints-how-to-get-points ' . esc_attr( implode( ' ', $extra_classes ) ) . '">'
			. '<thead><tr><th style="padding-right: 10px">' . esc_html_x( 'Points', 'column name', 'wordpoints' ) . '</th>'
			. '<th>' . esc_html_x( 'Action', 'column name', 'wordpoints' ) . '</th></tr></thead>'
			. '<tbody>';

		$html .= $this->list_points_hooks();

		if ( is_wordpoints_network_active() ) {

			WordPoints_Points_Hooks::set_network_mode( true );
			$html .= $this->list_points_hooks();
			WordPoints_Points_Hooks::set_network_mode( false );
		}

		$html .= '</tbody></table>';

		return $html;
	}

	/**
	 * List the points hooks for the current points type.
	 *
	 * @since 1.8.0
	 *
	 * @return string The HTML for the table rows.
	 */
	protected function list_points_hooks() {

		$network = '';

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$network = 'network_';
		}

		$hooks = WordPoints_Points_Hooks::get_points_type_hooks(
			$this->atts['points_type']
		);

		$html = '';

		foreach ( $hooks as $hook_id ) {

			$hook = WordPoints_Points_Hooks::get_handler( $hook_id );

			if ( ! $hook ) {
				continue;
			}

			$points = $hook->get_points( $network . $hook->get_number() );

			if ( ! $points ) {
				continue;
			}

			$points = wordpoints_format_points(
				$points
				, $hook->points_type()
				, 'how-to-get-points-shortcode'
			);

			$html .= '<tr><td>' . $points . '</td>'
				. '<td>' . esc_html( $hook->get_description() ) . '</td></tr>';
		}

		return $html;
	}
}
WordPoints_Shortcodes::register( 'wordpoints_how_to_get_points', 'WordPoints_How_To_Get_Points_Shortcode' );

// EOF
