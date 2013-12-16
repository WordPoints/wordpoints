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
 * Add admin screens to the administration menu.
 *
 * @since 1.0.0
 *
 * @action admin_menu
 */
function wordpoints_points_admin_menu() {

	// Hooks page.
	add_submenu_page(
		'wordpoints_configure'
		,__( 'WordPoints - Points Hooks', 'wordpoints' )
		,__( 'Points Hooks', 'wordpoints' )
		,'manage_options'
		,'wordpoints_points_hooks'
		,'wordpoints_points_admin_screen_hooks'
	);

	// Logs page.
	add_submenu_page(
		'wordpoints_configure'
		,__( 'WordPoints - Points Logs', 'wordpoints' )
		,__( 'Points Logs', 'wordpoints' )
		,'manage_options'
		,'wordpoints_points_logs'
		,'wordpoints_points_admin_screen_logs'
	);
}
add_action( 'admin_menu', 'wordpoints_points_admin_menu' );

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

	global $wp_version;

	$screen = get_current_screen();

	$screen->add_help_tab(
		array(
			'id'      => 'overview',
			'title'   => __( 'Overview', 'wordpoints' ),
			'content' =>
				'<p>' . __( 'Points Hooks let you award users points by "hooking into" different actions. They can be hooked to any points type that you have created. To create a points type, fill out the Add New Points Type form, and click Save. You can edit the settings for your points type at any time by clicking on the Settings title bar within that points type section.', 'wordpoints' ) . '</p>
				<p>' . __( "To link a hook to a points type, click on the hook's title bar and select a points type, or drag and drop the hook title bars into the desired points type. By default, only the first points type area is expanded. To populate additional points types, click on their title bars to expand them.", 'wordpoints' ) . '</p>
				<p>' . __( 'The Available Hooks section contains all the hooks you can choose from. Once you add a hook into a points type, it will open to allow you to configure its settings. When you are happy with the hook settings, click the Save button and the hook will begin awarding points. If you click Delete, it will remove the hook.', 'wordpoints' ) . '</p>'
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'removing-reusing',
			'title'   => __( 'Removing and Reusing', 'wordpoints' ),
			'content' =>
				'<p>' . __( 'If you want to remove the hook but save its setting for possible future use, just drag it into the Inactive Hooks area. You can add them back anytime from there.', 'wordpoints' ) . '</p>
				<p>' . __( 'Hooks may be used multiple times.', 'wordpoints' ) . '</p>
				<p>' . __( 'Enabling Accessibility Mode, via Screen Options, allows you to use Add and Edit buttons instead of using drag and drop.', 'wordpoints' ) . '</p>'
		)
	);

	$accessibility_mode = get_user_setting( 'wordpoints_points_hooks_access' );

	if ( isset( $_GET['accessibility-mode'] ) ) {

		$accessibility_mode = ( 'on' == $_GET['accessibility-mode'] ) ? 'on' : 'off';
		set_user_setting( 'wordpoints_points_hooks_access', $accessibility_mode );
	}

	if ( 'on' == $accessibility_mode ) {

		add_filter( 'admin_body_class', 'wordpoints_points_hooks_access_body_class' );

	} else {

		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_enqueue_script(
			'wordpoints-admin-points-hooks'
			,plugins_url( 'assets/js/hooks.js', __FILE__ )
			,array( 'jquery', 'jquery-ui-droppable', 'jquery-ui-sortable' )
			,WORDPOINTS_VERSION
		);

		wp_localize_script(
			'wordpoints-admin-points-hooks'
			,'WordPointsHooksL10n'
			,array(
				'confirmDelete' => __( 'Are you sure that you want to delete this points type?', 'wordpoints' ) . "\n\n" . __( 'This will delete all related logs and hooks.', 'wordpoints' ) . "\n\n" . __( 'This operation cannot be undone.', 'wordpoints' ),
			)
		);

		if ( wp_is_mobile() )
			wp_enqueue_script( 'jquery-touch-punch' );
	}

	$deps = null;

	if ( version_compare( $wp_version, '3.8', '>=' ) ) {

		$deps = array( 'dashicons' );

	} else {

		wp_enqueue_style(
			'wordpoints-admin-points-hooks-legacy'
			, plugins_url( 'assets/css/hooks-legacy.css', __FILE__ )
			, array( 'wordpoints-admin-points-hooks' )
			, WORDPOINTS_VERSION
		);
	}

	wp_enqueue_style(
		'wordpoints-admin-points-hooks'
		, plugins_url( 'assets/css/hooks.css', __FILE__ )
		, $deps
		, WORDPOINTS_VERSION
	);
}
add_action( 'load-wordpoints_page_wordpoints_points_hooks', 'wordpoints_admin_points_hooks_help' );

/**
 * Save points hooks from the non-JS form.
 *
 * @since 1.0.0
 */
function wordpoints_no_js_points_hooks_save() {

	if ( ! isset( $_POST['savehook'] ) && ! isset( $_POST['removehook'] ) )
		return;

	$hook_id = $_POST['hook-id'];

	check_admin_referer( "save-delete-hook-{$hook_id}" );

	// These are the hooks grouped by points type.
	$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();

	if ( empty( $points_types_hooks ) )
		$points_types_hooks = WordPoints_Points_Hooks::get_defaults();

	$points_type_id = $_POST['points_type'];
	$id_base        = $_POST['id_base'];

	if ( isset( $points_types_hooks[ $points_type_id ] ) )
		$points_type_hooks = $points_types_hooks[ $points_type_id ];
	else
		$points_type_hooks = array();

	$hook = WordPoints_Points_Hooks::get_handler_by_id_base( $id_base );

	if ( isset( $_POST['removehook'] ) && $_POST['removehook'] ) {

		// - We are deleting an instance of a hook.

		if ( ! in_array( $hook_id, $points_type_hooks, true ) ) {

			// The hook isn't hooked to this points type, give an error.
			wp_redirect( admin_url( 'admin.php?page=wordpoints_points_hooks&error=0' ) );
			exit;
		}

		// Remove the hook from this points type.
		$points_types_hooks[ $points_type_id ] = array_diff( $points_type_hooks, array( $hook_id ) );

		$hook->delete_callback( $hook_id );

	} elseif ( isset( $_POST['savehook'] ) && $_POST['savehook'] ) {

		// - We are saving an instance of a hook.

		$number = isset( $_POST['multi_number'] ) ? (int) $_POST['multi_number'] : '';

		if ( $number ) {

			// Search the POST for the instance settings.
			foreach ( $_POST as $key => $val ) {

				if ( is_array( $val ) && preg_match( '/__i__|%i%/', key( $val ) ) ) {

					$new_instance = array_shift( $val );
					break;
				}
			}

		} else {

			if ( isset( $_POST[ 'hook-' . $id_base ] ) && is_array( $_POST[ 'hook-' . $id_base ] ) )
				$new_instance = reset( $_POST[ 'hook-' . $id_base ] );

			$number = $hook->get_number_by_id( $hook_id );
		}

		if ( ! isset( $new_instance ) || ! is_array( $new_instance ) ) {

			$new_instance = array();
		}

		// Update the hook.
		$hook->update_callback( $new_instance, $number );

		// Add hook it to this points type.
		if ( ! in_array( $hook_id, $points_type_hooks ) ) {

			$points_type_hooks[] = $hook_id;
			$points_types_hooks[ $points_type_id ] = $points_type_hooks;
		}

		// Remove from old points type if it has changed.
		$old_points_type = WordPoints_Points_Hooks::get_points_type( $hook_id );

		if ( $old_points_type && $old_points_type != $points_type_id && is_array( $points_types_hooks[ $old_points_type ] ) ) {

			$points_types_hooks[ $old_points_type ] = array_diff( $points_types_hooks[ $old_points_type ], array( $hook_id ) );
		}

	} else {

		wp_redirect( admin_url( 'admin.php?page=wordpoints_points_hooks&error=0' ) );
		exit;
	}

	update_option( 'wordpoints_points_types_hooks', $points_types_hooks );

	wp_redirect( admin_url( 'admin.php?page=wordpoints_points_hooks&message=0' ) );
	exit;
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

	if ( 'wordpoints_page_wordpoints_points_hooks' == $screen->id ) {

		$screen_options = '<p><a id="access-on" href="admin.php?page=wordpoints_points_hooks&amp;accessibility-mode=on">' . __( 'Enable accessibility mode', 'wordpoints' ) . '</a><a id="access-off" href="admin.php?page=wordpoints_points_hooks&amp;accessibility-mode=off">' . __( 'Disable accessibility mode', 'wordpoints' ) . "</a></p>\n";
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
					<input type="hidden" name="<?php echo esc_attr( "wordpoints_points_old-{$slug}" ); ?>" value="<?php echo $points; ?>" />
					<input type="text" name="<?php echo esc_attr( "wordpoints_points-{$slug}" ); ?>" value="<?php echo $points; ?>" />
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

	if ( ! current_user_can( 'set_wordpoints_points', $user_id ) )
		return;

	if ( ! isset( $_POST['wordpoints_points_set_nonce'], $_POST['wordpoints_set_reason'] ) || ! wp_verify_nonce( $_POST['wordpoints_points_set_nonce'], 'wordpoints_points_set_profile' ) )
		return;

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

			delete_option( 'wordpoints_default_points_type' );

		} elseif ( wordpoints_is_points_type( $_POST['default_points_type'] ) ) {

			update_option( 'wordpoints_default_points_type', $_POST['default_points_type'] );
		}
	}
}
add_action( 'wordpoints_admin_settings_update', 'wordpoints_points_admin_settings_save' );

/**
 * Save points hooks order via AJAX.
 *
 * @since 1.0.0
 *
 * @action wp_ajax_wordpoints-points-hooks-order
 */
function wordpoints_ajax_points_hooks_order() {

	check_ajax_referer( 'save-wordpoints-points-hooks', 'savehooks' );

	if ( ! current_user_can( 'manage_options' ) )
		wp_die( -1 );

	// Save hooks order for all points types.
	if ( is_array( $_POST['points_types'] ) ) {

		$points_types_hooks = array();

		foreach ( $_POST['points_types'] as $points_type => $hooks ) {

			$points_type_hooks = array();

			if ( ! empty( $hooks ) ) {

				$hooks = explode( ',', $hooks );

				foreach ( $hooks as $order => $hook_id ) {

					if ( strpos( $hook_id, 'hook-' ) === false )
						continue;

					$points_type_hooks[ $order ] = substr( $hook_id, strpos( $hook_id, '_' ) + 1 );
				}
			}

			$points_types_hooks[ $points_type ] = $points_type_hooks;
		}

		WordPoints_Points_Hooks::save_points_types_hooks( wp_unslash( $points_types_hooks ) );

		wp_die( 1 );
	}

	wp_die( -1 );
}
add_action( 'wp_ajax_wordpoints-points-hooks-order', 'wordpoints_ajax_points_hooks_order' );

/**
 * Save points hook settings via AJAX.
 *
 * @since 1.0.0
 *
 * @action wp_ajax_save-wordpoints-points-hook
 */
function wordpoints_ajax_save_points_hook() {

	check_ajax_referer( 'save-wordpoints-points-hooks', 'savehooks' );

	if ( ! current_user_can( 'manage_options' ) )
		wp_die( -1 );

	$error = '<p>' . __( 'An error has occurred. Please reload the page and try again.', 'wordpoints' ) . '</p>';

	if ( isset( $_POST['points-name'] ) ) {

		// - We are saving the settings for a points type.

		$settings = array();

		$settings['name']   = trim( $_POST['points-name'] );
		$settings['prefix'] = ltrim( $_POST['points-prefix'] );
		$settings['suffix'] = rtrim( $_POST['points-suffix'] );

		if ( ! wordpoints_update_points_type( $_POST['points-slug'], wp_unslash( $settings ) ) ) {

			// If this fails, show the user a message along with the form.
			echo '<p>' . __( 'An error has occurred. Please try again.', 'wordpoints' ) . '</p>';

			WordPoints_Points_Hooks::points_type_form( $slug, 'none' );
		}

	} else {

		// - We are creating/updating/deleting an instance of a hook.

		$id_base        = $_POST['id_base'];
		$hook_id        = $_POST['hook-id'];
		$points_type_id = $_POST['points_type'];
		$number         = (int) $_POST['hook_number'];

		$points_hooks = WordPoints_Points_Hooks::get_all();

		/*
		 * Normally the hook ID will be in 'hook-id' when we are updating a hook.
		 * But when we are saving a brand new instance of a hook or updating a newly
		 * created hook, the ID won't have been set when the form was output, so
		 * 'hook-id' will be empty, and we'll get the ID from 'multi_number'.
		 */
		if ( ! $number ) {

			// This holds the ID number if the hook is brand new.
			$number = (int) $_POST['multi_number'];

			if ( ! $number )
				wp_die( $error );

			$hook_id = $id_base . '-' . $number;
		}

		if ( isset( $points_hooks[ $hook_id ] ) )
			$hook = $points_hooks[ $hook_id ];

		$settings = ( isset( $_POST[ 'hook-' . $id_base ] ) && is_array( $_POST[ 'hook-' . $id_base ] ) ) ? $_POST[ 'hook-' . $id_base ] : false;

		$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();

		// Get the hooks for this points type.
		$points_type_hooks = ( isset( $points_types_hooks[ $points_type_id ] ) ) ? $points_types_hooks[ $points_type_id ] : array();

		if ( isset( $_POST['delete_hook'] ) && $_POST['delete_hook'] ) {

			// - We are deleting a hook instance.

			if ( ! isset( $hook ) )
				wp_die( $error );

			$hook->delete_callback( $number );

			// Remove this instance of the hook, and reset the positions (keys).
			$points_types_hooks[ $points_type_id ] = array_diff( $points_type_hooks, array( $hook_id ) );

			WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );

			echo "deleted:{$hook_id}";

			wp_die();

		} elseif ( $settings && ! isset( $hook ) ) {

			// - We are creating a new a new instance of a hook.

			/*
			 * Get a hook object for this type of hook. We have to do this because
			 * since the hook is new, it hasn't been assigned an ID yet, so we can't
			 * just get it from the array of hooks by ID.
			 */
			$hook = WordPoints_Points_Hooks::get_handler_by_id_base( $id_base );

			$new_instance = reset( $settings );

			// Save the points types-hooks associations.
			$points_type_hooks[] = $hook->get_id( $number );
			$points_types_hooks[ $points_type_id ] = $points_type_hooks;
			WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );

		} else {

			// - We are updating the settings for an instance of a hook.

			if ( ! isset( $hook ) )
				wp_die( $error );

			$new_instance = ( ! empty( $settings ) ) ? reset( $settings ) : array();
		}

		$hook->update_callback( wp_unslash( $new_instance ), $number );

		if ( empty( $_POST['add_new'] ) )
			$hook->form_callback( $number );

	} // if ( isset( $_POST['points-name'] ) ) {} else

	wp_die();
}
add_action( 'wp_ajax_save-wordpoints-points-hook', 'wordpoints_ajax_save_points_hook' );

// end of file /components/points/admin/admin.php
