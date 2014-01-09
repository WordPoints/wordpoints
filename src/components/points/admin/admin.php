<?php

/**
 * Points component administration.
 *
 * This code is run only on the administration pages. It registers the points
 * administration panels, etc.
 *
 * @package WordPoints\Points\Administration
 * @since 1.0.0
 */

/**
 * AJAX callbacks.
 *
 * @since 1.2.0
 */
include_once WORDPOINTS_DIR . 'components/points/admin/includes/ajax.php';

/**
 * Add admin screens to the administration menu.
 *
 * @since 1.0.0
 *
 * @action admin_menu
 * @action network_admin_menu
 */
function wordpoints_points_admin_menu() {

	$wordpoints_menu = wordpoints_get_main_admin_menu();

	// Hooks page.
	add_submenu_page(
		$wordpoints_menu
		,__( 'WordPoints - Points Hooks', 'wordpoints' )
		,__( 'Points Hooks', 'wordpoints' )
		,'manage_options'
		,'wordpoints_points_hooks'
		,'wordpoints_points_admin_screen_hooks'
	);

	// Logs page.
	add_submenu_page(
		$wordpoints_menu
		,__( 'WordPoints - Points Logs', 'wordpoints' )
		,__( 'Points Logs', 'wordpoints' )
		,'manage_options'
		,'wordpoints_points_logs'
		,'wordpoints_points_admin_screen_logs'
	);
}
add_action( 'admin_menu', 'wordpoints_points_admin_menu' );
add_action( 'network_admin_menu', 'wordpoints_points_admin_menu' );

/**
 * Display the points hooks admin page.
 *
 * @since 1.0.0
 */
function wordpoints_points_admin_screen_hooks() {

	if ( isset( $_GET['edithook'] ) || isset( $_POST['savehook'] ) || isset( $_POST['removehook'] ) ) {

		// - We're doing this without AJAX (JS).

		/**
		 * The non-JS version of the points hooks admin screen.
		 *
		 * @since 1.0.0
		 */
		include WORDPOINTS_DIR . 'components/points/admin/screens/hooks-no-js.php';

	} else {

		/**
		 * The points hooks admin screen.
		 *
		 * @since 1.0.0
		 */
		include WORDPOINTS_DIR . 'components/points/admin/screens/hooks.php';
	}
}

/**
 * Display the points logs admin page.
 *
 * @since 1.0.0
 */
function wordpoints_points_admin_screen_logs() {

	/**
	 * The points logs page template.
	 *
	 * @since 1.0.0
	 */
	include WORDPOINTS_DIR . 'components/points/admin/screens/logs.php';
}

/**
 * Add help tabs to the points hooks page.
 *
 * @since 1.0.0
 *
 * @action load-wordpoints_page_wordpoints_points_hooks
 */
function wordpoints_admin_points_hooks_help() {

	/**
	 * Add help tabs and enqueue scripts and styles for the hooks screen.
	 *
	 * @since 1.2.0
	 */
	include WORDPOINTS_DIR . 'components/points/admin/screens/hooks-load.php';
}
add_action( 'load-wordpoints_page_wordpoints_points_hooks', 'wordpoints_admin_points_hooks_help' );

/**
 * Save points hooks from the non-JS form.
 *
 * @since 1.0.0
 */
function wordpoints_no_js_points_hooks_save() {

	if ( ! isset( $_POST['savehook'] ) && ! isset( $_POST['removehook'] ) ) {
		return;
	}

	/**
	 * Save the hooks for non-JS/accessibility mode hooks screen.
	 *
	 * @since 1.2.0
	 */
	include WORDPOINTS_DIR . 'components/points/admin/screens/hooks-no-js-load.php';
}
add_action( 'load-wordpoints_page_wordpoints_points_hooks', 'wordpoints_no_js_points_hooks_save' );

/**
 * Add accessibility mode screen option to the points hooks page.
 *
 * @since 1.0.0
 *
 * @action screen_settings
 *
 * @param string    $screen_options The options for the screen.
 * @param WP_Screen $screen         The screen object.
 *
 * @return string Options for this screen.
 */
function wordpoints_admin_points_hooks_screen_options( $screen_options, $screen ) {

	$path = 'admin.php?page=wordpoints_points_hooks&accessibility-mode=';

	switch ( $screen->id ) {

		case 'wordpoints_page_wordpoints_points_hooks':
			$url = admin_url( $path );
		// fallthru

		case 'wordpoints_page_wordpoints_points_hooks-network':
			if ( ! isset( $url ) ) {
				$url = network_admin_url( $path );
			}

			$screen_options = '<p><a id="access-on" href="' . esc_url( $url ) . 'on">'
				. __( 'Enable accessibility mode', 'wordpoints' )
				. '</a><a id="access-off" href="' . esc_url( $url ) . 'off">'
				. __( 'Disable accessibility mode', 'wordpoints' ) . "</a></p>\n";
		break;
	}

	return $screen_options;
}
add_action( 'screen_settings', 'wordpoints_admin_points_hooks_screen_options', 10, 2 );

/**
 * Filter the class of the points hooks page for accessiblitiy mode.
 *
 * @since 1.0.0
 *
 * @filter admin_body_class Added when needed by wordpoints_admin_points_hooks_help()
 */
function wordpoints_points_hooks_access_body_class( $classes ) {

	return "{$classes} wordpoints_hooks_access ";
}

/**
 * Display the user's points on their profile page.
 *
 * @since 1.0.0
 *
 * @action personal_options 20 Late so stuff doesn't end up in the wrong section.
 *
 * @param WP_User $user The user object for the user being edited.
 */
function wordpoints_points_profile_options( $user ) {

	if ( current_user_can( 'set_wordpoints_points', $user->ID ) ) {

		?>

		</table>

		<h3><?php _e( 'WordPoints', 'wordpoints' ); ?></h3>
		<p><?php _e( "If you would like to change the value for a type of points, enter the desired value in the text field, and check the checkbox beside it. If you don't check the checkbox, the change will not be saved. To provide a reason for the change, fill out the text field below.", 'wordpoints' ); ?></p>
		<lable><?php _e( 'Reason', 'wordpoints' ); ?> <input type="text" name="wordpoints_set_reason" />
		<table class="form-table">

		<?php

		wp_nonce_field( 'wordpoints_points_set_profile', 'wordpoints_points_set_nonce' );

		foreach ( wordpoints_get_points_types() as $slug => $type ) {

			$points = wordpoints_get_points( $user->ID, $slug );

			?>

			<tr>
				<th scope="row"><?php echo esc_html( $type['name'] ); ?></th>
				<td>
					<input type="hidden" name="<?php echo esc_attr( "wordpoints_points_old-{$slug}" ); ?>" value="<?php echo esc_attr( $points ); ?>" />
					<input type="text" name="<?php echo esc_attr( "wordpoints_points-{$slug}" ); ?>" value="<?php echo esc_attr( $points ); ?>" />
					<input type="checkbox" value="1" name="<?php echo esc_attr( "wordpoints_points_set-{$slug}" ); ?>" />
				</td>
			</tr>

			<?php
		}

	} elseif ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {

		/**
		 * My points admin profile heading.
		 *
		 * The text displayed as the heading for the points section when the user is
		 * viewing their profile page.
		 *
		 * HTML will be escaped.
		 *
		 * @since 1.0.0
		 *
		 * @param string $heading The text for the heading.
		 */
		$heading = apply_filters( 'wordpoints_profile_points_heading', __( 'My Points', 'wordpoints' ) );

		?>

		</table>

		<h3><?php echo esc_html( $heading ); ?></h3>

		<?php

		foreach ( wordpoints_get_points_types() as $slug => $type ) {

			echo esc_html( $type['name'] ) . ': ' . wordpoints_format_points( wordpoints_get_points( $user->ID, $slug ), $slug, 'profile_page' ) . '<br />';
		}
	}
}
add_action( 'personal_options', 'wordpoints_points_profile_options', 20 );

/**
 * Save the user's points on profile edit.
 *
 * @since 1.0.0
 *
 * @action personal_options_update  User editing own profile.
 * @action edit_user_profile_update Other users editing profile.
 *
 * @param int $user_id The ID of the user being edited.
 *
 * @return void
 */
function wordpoints_points_profile_options_update( $user_id ) {

	if ( ! current_user_can( 'set_wordpoints_points', $user_id ) ) {
		return;
	}

	if (
		! isset( $_POST['wordpoints_points_set_nonce'], $_POST['wordpoints_set_reason'] )
		|| ! wp_verify_nonce( $_POST['wordpoints_points_set_nonce'], 'wordpoints_points_set_profile' )
	) {
		return;
	}

	foreach ( wordpoints_get_points_types() as $slug => $type ) {

		if ( isset( $_POST[ "wordpoints_points_set-{$slug}" ], $_POST[ "wordpoints_points-{$slug}" ], $_POST[ "wordpoints_points_old-{$slug}" ] ) ) {

			wordpoints_alter_points( $user_id, $_POST[ "wordpoints_points-{$slug}" ] - $_POST[ "wordpoints_points_old-{$slug}" ], $slug, 'profile_edit', array( 'user_id' => get_current_user_id(), 'reason' => $_POST['wordpoints_set_reason'] ) );
		}
	}
}
add_action( 'personal_options_update', 'wordpoints_points_profile_options_update' );
add_action( 'edit_user_profile_update', 'wordpoints_points_profile_options_update' );

/**
 * Add settings to the top of the admin settings form.
 *
 * Currently only displays one setting: Default Points Type.
 *
 * @since 1.0.0
 *
 * @action wordpoints_admin_settings_top
 */
function wordpoints_points_admin_settings() {

	$dropdown_args = array(
		'selected'         => wordpoints_get_default_points_type(),
		'id'               => 'default_points_type',
		'name'             => 'default_points_type',
		'show_option_none' => __( 'No default', 'wordpoints' ),
	);

	?>

	<h3><?php esc_html_e( 'Default Points Type', 'wordpoints' ); ?></h3>
	<p><?php _e( 'You can optionally set one points type to be the default. The default points type will, for example, be used by shortcodes when no type is specified. This is also useful if you only have one type of points.', 'wordpoints' ); ?></p>
	<table class="form-table">
		<tbody>
			<tr>
				<th>
					<label for="default_points_type"><?php _e( 'Default', 'wordpoints' ); ?></label>
				</th>
				<td>
					<?php wordpoints_points_types_dropdown( $dropdown_args ); ?>
				</td>
			</tr>
		</tbody>
	</table>

	<?php
}
add_action( 'wordpoints_admin_settings_top', 'wordpoints_points_admin_settings' );

/**
 * Save settings on general settings panel.
 *
 * @since 1.0.0
 *
 * @action wordpoints_admin_settings_update
 */
function wordpoints_points_admin_settings_save() {

	if ( isset( $_POST['default_points_type'] ) ) {

		if ( '-1' == $_POST['default_points_type'] ) {

			wordpoints_delete_network_option( 'wordpoints_default_points_type' );

		} elseif ( wordpoints_is_points_type( $_POST['default_points_type'] ) ) {

			wordpoints_update_network_option( 'wordpoints_default_points_type', $_POST['default_points_type'] );
		}
	}
}
add_action( 'wordpoints_admin_settings_update', 'wordpoints_points_admin_settings_save' );

// end of file /components/points/admin/admin.php
