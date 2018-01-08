<?php

/**
 * Admin-side functions.
 *
 * @package WordPoints\Admin
 * @since 2.1.0
 */

/**
 * Register the admin apps when the main app is initialized.
 *
 * @since 2.1.0
 *
 * @WordPress\action wordpoints_init_app-apps
 *
 * @param WordPoints_App $app The main WordPoints app.
 */
function wordpoints_hooks_register_admin_apps( $app ) {

	$apps = $app->sub_apps();

	$apps->register( 'admin', 'WordPoints_App' );

	/** @var WordPoints_App $admin */
	$admin = $apps->get( 'admin' );

	$admin->sub_apps()->register( 'screen', 'WordPoints_Admin_Screens' );
}

/**
 * Get the slug of the main administration menu item for the plugin.
 *
 * The main item changes in multisite when the plugin is network activated. In the
 * network admin it is the usual 'wordpoints_configure', while everywhere else it is
 * 'wordpoints_extensions' instead.
 *
 * @since 1.2.0
 *
 * @return string The slug for the plugin's main top level admin menu item.
 */
function wordpoints_get_main_admin_menu() {

	$slug = 'wordpoints_configure';

	/*
	 * If the plugin is network active and we are displaying the regular admin menu,
	 * the modules screen should be the main one (the configure menu is only for the
	 * network admin when network active).
	 */
	if ( is_wordpoints_network_active() && 'admin_menu' === current_filter() ) {
		$slug = 'wordpoints_extensions';
	}

	return $slug;
}

/**
 * Add admin screens to the administration menu.
 *
 * @since 1.0.0
 *
 * @WordPress\action admin_menu
 * @WordPress\action network_admin_menu
 */
function wordpoints_admin_menu() {

	$main_menu  = wordpoints_get_main_admin_menu();
	$wordpoints = __( 'WordPoints', 'wordpoints' );

	/*
	 * The settings page is always the main menu, except when the plugin is network
	 * active on multisite. Then it is only the main menu when in the network admin.
	 */
	if ( 'wordpoints_configure' === $main_menu ) {

		// Main page.
		add_menu_page(
			$wordpoints
			, esc_html( $wordpoints )
			, 'manage_options'
			, 'wordpoints_configure'
			, 'wordpoints_admin_screen_configure'
		);

		// Settings page.
		add_submenu_page(
			'wordpoints_configure'
			, __( 'WordPoints — Settings', 'wordpoints' )
			, esc_html__( 'Settings', 'wordpoints' )
			, 'manage_options'
			, 'wordpoints_configure'
			, 'wordpoints_admin_screen_configure'
		);

	} else {

		/*
		 * When network-active and displaying the admin menu, we don't display the
		 * settings page, instead we display the modules page as the main page.
		 */

		// Main page.
		add_menu_page(
			$wordpoints
			, esc_html( $wordpoints )
			, 'activate_wordpoints_extensions'
			, 'wordpoints_extensions'
			, 'wordpoints_admin_screen_modules'
		);

	} // End if ( configure is main menu ) else.

	// Extensions page.
	add_submenu_page(
		$main_menu
		, __( 'WordPoints — Extensions', 'wordpoints' )
		, esc_html__( 'Extensions', 'wordpoints' )
		, 'activate_wordpoints_extensions'
		, 'wordpoints_extensions'
		, 'wordpoints_admin_screen_modules'
	);

	// Back-compat for extensions page when the slug was "modules".
	add_submenu_page(
		$main_menu
		, __( 'WordPoints — Extensions', 'wordpoints' )
		, esc_html__( 'Extensions', 'wordpoints' )
		, 'activate_wordpoints_extensions'
		, 'wordpoints_modules'
		, 'wordpoints_admin_screen_modules'
	);

	// Extensions install page.
	add_submenu_page(
		$main_menu
		, __( 'WordPoints — Install Extensions', 'wordpoints' )
		, esc_html__( 'Install Extensions', 'wordpoints' )
		, 'install_wordpoints_extensions'
		, 'wordpoints_install_extensions'
		, 'wordpoints_admin_screen_install_modules'
	);

	// Back-compat for extensions install page when the slug was "modules".
	add_submenu_page(
		$main_menu
		, __( 'WordPoints — Install Extensions', 'wordpoints' )
		, esc_html__( 'Install Extensions', 'wordpoints' )
		, 'install_wordpoints_extensions'
		, 'wordpoints_install_modules'
		, 'wordpoints_admin_screen_install_modules'
	);
}

/**
 * Corrects the display of "hidden" submenu items.
 *
 * @since 2.5.0
 *
 * @WordPress\filter submenu_file
 *
 * @param string $submenu_file The submenu file/slug.
 *
 * @return string The filtered submenu file/slug.
 */
function wordpoints_admin_submenu_filter( $submenu_file ) {

	global $plugin_page;

	$hidden_submenus = array(
		'wordpoints_install_extensions' => true,
		'wordpoints_install_modules'    => true,
		'wordpoints_modules'            => true,
	);

	if ( $plugin_page && isset( $hidden_submenus[ $plugin_page ] ) ) {
		$submenu_file = 'wordpoints_extensions';
	}

	$menu_slug = wordpoints_get_main_admin_menu();

	foreach ( $hidden_submenus as $submenu => $unused ) {
		remove_submenu_page( $menu_slug, $submenu );
	}

	return $submenu_file;
}

/**
 * Display the modules admin screen.
 *
 * @since 1.2.0
 */
function wordpoints_admin_screen_modules() {

	/**
	 * The modules administration screen.
	 *
	 * @since 1.1.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/modules.php';
}

/**
 * Set up for the modules screen.
 *
 * @since 1.1.0
 *
 * @WordPress\action load-wordpoints_page_wordpoints_extensions
 * @WordPress\action load-toplevel_page_wordpoints_extensions
 */
function wordpoints_admin_screen_modules_load() {

	/**
	 * Set up for the modules page.
	 *
	 * @since 1.1.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/modules-load.php';
}

/**
 * Display the install modules admin screen.
 *
 * @since 1.1.0
 */
function wordpoints_admin_screen_install_modules() {

	/**
	 * The WordPoints > Install Modules admin panel.
	 *
	 * @since 1.1.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/module-install.php';
}

/**
 * Set up for the configure screen.
 *
 * @since 1.5.0 As wordpoints_admin_sreen_configure_load().
 * @since 2.3.0
 *
 * @WordPress\action load-toplevel_page_wordpoints_configure
 */
function wordpoints_admin_screen_configure_load() {

	/**
	 * Set up for the WordPoints » Settings administration screen.
	 *
	 * @since 1.5.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/configure-settings-load.php';
}

/**
 * Set up for the configure screen.
 *
 * @since 1.5.0
 * @deprecated 2.3.0 Use wordpoints_admin_screen_configure_load() instead.
 */
function wordpoints_admin_sreen_configure_load() {

	_deprecated_function(
		__FUNCTION__
		, '2.3.0'
		, 'wordpoints_admin_screen_configure_load()'
	);

	wordpoints_admin_screen_configure_load();
}

/**
 * Activate/deactivate components.
 *
 * This function handles activation and deactivation of components from the
 * WordPoints » Settings » Components administration screen.
 *
 * @since 1.0.1
 *
 * @WordPress\action load-toplevel_page_wordpoints_configure
 */
function wordpoints_admin_activate_components() {

	/**
	 * Set up for the WordPoints > Components administration screen.
	 *
	 * @since 1.1.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/configure-components-load.php';
}

/**
 * Display an error message.
 *
 * @since 1.0.0
 * @since 1.8.0 The $args parameter was added.
 *
 * @uses wordpoints_show_admin_message()
 *
 * @param string $message The text for the error message.
 * @param array  $args    Other optional arguments.
 */
function wordpoints_show_admin_error( $message, array $args = array() ) {

	wordpoints_show_admin_message( $message, 'error', $args );
}

/**
 * Display an update message.
 *
 * You should use {@see wordpoints_show_admin_error()} instead for showing error
 * messages. Currently there aren't wrappers for the other types, as they aren't used
 * in WordPoints core.
 *
 * @since 1.0.0
 * @since 1.2.0  The $type parameter is now properly escaped.
 * @since 1.8.0  The $message will be passed through wp_kses().
 * @since 1.8.0  The $args parameter was added with "dismissable" and "option" args.
 * @since 1.10.0 The "dismissable" arg was renamed to "dismissible".
 * @since 2.1.0  Now supports 'warning' and 'info' message types, and 'updated' is
 *               deprecated in favor of 'success'.
 *
 * @param string $message The text for the message.
 * @param string $type    The type of message to display, 'success' (default),
 *                        'error', 'warning' or 'info'.
 * @param array  $args    {
 *        Other optional arguments.
 *
 *        @type bool   $dismissible Whether this notice can be dismissed. Default is
 *                                  false (not dismissible).
 *        @type string $option      An option to delete when if message is dismissed.
 *                                  Required when $dismissible is true.
 * }
 */
function wordpoints_show_admin_message( $message, $type = 'success', array $args = array() ) {

	$defaults = array(
		'dismissible' => false,
		'option'      => '',
	);

	$args = array_merge( $defaults, $args );

	if ( isset( $args['dismissable'] ) ) {

		$args['dismissible'] = $args['dismissable'];

		_deprecated_argument(
			__FUNCTION__
			, '1.10.0'
			, 'The "dismissable" argument has been replaced by the correct spelling, "dismissible".'
		);
	}

	if ( 'updated' === $type ) {

		$type = 'success';

		_deprecated_argument(
			__FUNCTION__
			, '2.1.0'
			, 'Use "success" instead of "updated" for the $type argument.'
		);
	}

	if ( $args['dismissible'] && $args['option'] ) {
		wp_enqueue_style( 'wordpoints-admin-general' );
		wp_enqueue_script( 'wordpoints-admin-dismiss-notice' );
	}

	?>

	<div
		class="notice notice-<?php echo sanitize_html_class( $type, 'success' ); ?><?php echo ( $args['dismissible'] ) ? ' is-dismissible' : ''; ?>"
		<?php if ( $args['dismissible'] && $args['option'] ) : ?>
			data-nonce="<?php echo esc_attr( wp_create_nonce( "wordpoints_dismiss_notice-{$args['option']}" ) ); ?>"
			data-option="<?php echo esc_attr( $args['option'] ); ?>"
		<?php endif; ?>
		>
		<p>
			<?php echo wp_kses( $message, 'wordpoints_admin_message' ); ?>
		</p>
		<?php if ( $args['dismissible'] && $args['option'] ) : ?>
			<form method="post" class="wordpoints-notice-dismiss-form">
				<input type="hidden" name="wordpoints_notice" value="<?php echo esc_html( $args['option'] ); ?>" />
				<?php wp_nonce_field( "wordpoints_dismiss_notice-{$args['option']}" ); ?>
				<?php submit_button( __( 'Hide This Notice', 'wordpoints' ), '', 'wordpoints_dismiss_notice', false ); ?>
			</form>
		<?php endif; ?>
	</div>

	<?php
}

/**
 * Get the current tab.
 *
 * @since 1.0.0
 *
 * @param array $tabs The tabs. If passed, the first key will be returned if
 *        $_GET['tab'] is not set, or not one of the values in $tabs.
 *
 * @return string
 */
function wordpoints_admin_get_current_tab( array $tabs = null ) {

	$tab = '';

	if ( isset( $_GET['tab'] ) ) { // WPCS: CSRF OK.

		$tab = sanitize_key( $_GET['tab'] ); // WPCS: CSRF OK.
	}

	if ( isset( $tabs ) && ! isset( $tabs[ $tab ] ) ) {

		reset( $tabs );
		$tab = key( $tabs );
	}

	return $tab;
}

/**
 * Display a set of tabs.
 *
 * @since 1.0.0
 *
 * @uses wordpoints_admin_get_current_tab()
 *
 * @param string[] $tabs         The tabs. Keys are slugs, values displayed text.
 * @param bool     $show_heading Whether to show an <h1> element using the current
 *                               tab. Default is true.
 */
function wordpoints_admin_show_tabs( $tabs, $show_heading = true ) {

	$current = wordpoints_admin_get_current_tab( $tabs );

	if ( $show_heading ) {

		// translators: Current tab name.
		echo '<h1>', esc_html( sprintf( __( 'WordPoints — %s', 'wordpoints' ), $tabs[ $current ] ) ), '</h1>';
	}

	echo '<h2 class="nav-tab-wrapper">';

	$page = '';

	if ( isset( $_GET['page'] ) ) { // WPCS: CSRF OK.
		$page = sanitize_key( $_GET['page'] ); // WPCS: CSRF OK.
	}

	foreach ( $tabs as $tab => $name ) {

		$class = ( $tab === $current ) ? ' nav-tab-active' : '';

		echo '<a class="nav-tab ', sanitize_html_class( $class ), '" href="?page=', rawurlencode( $page ), '&amp;tab=', rawurlencode( $tab ), '">', esc_html( $name ), '</a>';
	}

	echo '</h2>';
}

/**
 * Add a sidebar to the general settings page.
 *
 * @since 1.1.0
 *
 * @WordPress\action wordpoints_admin_settings_bottom 5 Before other items are added.
 */
function wordpoints_admin_settings_screen_sidebar() {

	wp_enqueue_style( 'wordpoints-admin-general' );

	?>

	<div class="notice notice-info inline wordpoints-settings-help-notice">
		<div>
			<h3><?php esc_html_e( 'Like this plugin?', 'wordpoints' ); ?></h3>
			<p>
				<?php

				echo wp_kses(
					sprintf(
						// translators: URL for leaving a review of WordPoints on WordPress.org.
						__( 'If you think WordPoints is great, let everyone know by giving it a <a href="%s">5 star rating</a>.', 'wordpoints' )
						, 'https://wordpress.org/support/view/plugin-reviews/wordpoints?rate=5#postform'
					)
					, array( 'a' => array( 'href' => true ) )
				);

				?>
			</p>
			<p><?php esc_html_e( 'If you don&#8217;t think this plugin deserves 5 stars, please let us know how we can improve it.', 'wordpoints' ); ?></p>
		</div>
		<div>
			<h3><?php esc_html_e( 'Need help?', 'wordpoints' ); ?></h3>
			<p>
				<?php

				echo wp_kses(
					sprintf(
						// translators: URL of WordPoints plugin support forums WordPress.org.
						__( 'Post your feature request or support question in the <a href="%s">support forums</a>', 'wordpoints' )
						, 'https://wordpress.org/support/plugin/wordpoints'
					)
					, array( 'a' => array( 'href' => true ) )
				);

				?>
			</p>
			<p><em><?php esc_html_e( 'Thank you for using WordPoints!', 'wordpoints' ); ?></em></p>
		</div>
	</div>

	<?php
}

/**
 * Display notices to the user on the administration panels.
 *
 * @since 1.8.0
 *
 * @WordPress\action admin_notices
 */
function wordpoints_admin_notices() {

	wordpoints_delete_admin_notice_option();

	if ( current_user_can( 'activate_wordpoints_extensions' ) ) {

		if ( is_network_admin() ) {

			$deactivated_modules = get_site_option( 'wordpoints_breaking_deactivated_modules' );

			if ( is_array( $deactivated_modules ) ) {
				wordpoints_show_admin_error(
					sprintf(
						// translators: 1. Plugin version; 2. List of extensions.
						__( 'WordPoints has deactivated the following extensions because of incompatibilities with WordPoints %1$s: %2$s', 'wordpoints' )
						, WORDPOINTS_VERSION
						, implode( ', ', $deactivated_modules )
					)
					, array(
						'dismissible' => true,
						'option'      => 'wordpoints_breaking_deactivated_modules',
					)
				);
			}

			$incompatible_modules = get_site_option( 'wordpoints_incompatible_modules' );

			if ( is_array( $incompatible_modules ) ) {
				wordpoints_show_admin_error(
					sprintf(
						// translators: 1. Plugin version; 2. List of extensions.
						__( 'WordPoints has deactivated the following network-active extensions because of incompatibilities with WordPoints %1$s: %2$s', 'wordpoints' )
						, WORDPOINTS_VERSION
						, implode( ', ', $incompatible_modules )
					)
					, array(
						'dismissible' => true,
						'option'      => 'wordpoints_incompatible_modules',
					)
				);
			}

		} else {

			$incompatible_modules = get_option( 'wordpoints_incompatible_modules' );

			if ( is_array( $incompatible_modules ) ) {
				wordpoints_show_admin_error(
					sprintf(
						// translators: 1. Plugin version; 2. List of extensions.
						__( 'WordPoints has deactivated the following extensions on this site because of incompatibilities with WordPoints %1$s: %2$s', 'wordpoints' )
						, WORDPOINTS_VERSION
						, implode( ', ', $incompatible_modules )
					)
					, array(
						'dismissible' => true,
						'option'      => 'wordpoints_incompatible_modules',
					)
				);
			}

		} // End if ( is_network_admin() ) else.

	} // End if ( user can activate modules ).

	if (
		current_user_can( 'delete_wordpoints_extensions' )
		&& (
			! isset( $_REQUEST['action'] ) // WPCS: CSRF OK.
			|| 'delete-selected' !== $_REQUEST['action'] // WPCS: CSRF OK.
		)
	) {

		$merged_extensions = get_site_option( 'wordpoints_merged_extensions' );

		if ( is_array( $merged_extensions ) && ! empty( $merged_extensions ) ) {

			foreach ( $merged_extensions as $i => $extension ) {
				if ( true !== wordpoints_validate_module( $extension ) ) {
					unset( $merged_extensions[ $i ] );
				}
			}

			update_site_option( 'wordpoints_merged_extensions', $merged_extensions );

			if ( ! empty( $merged_extensions ) ) {

				$message = sprintf(
					// translators: 1. Plugin version; 2. List of extensions.
					__( 'WordPoints has deactivated the following extensions because their features have now been merged into WordPoints %1$s: %2$s.', 'wordpoints' )
					, WORDPOINTS_VERSION
					, implode( ', ', $merged_extensions )
				);

				$message .= ' ';
				$message .= __( 'You can now safely delete these extensions.', 'wordpoints' );
				$message .= ' ';

				$url = admin_url(
					'admin.php?page=wordpoints_extensions&action=delete-selected'
				);

				foreach ( $merged_extensions as $extension ) {
					$url .= '&checked[]=' . rawurlencode( $extension );
				}

				$url = wp_nonce_url( $url, 'bulk-modules' );

				$message .= '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Delete Unneeded Extensions', 'wordpoints' ) . '</a>';

				wordpoints_show_admin_message(
					$message
					, 'warning'
					, array(
						'dismissible' => true,
						'option'      => 'wordpoints_merged_extensions',
					)
				);
			}

		} // End if ( merged extensions ).

	} // End if ( user can delete and aren't deleting ).

	if ( is_wordpoints_network_active() ) {
		wordpoints_admin_show_update_skipped_notices( 'install' );
		wordpoints_admin_show_update_skipped_notices( 'update' );
	}
}

/**
 * Handle a request to delete an option tied to an admin notice.
 *
 * @since 2.1.0
 *
 * @WordPress\action wp_ajax_wordpoints-delete-admin-notice-option
 */
function wordpoints_delete_admin_notice_option() {

	// Check if any notices have been dismissed.
	$is_notice_dismissed = wordpoints_verify_nonce(
		'_wpnonce'
		, 'wordpoints_dismiss_notice-%s'
		, array( 'wordpoints_notice' )
		, 'post'
	);

	if ( $is_notice_dismissed && isset( $_POST['wordpoints_notice'] ) ) {

		$option = sanitize_key( $_POST['wordpoints_notice'] );

		if ( ! is_network_admin() && 'wordpoints_incompatible_modules' === $option ) {
			delete_option( $option );
		} else {
			wordpoints_delete_maybe_network_option( $option );
		}
	}

	if ( wp_doing_ajax() ) {
		wp_die( '', 200 );
	}
}

/**
 * Save the screen options.
 *
 * @since 2.0.0
 *
 * @WordPress\filter set-screen-option
 *
 * @param mixed  $sanitized The sanitized option value, or false if not sanitized.
 * @param string $option    The option being saved.
 * @param mixed  $value     The raw value supplied by the user.
 *
 * @return mixed The option value, sanitized if it is for one of the plugin's screens.
 */
function wordpoints_admin_set_screen_option( $sanitized, $option, $value ) {

	switch ( $option ) {

		case 'wordpoints_page_wordpoints_extensions_per_page':
		case 'wordpoints_page_wordpoints_extensions_network_per_page':
		case 'toplevel_page_wordpoints_extensions_per_page':
			$sanitized = wordpoints_posint( $value );
			break;
	}

	return $sanitized;
}

/**
 * Ajax callback to load the modules admin screen when running module compat checks.
 *
 * We run this Ajax action to check module compatibility before loading modules
 * after WordPoints is updated to a new major version. This avoids breaking the site
 * if some modules aren't compatible with the backward-incompatible changes that are
 * present in a major version.
 *
 * @since 2.0.0
 *
 * @WordPress\action wp_ajax_nopriv_wordpoints_breaking_module_check
 */
function wordpoints_admin_ajax_breaking_module_check() {

	if ( ! isset( $_GET['wordpoints_module_check'] ) ) { // WPCS: CSRF OK.
		wp_die( '', 400 );
	}

	if ( is_network_admin() ) {
		$nonce = get_site_option( 'wordpoints_module_check_nonce' );
	} else {
		$nonce = get_option( 'wordpoints_module_check_nonce' );
	}

	if ( ! $nonce || ! hash_equals( $nonce, sanitize_key( $_GET['wordpoints_module_check'] ) ) ) { // WPCS: CSRF OK.
		wp_die( '', 403 );
	}

	// The list table constructor calls WP_Screen::get(), which expects this.
	$GLOBALS['hook_suffix'] = null;

	wordpoints_admin_screen_modules();

	wp_die( '', 200 );
}

/**
 * Initialize the Ajax actions.
 *
 * @since 2.1.0
 *
 * @WordPress\action admin_init
 */
function wordpoints_hooks_admin_ajax() {

	if ( wp_doing_ajax() ) {
		new WordPoints_Admin_Ajax_Hooks();
	}
}

/**
 * Shows the admin a notice if the update/install for an installable was skipped.
 *
 * @since 2.4.0
 *
 * @param string $notice_type The type of notices to display, 'update', or 'install'.
 */
function wordpoints_admin_show_update_skipped_notices( $notice_type = 'update' ) {

	$all_skipped = array_filter(
		wordpoints_get_array_option( "wordpoints_network_{$notice_type}_skipped", 'site' )
	);

	if ( empty( $all_skipped ) ) {
		return;
	}

	$messages = array();

	if ( 'install' === $notice_type ) {
		// translators: 1. Extension/plugin name; 2. "extension", "plugin", or "component".
		$message_template = __( 'WordPoints detected a large network and has skipped part of the installation process for &#8220;%1$s&#8221; %2$s.', 'wordpoints' );
	} else {
		// translators: 1. Extension/plugin name; 2. "extension", "plugin", or "component"; 3. Version number.
		$message_template = __( 'WordPoints detected a large network and has skipped part of the update process for &#8220;%1$s&#8221; %2$s for version %3$s (and possibly later versions).', 'wordpoints' );
	}

	foreach ( $all_skipped as $type => $skipped ) {

		switch ( $type ) {

			case 'module':
				$capability = 'wordpoints_manage_network_modules';
			break;

			default:
				$capability = 'manage_network_plugins';
		}

		if ( ! current_user_can( $capability ) ) {
			continue;
		}

		switch ( $type ) {

			case 'module':
				$type_name = __( '(extension)', 'wordpoints' );
			break;

			case 'component':
				$type_name = __( '(component)', 'wordpoints' );
			break;

			default:
				$type_name = __( '(plugin)', 'wordpoints' );
		}

		foreach ( $skipped as $slug => $version ) {

			// Normally we might have used the installable's fancy name instead
			// of the slug, but this is such an edge case to start with that I
			// decided not to. Also of note: the version is only used in the
			// update message.
			$messages[] = esc_html(
				sprintf(
					$message_template
					, $slug
					, $type_name
					, $version
				)
			);
		}

	} // End foreach ( $all_skipped ).

	if ( ! empty( $messages ) ) {

		$message  = '<p>' . implode( '</p><p>', $messages ) . '</p>';
		$message .= '<p>' . esc_html__( 'The rest of the process needs to be completed manually. If this has not been done already, some features may not function properly.', 'wordpoints' );
		$message .= ' <a href="https://wordpoints.org/user-guide/multisite/">' . esc_html__( 'Learn more.', 'wordpoints' ) . '</a></p>';

		$args = array(
			'dismissible' => true,
			'option'      => "wordpoints_network_{$notice_type}_skipped",
		);

		wordpoints_show_admin_error( $message, $args );
	}
}

// EOF
