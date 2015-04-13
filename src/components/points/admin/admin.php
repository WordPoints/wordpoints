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
 * Register admin scripts.
 *
 * @since 1.7.0
 */
function wordpoints_admin_register_scripts() {

	$assets_url = WORDPOINTS_URL . '/components/points/admin/assets';

	// CSS

	wp_register_style(
		'wordpoints-admin-points-hooks'
		, $assets_url . '/css/hooks.css'
		, array( 'dashicons' )
		, WORDPOINTS_VERSION
	);

	// JS

	wp_register_script(
		'wordpoints-admin-points-hooks'
		, $assets_url . '/js/hooks.js'
		, array( 'jquery', 'jquery-ui-droppable', 'jquery-ui-sortable', 'jquery-ui-dialog' )
		, WORDPOINTS_VERSION
	);

	wp_localize_script(
		'wordpoints-admin-points-hooks'
		, 'WordPointsHooksL10n'
		, array(
			'confirmDelete' => esc_html__( 'Are you sure that you want to delete this points type? This will delete all related logs and hooks.', 'wordpoints' )
				. ' ' . esc_html__( 'Once a points type has been deleted, you cannot bring it back.', 'wordpoints' ),
			'confirmTitle'  => esc_html__( 'Are you sure?', 'wordpoints' ),
			'deleteText'    => esc_html__( 'Delete', 'wordpoints' ),
			'cancelText'    => esc_html__( 'Cancel', 'wordpoints' ),
		)
	);
}
add_action( 'init', 'wordpoints_admin_register_scripts' );

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
		,__( 'WordPoints — Points Hooks', 'wordpoints' )
		,__( 'Points Hooks', 'wordpoints' )
		,'manage_options'
		,'wordpoints_points_hooks'
		,'wordpoints_points_admin_screen_hooks'
	);

	// Logs page.
	add_submenu_page(
		$wordpoints_menu
		,__( 'WordPoints — Points Logs', 'wordpoints' )
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

	$path = 'admin.php?page=wordpoints_points_hooks';

	switch ( $screen->id ) {

		case 'wordpoints_page_wordpoints_points_hooks':
			$url = admin_url( $path );
			// fallthru

		case 'wordpoints_page_wordpoints_points_hooks-network':
			if ( ! isset( $url ) ) {
				$url = network_admin_url( $path );
			}

			$screen_options = '<p><a id="access-on" href="' . esc_attr( esc_url( wp_nonce_url( $url, 'wordpoints_points_hooks_accessiblity', 'wordpoints-accessiblity-nonce' ) ) ) . '&amp;accessibility-mode=on">'
				. esc_html__( 'Enable accessibility mode', 'wordpoints' )
				. '</a><a id="access-off" href="' . esc_attr( esc_url( wp_nonce_url( $url, 'wordpoints_points_hooks_accessiblity', 'wordpoints-accessiblity-nonce' ) ) ) . '&amp;accessibility-mode=off">'
				. esc_html__( 'Disable accessibility mode', 'wordpoints' ) . "</a></p>\n";
		break;
	}

	return $screen_options;
}
add_action( 'screen_settings', 'wordpoints_admin_points_hooks_screen_options', 10, 2 );

/**
 * Filter the class of the points hooks page for accessibility mode.
 *
 * @since 1.0.0
 *
 * @filter admin_body_class Added when needed by wordpoints_admin_points_hooks_help()
 */
function wordpoints_points_hooks_access_body_class( $classes ) {

	return "{$classes} wordpoints_hooks_access ";
}

/**
 * Display the hook description field in the hook forms.
 *
 * @since 1.4.0
 *
 * @action wordpoints_in_points_hook_form
 *
 * @param bool                   $has_form Whether this instance displayed a form.
 * @param array                  $instance The settings for this hook instance.
 * @param WordPoints_Points_Hook $hook     The points hook object.
 */
function wordpoints_points_hook_description_form( $has_form, $instance, $hook ) {

	$description = ( isset( $instance['_description'] ) ) ? $instance['_description'] : '';

	?>

	<?php if ( $has_form ) : ?>
		<hr />
	<?php else : ?>
		<br />
	<?php endif; ?>

	<div class="hook-instance-description">
		<label for="<?php $hook->the_field_id( '_description' ); ?>"><?php echo esc_html_x( 'Description (optional):', 'points hook', 'wordpoints' ); ?></label>
		<input type="text" id="<?php $hook->the_field_id( '_description' ); ?>" name="<?php $hook->the_field_name( '_description' ); ?>" class="widefat" value="<?php echo esc_attr( $description ); ?>" />
		<p class="description"><?php echo esc_html( sprintf( _x( 'Default: %s', 'points hook description', 'wordpoints' ), $hook->get_description( 'generated' ) ) ); ?></p>
	</div>

	<br />

	<?php
}
add_action( 'wordpoints_in_points_hook_form', 'wordpoints_points_hook_description_form', 10, 3 );

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

		<h3><?php esc_html_e( 'WordPoints', 'wordpoints' ); ?></h3>
		<p><?php esc_html_e( "If you would like to change the value for a type of points, enter the desired value in the text field, and check the checkbox beside it. If you don't check the checkbox, the change will not be saved. To provide a reason for the change, fill out the text field below.", 'wordpoints' ); ?></p>
		<lable><?php esc_html_e( 'Reason', 'wordpoints' ); ?> <input type="text" name="wordpoints_set_reason" />
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
					<input type="number" name="<?php echo esc_attr( "wordpoints_points-{$slug}" ); ?>" value="<?php echo esc_attr( $points ); ?>" autocomplete="off" />
					<input type="checkbox" value="1" name="<?php echo esc_attr( "wordpoints_points_set-{$slug}" ); ?>" />
					<?php /* translators: %s is the number of points. */ ?>
					<span><?php echo esc_html( sprintf( __( '(current: %s)', 'wordpoints' ), $points ) ); ?></span>
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

		<table>
			<tbody>
				<?php foreach ( wordpoints_get_points_types() as $slug => $type ) : ?>
					<tr>
						<th scope="row" style="text-align: left;"><?php echo esc_html( $type['name'] ); ?></th>
						<td style="text-align: right;"><?php wordpoints_display_points( $user->ID, $slug, 'profile_page' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>

		<?php
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
		|| ! wordpoints_verify_nonce( 'wordpoints_points_set_nonce', 'wordpoints_points_set_profile', null, 'post' )
	) {
		return;
	}

	foreach ( wordpoints_get_points_types() as $slug => $type ) {

		if (
			isset(
				$_POST[ "wordpoints_points_set-{$slug}" ]
				, $_POST[ "wordpoints_points-{$slug}" ]
				, $_POST[ "wordpoints_points_old-{$slug}" ]
			)
			&& false !== wordpoints_int( $_POST[ "wordpoints_points-{$slug}" ] )
			&& false !== wordpoints_int( $_POST[ "wordpoints_points_old-{$slug}" ] )
		) {

			wordpoints_alter_points(
				$user_id
				, (int) $_POST[ "wordpoints_points-{$slug}" ] - (int) $_POST[ "wordpoints_points_old-{$slug}" ]
				, $slug
				, 'profile_edit'
				, array(
					'user_id' => get_current_user_id(),
					'reason' => wp_unslash( esc_html( $_POST['wordpoints_set_reason'] ) )
				)
			);
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
	<p><?php esc_html_e( 'You can optionally set one points type to be the default. The default points type will, for example, be used by shortcodes when no type is specified. This is also useful if you only have one type of points.', 'wordpoints' ); ?></p>
	<table class="form-table">
		<tbody>
			<tr>
				<th>
					<label for="default_points_type"><?php esc_html_e( 'Default', 'wordpoints' ); ?></label>
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

		$points_type = sanitize_key( $_POST['default_points_type'] );

		if ( '-1' === $points_type ) {

			wordpoints_update_network_option( 'wordpoints_default_points_type', '' );

		} elseif ( wordpoints_is_points_type( $points_type ) ) {

			wordpoints_update_network_option( 'wordpoints_default_points_type', $points_type );
		}
	}
}
add_action( 'wordpoints_admin_settings_update', 'wordpoints_points_admin_settings_save' );

/**
 * Display notices to the user on the administration panels.
 *
 * @since 1.9.0
 */
function wordpoints_points_admin_notices() {

	if (
		( ! isset( $_GET['page'] ) || 'wordpoints_points_hooks' !== $_GET['page'] )
		&& current_user_can( 'manage_wordpoints_points_types' )
		&& ! wordpoints_get_points_types()
	) {

		wordpoints_show_admin_message(
			sprintf(
				__( 'Welcome to WordPoints! Get started by <a href="%s">creating a points type</a>.', 'wordpoints' )
				, esc_attr( self_admin_url( 'admin.php?page=wordpoints_points_hooks' ) )
			)
		);
	}
}
add_action( 'admin_notices', 'wordpoints_points_admin_notices' );

// EOF
