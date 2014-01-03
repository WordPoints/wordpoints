<?php

/**
 * WordPoints administration sreen: points logs.
 *
 * @package WordPoints\Points\Administration
 * @since 1.0.0
 */

if ( is_network_admin() ) {
	$title = __( 'WordPoints - Network Points Logs', 'wordpoints' );
} else {
	$title = __( 'WordPoints - Points Logs', 'wordpoints' );
}

?>

<div class="wrap">
	<h2><?php echo esc_html( $title ); ?></h2>
	<p class="wordpoints-admin-panel-desc"><?php _e( 'View recent points transactions.', 'wordpoints' ); ?></p>

	<?php

		/**
		 * Before points logs on admin panel.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wordpoints_admin_points_logs' );

		$points_types = wordpoints_get_points_types();

		if ( empty( $points_types ) ) {

			wordpoints_show_admin_error( sprintf( __( 'You need to <a href="%s">create a type of points</a> before you can use this page.', 'wordpoints' ), 'admin.php?page=wordpoints_points_hooks' ) );

		} else {

			// Show a tab for each points type.
			$tabs = array();

			foreach ( $points_types as $slug => $settings ) {

				$tabs[ $slug ] = $settings['name'];
			}

			wordpoints_admin_show_tabs( $tabs, false );

			if ( is_network_admin() ) {
				$query = 'network';
			} else {
				$query = 'default';
			}

			// Get and display the logs based on current points type.
			wordpoints_show_points_logs_query( wordpoints_admin_get_current_tab( $tabs ), $query );
		}

		/**
		 * After points logs on administration panel.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wordpoints_admin_points_logs_after' );

	?>

</div>
