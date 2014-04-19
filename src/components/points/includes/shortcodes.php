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

	$atts = shortcode_atts(
		array(
			'users'       => 10,
			'points_type' => '',
		)
		,$atts
		,'wordpoints_points_top'
	);

	if ( ! wordpoints_posint( $atts['users'] ) ) {

		return wordpoints_shortcode_error( __( 'The &#8220;users&#8221; attribute of the <code>[wordpoints_points_top]</code> shortcode must be a positive integer. Example: <code>[wordpoints_points_top <b>users="10"</b> type="points"]</code>.', 'wordpoints' ) );

	} elseif ( ! wordpoints_is_points_type( $atts['points_type'] ) ) {

		$atts['points_type'] = wordpoints_get_default_points_type();

		if ( ! $atts['points_type'] ) {

			return wordpoints_shortcode_error( __( 'The &#8220;points_type&#8221; attribute of the <code>[wordpoints_points_top]</code> shortcode must be the slug of a points type. Example: <code>[wordpoints_points_top points_type="points"]</code>.', 'wordpoints' ) );
		}
	}

	wp_enqueue_style( 'wordpoints-top-users' );

	$top_users = wordpoints_points_get_top_users( $atts['users'], $atts['points_type'] );

	$position = 1;

	$table = '<table class="wordpoints-points-top-users">';

	foreach ( $top_users as $user_id ) {

		$user = get_userdata( $user_id );

		$table .= '<tr class="top-' . $position . '">
			<td>' . number_format_i18n( $position ) . '</td>
			<td>' . get_avatar( $user_id, 32 ) . '</td>
			<td>' . sanitize_user_field( 'display_name', $user->display_name, $user_id, 'display' ) . '</td>
			<td>' . wordpoints_get_formatted_points( $user_id, $atts['points_type'], 'top_users_shortcode' ) . '</td>
		</tr>';

		$position++;
	}

	$table .= '</table>';

	return $table;
}
add_shortcode( 'wordpoints_points_top', 'wordpoints_points_top_shortcode' );

/**
 * Points logs shortcode.
 *
 * @since 1.0.0
 *
 * @shortcode wordpoints_points_logs
 *
 * @param array $atts The shortcode attributes. {
 *        @type string $points_type The type of points to display. Required.
 *        @type string $query       The logs query to display.
 *        @type int    $datatables  Whether the table should be a datatable. 1 or 0.
 *        @type int    $show_users  Whether to show the 'Users' column in the table.
 * }
 *
 * @return string
 */
function wordpoints_points_logs_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'points_type' => null,
			'query'       => 'default',
			'datatables'  => 1,
			'show_users'  => 1,
		)
		,$atts
		,'wordpoints_points_logs'
	);

	if ( ! wordpoints_is_points_type( $atts['points_type'] ) ) {

		$atts['points_type'] = wordpoints_get_default_points_type();

		if ( ! $atts['points_type'] ) {

			return wordpoints_shortcode_error( __( 'The &#8220;points_type&#8221; attribute of the <code>[wordpoints_points_logs]</code> shortcode must be the slug of a points type. Example: <code>[wordpoints_points_logs points_type="points"]</code>.', 'wordpoints' ) );
		}

	} elseif ( ! wordpoints_is_points_logs_query( $atts['query'] ) ) {

		return wordpoints_shortcode_error( __( 'The &#8220;query&#8221; attribute of the <code>[wordpoints_points_logs]</code> shortcode must be the slug of a registered points log query. Example: <code>[wordpoints_points_logs <b>query="default"</b> points_type="points"]</code>.', 'wordpoints' ) );
	}

	if ( false === wordpoints_int( $atts['datatables'] ) ) {
		$atts['datatables'] = 1;
	}

	if ( false === wordpoints_int( $atts['show_users'] ) ) {
		$atts['show_users'] = 1;
	}

	ob_start();
	wordpoints_show_points_logs_query( $atts['points_type'], $atts['query'], array( 'datatable' => $atts['datatables'], 'show_users' => $atts['show_users'] ) );
	return ob_get_clean();
}
add_shortcode( 'wordpoints_points_logs', 'wordpoints_points_logs_shortcode' );

/**
 * Display the points of a user.
 *
 * @since 1.3.0
 *
 * @shortcode wordpoints_points
 *
 * @param array $atts {
 *        The shortcode attributes.
 *
 *        @type string $points_type The type of points to display.
 *        @type int    $user_id     The ID of the user whose points should be
 *                                  displayed. Defaults to the current user.
 * }
 *
 * @return string The points for the user.
 */
function wordpoints_points_shortcode( $atts ) {

	$atts = shortcode_atts(
		array( 'points_type' => '', 'user_id' => 0 )
		,$atts
		,'wordpoints_points'
	);

	if ( ! wordpoints_is_points_type( $atts['points_type'] ) ) {

		$atts['points_type'] = wordpoints_get_default_points_type();

		if ( ! $atts['points_type'] ) {

			return wordpoints_shortcode_error( __( 'The &#8220;points_type&#8221; attribute of the <code>[wordpoints_points]</code> shortcode must be the slug of a points type. Example: <code>[wordpoints_points points_type="points"]</code>.', 'wordpoints' ) );
		}
	}

	if ( ! wordpoints_posint( $atts['user_id'] ) ) {
		$atts['user_id'] = get_current_user_id();
	}

	return wordpoints_get_formatted_points( $atts['user_id'], $atts['points_type'], 'points-shortcode' );
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

	$atts = shortcode_atts(
		array( 'points_type' => '' )
		, $atts
		, 'wordpoints_how_to_get_points'
	);

	if ( ! wordpoints_is_points_type( $atts['points_type'] ) ) {

		$atts['points_type'] = wordpoints_get_default_points_type();

		if ( ! $atts['points_type'] ) {

			return wordpoints_shortcode_error( __( 'The &#8220;points_type&#8221; attribute of the <code>[wordpoints_how_to_get_points]</code> shortcode must be the slug of a points type. Example: <code>[wordpoints_how_to_get_points points_type="points"]</code>.', 'wordpoints' ) );
		}
	}

	$hooks = WordPoints_Points_Hooks::get_points_type_hooks( $atts['points_type'] );

	$html = '<table class="wordpoints-how-to-get-points">'
		. '<thead><th style="padding-right: 10px">' . _x( 'Points', 'column name', 'wordpoints' ) . '</th>'
		. '<th>' . _x( 'Action', 'column name', 'wordpoints' ) . '</th></thead>'
		. '<tbody>';

	foreach ( $hooks as $hook_id ) {

		$hook = WordPoints_Points_Hooks::get_handler( $hook_id );

		if ( ! $hook ) {
			continue;
		}

		$points = $hook->get_points();

		if ( ! $points ) {
			continue;
		}

		$html .= '<tr><td>' . wordpoints_format_points( $points, $hook->points_type(), 'how-to-get-points-shortcode' ) . '</td>'
			. '<td>' . esc_html( $hook->get_description() ) . '</td></tr>';
	}

	if ( is_wordpoints_network_active() ) {

		WordPoints_Points_Hooks::set_network_mode( true );

		$hooks = WordPoints_Points_Hooks::get_points_type_hooks( $atts['points_type'] );

		foreach ( $hooks as $hook_id ) {

			$hook = WordPoints_Points_Hooks::get_handler( $hook_id );

			if ( ! $hook ) {
				continue;
			}

			$points = $hook->get_points( 'network_' . $hook->get_number() );

			if ( ! $points ) {
				continue;
			}

			$html .= '<tr><td>' . wordpoints_format_points( $points, $hook->points_type(), 'how-to-get-points-shortcode' ) . '</td>'
				. '<td>' . esc_html( $hook->get_description() ) . '</td></tr>';
		}

		WordPoints_Points_Hooks::set_network_mode( false );
	}

	$html .= '</tbody></table>';

	return $html;
}
add_shortcode( 'wordpoints_how_to_get_points', 'wordpoints_how_to_get_points_shortcode' );

// end of file /components/points/includes/shortcodes.php
