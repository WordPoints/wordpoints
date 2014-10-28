<?php

/**
 * Integrate Ranks with the points component.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Add support for the %rank% placeholder in the My Points widget.
 *
 * @since 1.7.0
 *
 * @WordPress\filter wordpoints_points_widget_text 30 After esc_html().
 */
function wordpoints_ranks_points_widget_text_filter( $text, $instance ) {

	if ( is_user_logged_in() ) {

		$rank = wordpoints_get_formatted_user_rank(
			get_current_user_id()
			, "points_type-{$instance['points_type']}"
			, 'my-points-widget'
			, array( 'widget_settings' => $instance )
		);

		$text = str_replace( '%rank%', $rank, $text );
	}

	return $text;
}
add_filter( 'wordpoints_points_widget_text', 'wordpoints_ranks_points_widget_text_filter', 30, 2 );

/**
 * Display a message explaining the %rank% placeholder.
 *
 * @since 1.7.0
 */
function wordpoints_ranks_my_points_widget_below_text_field() {

	?>
	<br />
	<small><i><?php echo esc_html( sprintf( __( '%s will be replaced with the rank of the logged in user', 'wordpoints' ), '%rank%' ) ); ?></i></small>
	<?php
}
add_action( 'wordpoints_my_points_widget_below_text_field', 'wordpoints_ranks_my_points_widget_below_text_field' );

/**
 * Add a user's rank to their name in the points top users table.
 *
 * @since 1.7.0
 */
function wordpoints_ranks_points_top_users_username_filter( $name, $user_id, $points_type, $context ) {

	$rank = wordpoints_get_formatted_user_rank(
		$user_id
		, "points_type-{$points_type}"
		, $context
	);

	$name = "{$name} ({$rank})";

	return $name;
}
add_filter( 'wordpoints_points_top_users_username', 'wordpoints_ranks_points_top_users_username_filter', 10, 4 );

// EOF
