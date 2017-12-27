<?php

/**
 * Admin-side functions of the points component.
 *
 * @package WordPoints\Points
 * @since 2.1.0
 */

/**
 * Register points component admin scripts.
 *
 * @since 2.1.0
 *
 * @WordPress\action init
 */
function wordpoints_points_admin_register_scripts() {

	$assets_url        = WORDPOINTS_URL . '/components/points/admin/assets';
	$suffix            = SCRIPT_DEBUG ? '' : '.min';
	$manifested_suffix = SCRIPT_DEBUG ? '.manifested' : '.min';

	// CSS

	wp_register_style(
		'wordpoints-admin-points-hooks'
		, "{$assets_url}/css/hooks{$suffix}.css"
		, array( 'dashicons' )
		, WORDPOINTS_VERSION
	);

	wp_register_style(
		'wordpoints-admin-user-points'
		, "{$assets_url}/css/user-points{$suffix}.css"
		, array( 'wp-jquery-ui-dialog' )
		, WORDPOINTS_VERSION
	);

	$styles = wp_styles();

	$rtl_styles = array(
		'wordpoints-admin-points-hooks',
		'wordpoints-admin-user-points',
	);

	foreach ( $rtl_styles as $handle ) {

		$styles->add_data( $handle, 'rtl', 'replace' );

		if ( $suffix ) {
			$styles->add_data( $handle, 'suffix', $suffix );
		}
	}

	// JS

	wp_register_script(
		'wordpoints-admin-points-types'
		, "{$assets_url}/js/points-types{$suffix}.js"
		, array( 'backbone', 'jquery-ui-dialog', 'wp-util', 'postbox' )
		, WORDPOINTS_VERSION
	);

	wp_localize_script(
		'wordpoints-admin-points-types'
		, 'WordPointsPointsTypesL10n'
		, array(
			'confirmAboutTo' => esc_html__( 'You are about to delete the following points type:', 'wordpoints' ),
			'confirmDelete'  => esc_html__( 'Are you sure that you want to delete this points type? This will delete all logs, event reactions, and other data associated with this points type.', 'wordpoints' )
				. ' ' . esc_html__( 'Once a points type has been deleted, you cannot bring it back.', 'wordpoints' ),
			'confirmType'    => esc_html__( 'If you are sure you want to delete this points type, confirm by typing its name below:', 'wordpoints' ),
			'confirmLabel'   => esc_html_x( 'Name:', 'points type', 'wordpoints' ),
			'confirmTitle'   => esc_html__( 'Are you sure?', 'wordpoints' ),
			'deleteText'     => esc_html__( 'Delete', 'wordpoints' ),
			'cancelText'     => esc_html__( 'Cancel', 'wordpoints' ),
		)
	);

	wp_register_script(
		'wordpoints-hooks-reactor-points'
		, "{$assets_url}/js/hooks/reactors/points{$manifested_suffix}.js"
		, array( 'wordpoints-hooks-views' )
		, WORDPOINTS_VERSION
	);

	wp_register_script(
		'wordpoints-admin-points-hooks'
		, "{$assets_url}/js/hooks{$suffix}.js"
		, array( 'jquery', 'jquery-ui-droppable', 'jquery-ui-sortable', 'jquery-ui-dialog' )
		, WORDPOINTS_VERSION
	);

	wp_register_script(
		'wordpoints-admin-user-points'
		, "{$assets_url}/js/user-points{$suffix}.js"
		, array(
			'wordpoints-admin-utils',
			'jquery-ui-dialog',
			'jquery-effects-highlight',
			'wp-a11y',
		)
		, WORDPOINTS_VERSION
	);

	wp_localize_script(
		'wordpoints-admin-user-points'
		, 'WordPointsUserPointsTableL10n'
		, array(
			'addButtonText'      => __( 'Add Points', 'wordpoints' ),
			'subtractButtonText' => __( 'Subtract Points', 'wordpoints' ),
			'closeButtonText'    => __( 'Close', 'wordpoints' ),
			'cancelButtonText'   => __( 'Cancel', 'wordpoints' ),
			'waitMessage'        => __( 'Please wait&hellip;', 'wordpoints' ),
			'successMessage'     => __( 'Points updated successfully!', 'wordpoints' ),
			'errorMessage'       => __( 'Sorry, an unknown error occurred. Please try again.', 'wordpoints' ),
			'invalidInputText'   => __( 'Please enter a positive integer value.', 'wordpoints' ),
			'invalidInputTitle'  => __( 'Invalid input', 'wordpoints' ),
			'lessThanMinimum'    => sprintf(
				// translators: The minimum number of points.
				__( 'Users cannot have less than %s points.', 'wordpoints' )
				, '{{ data.minimum }}'
			),
		)
	);

	wp_script_add_data(
		'wordpoints-admin-user-points'
		, 'wordpoints-templates'
		, '
			<script type="text/template" id="tmpl-wordpoints-user-points-dialog">
				<div title="' . esc_attr__( 'Are you sure?', 'wordpoints' ) . '">
					<p>
						' . esc_html__( 'Please review the change you are about to make.', 'wordpoints' ) . '
					</p>
					<p class="wordpoints-points-user">
						<strong>
							' . esc_html__( 'User:', 'wordpoints' ) . '
						</strong>
					</p>
					<p class="wordpoints-points-total">
						<strong>
							' . esc_html__( 'Total:', 'wordpoints' ) . '
						</strong>
						{{ data.total }}
					</p>
					<p class="wordpoints-points-reason">
						' . esc_html__( 'If you would like to provide a reason for the change, you can add a message in the field below. This is optional.', 'wordpoints' ) . '
					</p>
					<p>
						<label>
							' . esc_html__( 'Reason:', 'wordpoints' ) . '
							<input type="text" />
						</label>
					</p>
				</div>
			</script>
		'
	);
}

/**
 * Add admin screens to the administration menu.
 *
 * @since 1.0.0
 *
 * @WordPress\action admin_menu
 * @WordPress\action network_admin_menu Only when the component is network-active.
 */
function wordpoints_points_admin_menu() {

	$wordpoints_menu = wordpoints_get_main_admin_menu();

	/** @var WordPoints_Admin_Screens $admin_screens */
	$admin_screens = wordpoints_apps()->get_sub_app( 'admin' )->get_sub_app(
		'screen'
	);

	// Hooks page.
	$id = add_submenu_page(
		$wordpoints_menu
		, __( 'WordPoints — Points Types', 'wordpoints' )
		, __( 'Points Types', 'wordpoints' )
		, 'manage_options'
		, 'wordpoints_points_types'
		, array( $admin_screens, 'display' )
	);

	if ( $id ) {
		$admin_screens->register( $id, 'WordPoints_Points_Admin_Screen_Points_Types' );
	}

	// Remove the old hooks screen if not needed.
	$disabled_hooks = wordpoints_get_maybe_network_array_option(
		'wordpoints_legacy_points_hooks_disabled'
		, is_network_admin()
	);

	$hooks = WordPoints_Points_Hooks::get_handlers();

	// If all of the registered hooks have been imported and disabled, then there is
	// no need to keep the old hooks screen.
	if ( array_diff_key( $hooks, $disabled_hooks ) ) {
		// Legacy hooks page.
		add_submenu_page(
			$wordpoints_menu
			, __( 'WordPoints — Points Hooks', 'wordpoints' )
			, __( 'Points Hooks', 'wordpoints' )
			, 'manage_options'
			, 'wordpoints_points_hooks'
			, 'wordpoints_points_admin_screen_hooks'
		);
	}

	// Logs page.
	add_submenu_page(
		$wordpoints_menu
		, __( 'WordPoints — Points Logs', 'wordpoints' )
		, __( 'Points Logs', 'wordpoints' )
		, 'manage_options'
		, 'wordpoints_points_logs'
		, 'wordpoints_points_admin_screen_logs'
	);

	// User Points screen.
	if ( ! is_network_admin() && current_user_can( 'list_users' ) ) {
		$id = add_submenu_page(
			$wordpoints_menu
			, __( 'WordPoints — User Points', 'wordpoints' )
			, __( 'User Points', 'wordpoints' )
			, 'set_wordpoints_points'
			, 'wordpoints_user_points'
			, array( $admin_screens, 'display' )
		);

		if ( $id ) {
			$admin_screens->register( $id, 'WordPoints_Points_Admin_Screen_User_Points' );
		}
	}
}

/**
 * Display the points hooks admin page.
 *
 * @since 1.0.0
 */
function wordpoints_points_admin_screen_hooks() {

	if ( isset( $_GET['edithook'] ) || isset( $_POST['savehook'] ) || isset( $_POST['removehook'] ) ) { // WPCS: CSRF OK.

		// - We're doing this without AJAX (JS).

		/**
		 * The non-JS version of the points hooks admin screen.
		 *
		 * @since 1.0.0
		 */
		require WORDPOINTS_DIR . 'components/points/admin/screens/hooks-no-js.php';

	} else {

		/**
		 * The points hooks admin screen.
		 *
		 * @since 1.0.0
		 */
		require WORDPOINTS_DIR . 'components/points/admin/screens/hooks.php';
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
	require WORDPOINTS_DIR . 'components/points/admin/screens/logs.php';
}

/**
 * Add help tabs to the points hooks page.
 *
 * @since 1.0.0
 *
 * @WordPress\action load-wordpoints_page_wordpoints_points_hooks
 */
function wordpoints_admin_points_hooks_help() {

	/**
	 * Add help tabs and enqueue scripts and styles for the hooks screen.
	 *
	 * @since 1.2.0
	 */
	require WORDPOINTS_DIR . 'components/points/admin/screens/hooks-load.php';
}

/**
 * Save points hooks from the non-JS form.
 *
 * @since 1.0.0
 *
 * @WordPress\action load-wordpoints_page_wordpoints_points_hooks
 */
function wordpoints_no_js_points_hooks_save() {

	if ( ! isset( $_POST['savehook'] ) && ! isset( $_POST['removehook'] ) ) { // WPCS: CSRF OK.
		return;
	}

	/**
	 * Save the hooks for non-JS/accessibility mode hooks screen.
	 *
	 * @since 1.2.0
	 */
	require WORDPOINTS_DIR . 'components/points/admin/screens/hooks-no-js-load.php';
}

/**
 * Add accessibility mode screen option to the points hooks page.
 *
 * @since 1.0.0
 *
 * @WordPress\action screen_settings
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
			// Fall through.

		case 'wordpoints_page_wordpoints_points_hooks-network':
			if ( ! isset( $url ) ) {
				$url = network_admin_url( $path );
			}

			$screen_options = '<p><a id="access-on" href="' . esc_url( wp_nonce_url( $url, 'wordpoints_points_hooks_accessiblity', 'wordpoints-accessiblity-nonce' ) ) . '&amp;accessibility-mode=on">'
				. esc_html__( 'Enable accessibility mode', 'wordpoints' )
				. '</a><a id="access-off" href="' . esc_url( wp_nonce_url( $url, 'wordpoints_points_hooks_accessiblity', 'wordpoints-accessiblity-nonce' ) ) . '&amp;accessibility-mode=off">'
				. esc_html__( 'Disable accessibility mode', 'wordpoints' ) . "</a></p>\n";
		break;
	}

	return $screen_options;
}

/**
 * Filter the class of the points hooks page for accessibility mode.
 *
 * @since 1.0.0
 *
 * @WordPress\filter admin_body_class Added when needed by wordpoints_admin_points_hooks_help()
 *
 * @param string $classes The body classes.
 *
 * @return string The classes, with 'wordpoints_hooks_access' added.
 */
function wordpoints_points_hooks_access_body_class( $classes ) {

	return "{$classes} wordpoints_hooks_access ";
}

/**
 * Display the hook description field in the hook forms.
 *
 * @since 1.4.0
 *
 * @WordPress\action wordpoints_in_points_hook_form
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
		<p class="description">
			<?php

			echo esc_html(
				sprintf(
					// translators: Default points hook description.
					_x( 'Default: %s', 'points hook description', 'wordpoints' )
					, $hook->get_description( 'generated' )
				)
			);

			?>
		</p>
	</div>

	<br />

	<?php
}

/**
 * Display the user's points on their profile page.
 *
 * @since 1.0.0
 * @deprecated 2.5.0 Feature moved to the Points Admin Profile Options extension.
 *
 * @param WP_User $user The user object for the user being edited.
 */
function wordpoints_points_profile_options( $user ) {
	_deprecated_function( __FUNCTION__, '2.5.0' );
}

/**
 * Save the user's points on profile edit.
 *
 * @since 1.0.0
 * @deprecated 2.5.0 Feature moved to the Points Admin Profile Options extension.
 *
 * @param int $user_id The ID of the user being edited.
 *
 * @return void
 */
function wordpoints_points_profile_options_update( $user_id ) {
	_deprecated_function( __FUNCTION__, '2.5.0' );
}

/**
 * Add settings to the top of the admin settings form.
 *
 * Currently only displays one setting: Default Points Type.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_admin_settings_top
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
					<?php wp_nonce_field( 'wordpoints_default_points_type', 'wordpoints_default_points_type_nonce' ); ?>
				</td>
			</tr>
		</tbody>
	</table>

	<?php
}

/**
 * Save settings on general settings panel.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_admin_settings_update
 */
function wordpoints_points_admin_settings_save() {

	if (
		isset( $_POST['default_points_type'] )
		&& wordpoints_verify_nonce( 'wordpoints_default_points_type_nonce', 'wordpoints_default_points_type', null, 'post' )
	) {

		$points_type = sanitize_key( $_POST['default_points_type'] );

		if ( '-1' === $points_type ) {

			wordpoints_update_maybe_network_option( 'wordpoints_default_points_type', '' );

		} elseif ( wordpoints_is_points_type( $points_type ) ) {

			wordpoints_update_maybe_network_option( 'wordpoints_default_points_type', $points_type );
		}
	}
}

/**
 * Display notices to the user on the administration panels.
 *
 * @since 1.9.0
 *
 * @WordPress\action admin_notices
 */
function wordpoints_points_admin_notices() {

	if (
		( ! isset( $_GET['page'] ) || 'wordpoints_points_types' !== $_GET['page'] ) // WPCS: CSRF OK.
		&& current_user_can( 'manage_wordpoints_points_types' )
		&& ! wordpoints_get_points_types()
	) {

		wordpoints_show_admin_message(
			sprintf(
				// translators: URL of Points Types admin screen.
				__( 'Welcome to WordPoints! Get started by <a href="%s">creating a points type</a>.', 'wordpoints' )
				, esc_url( self_admin_url( 'admin.php?page=wordpoints_points_types' ) )
			)
			, 'info'
		);
	}

	if (
		get_site_option( 'wordpoints_points_admin_profile_options_extension_offer' )
		&& (
			! isset( $_GET['page'] ) // WPCS: CSRF OK.
			|| (
				'wordpoints_extensions' !== $_GET['page'] // WPCS: CSRF OK.
				&& 'wordpoints_install_extensions' !== $_GET['page'] // WPCS: CSRF OK.
			)
		)
		&& current_user_can( 'install_wordpoints_extensions' )
	) {

		wordpoints_show_admin_message(
			sprintf(
				// translators: URL of Points Types admin screen.
				__( 'WordPoints 2.5.0 includes a new way to manually update user points, on the <a href="%1$s">User Points admin screen</a>. If you still want the points to be shown on the Profile admin screen, you can install the <a href="%2$s">Points Admin Profile Options extension</a>.', 'wordpoints' )
				, esc_url( self_admin_url( 'admin.php?page=wordpoints_points_types' ) )
				, 'https://wordpoints.org/extensions/points-admin-profile-options/'
			)
			, 'info'
			, array(
				'dismissible' => true,
				'option'      => 'wordpoints_points_admin_profile_options_extension_offer',
			)
		);
	}
}

/**
 * Sanitizes the screen options on save.
 *
 * @since 2.5.0
 *
 * @WordPress\filter set-screen-option
 *
 * @param mixed  $sanitized The sanitized option value, or false if not sanitized.
 * @param string $option    The option being saved.
 * @param mixed  $value     The raw value supplied by the user.
 *
 * @return mixed The option value, sanitized if it is for one of the plugin's screens.
 */
function wordpoints_points_admin_set_screen_option( $sanitized, $option, $value ) {

	switch ( $option ) {

		case 'wordpoints_page_wordpoints_user_points_per_page':
			$sanitized = wordpoints_posint( $value );
		break;
	}

	return $sanitized;
}

// EOF
