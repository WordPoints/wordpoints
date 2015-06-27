<?php

/**
 * Administration-side functions.
 *
 * This and the included files are run on the admin side only. They create all of
 * the main administration screens, enqueue scripts and styles where needed, etc.
 *
 * Note that each component has its own administration package also.
 *
 * @package WordPoints\Administration
 * @since 1.0.0
 */

/**
 * Screen: Configuration.
 *
 * @since 1.0.0
 */
include_once WORDPOINTS_DIR . 'admin/screens/configure.php';

/**
 * Get the slug of the main administration menu item for the plugin.
 *
 * The main item changes in multisite when the plugin is network activated. In the
 * network admin it is the usual 'wordpoints_configure', while everywhere else it is
 * 'wordpoints_modules' instead.
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
		$slug = 'wordpoints_modules';
	}

	return $slug;
}

/**
 * Add admin screens to the administration menu.
 *
 * @since 1.0.0
 *
 * @action admin_menu
 * @action network_admin_menu
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
			,esc_html( $wordpoints )
			,'manage_options'
			,'wordpoints_configure'
			,'wordpoints_admin_screen_configure'
		);

		// Settings page.
		add_submenu_page(
			'wordpoints_configure'
			,__( 'WordPoints — Configure', 'wordpoints' )
			,esc_html__( 'Configure', 'wordpoints' )
			,'manage_options'
			,'wordpoints_configure'
			,'wordpoints_admin_screen_configure'
		);

	} else {

		/*
		 * When network-active and displaying the admin menu, we don't display the
		 * settings page, instead we display the modules page as the main page.
		 */

		// Main page.
		add_menu_page(
			$wordpoints
			,esc_html( $wordpoints )
			,'activate_wordpoints_modules'
			,'wordpoints_modules'
			,'wordpoints_admin_screen_modules'
		);
	}

	// Modules page.
	add_submenu_page(
		$main_menu
		,__( 'WordPoints — Modules', 'wordpoints' )
		,esc_html__( 'Modules', 'wordpoints' )
		,'activate_wordpoints_modules'
		,'wordpoints_modules'
		,'wordpoints_admin_screen_modules'
	);

	// Module install page.
	add_submenu_page(
		'_wordpoints_modules' // Fake menu.
		,__( 'WordPoints — Install Modules', 'wordpoints' )
		,esc_html__( 'Install Modules', 'wordpoints' )
		,'install_wordpoints_modules'
		,'wordpoints_install_modules'
		,'wordpoints_admin_screen_install_modules'
	);
}
add_action( 'admin_menu', 'wordpoints_admin_menu' );
add_action( 'network_admin_menu', 'wordpoints_admin_menu' );

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
 * @action load-wordpoints_page_wordpoints_modules
 * @action load-toplevel_page_wordpoints_modules
 */
function wordpoints_admin_screen_modules_load() {

	/**
	 * Set up for the modules page.
	 *
	 * @since 1.1.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/modules-load.php';
}
add_action( 'load-wordpoints_page_wordpoints_modules', 'wordpoints_admin_screen_modules_load' );
add_action( 'load-toplevel_page_wordpoints_modules', 'wordpoints_admin_screen_modules_load' );

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
 * @since 1.5.0
 *
 * @action load-toplevel_page_wordpoints_configure
 */
function wordpoints_admin_sreen_configure_load() {

	/**
	 * Set up for the WordPoints » Configure administration screen.
	 *
	 * @since 1.5.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/configure-settings-load.php';
}
add_action( 'load-toplevel_page_wordpoints_configure', 'wordpoints_admin_sreen_configure_load' );

/**
 * Activate/deactivate components.
 *
 * This function handles activation and deactivation of components from the
 * WordPoints > Configure > Components administration screen.
 *
 * @since 1.0.1
 *
 * @action load-toplevel_page_wordpoints_configure
 */
function wordpoints_admin_activate_components() {

	/**
	 * Set up for the WordPoints > Components adminstration screen.
	 *
	 * @since 1.1.0
	 */
	require WORDPOINTS_DIR . 'admin/screens/configure-components-load.php';
}
add_action( 'load-toplevel_page_wordpoints_configure', 'wordpoints_admin_activate_components' );

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
 * @since 1.2.0 The $type parameter is now properly escaped.
 * @since 1.8.0 The $message will be passed through wp_kses().
 * @since 1.8.0 The $args parameter was added with "dismissable" and "option" args.
 * @since 1.10.0 The "dismissable" arg was renamed to "dismissible".
 *
 * @param string $message The text for the message.
 * @param string $type    The type of message to display. Default is 'updated'.
 * @param array  $args    {
 *        Other optional arguments.
 *
 *        @type bool   $dismissible Whether this notice can be dismissed. Default is
 *                                  false (not dismissible).
 *        @type string $option      An option to delete when if message is dismissed.
 *                                  Required when $dismissible is true.
 * }
 */
function wordpoints_show_admin_message( $message, $type = 'updated', array $args = array() ) {

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

	?>

	<div id="message" class="<?php echo sanitize_html_class( $type, 'updated' ); ?>">
		<p>
			<?php echo wp_kses( $message, 'wordpoints_admin_message' ); ?>
		</p>
		<?php if ( $args['dismissible'] ) : ?>
			<form method="post" style="padding-bottom: 5px;">
				<input type="hidden" name="wordpoints_notice" value="<?php echo esc_html( $args['option'] ); ?>" />
				<?php wp_nonce_field( "wordpoints_dismiss_notice-{$args['option']}" ); ?>
				<?php submit_button( __( 'Hide This Notice', 'wordpoints' ), 'secondary', 'wordpoints_dismiss_notice', false ); ?>
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

	if ( isset( $_GET['tab'] ) ) {

		$tab = sanitize_key( $_GET['tab'] );
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
 * @param bool     $show_heading Whether to show an <h2> element using the current
 *        tab. Default is true.
 */
function wordpoints_admin_show_tabs( $tabs, $show_heading = true ) {

	$current = wordpoints_admin_get_current_tab( $tabs );

	if ( $show_heading ) {

		echo '<h2>', esc_html( sprintf( __( 'WordPoints — %s', 'wordpoints' ), $tabs[ $current ] ) ), '</h2>';
	}

	echo '<h2 class="nav-tab-wrapper">';

	$page = '';

	if ( isset( $_GET['page'] ) ) {
		$page = sanitize_key( $_GET['page'] );
	}

	foreach ( $tabs as $tab => $name ) {

		$class = ( $tab === $current ) ? ' nav-tab-active' : '';

		echo '<a class="nav-tab ', sanitize_html_class( $class ), '" href="?page=', rawurlencode( $page ), '&amp;tab=', rawurlencode( $tab ), '">', esc_html( $name ), '</a>';
	}

	echo '</h2>';
}

/**
 * Display the upload module from zip form.
 *
 * @since 1.1.0
 */
function wordpoints_install_modules_upload() {

	?>

	<h4><?php esc_html_e( 'Install a module in .zip format', 'wordpoints' ); ?></h4>
	<p class="install-help"><?php esc_html_e( 'If you have a module in a .zip format, you may install it by uploading it here.', 'wordpoints' ); ?></p>
	<form method="post" enctype="multipart/form-data" class="wp-upload-form" action="<?php echo esc_attr( esc_url( self_admin_url( 'update.php?action=upload-wordpoints-module' ) ) ); ?>">
		<?php wp_nonce_field( 'wordpoints-module-upload' ); ?>
		<label class="screen-reader-text" for="modulezip"><?php esc_html_e( 'Module zip file', 'wordpoints' ); ?></label>
		<input type="file" id="modulezip" name="modulezip" />
		<?php submit_button( __( 'Install Now', 'wordpoints' ), 'button', 'install-module-submit', false ); ?>
	</form>

	<?php
}
add_action( 'wordpoints_install_modules-upload', 'wordpoints_install_modules_upload', 10 );

/**
 * Perfom module upload from .zip file.
 *
 * @since 1.1.0
 *
 * @action update-custom_upload-wordpoints-module
 */
function wordpoints_upload_module_zip() {

	if ( ! current_user_can( 'install_wordpoints_modules' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to install WordPoints modules on this site.', 'wordpoints' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( 'wordpoints-module-upload' );

	$file_upload = new File_Upload_Upgrader( 'modulezip', 'package' );

	$title = esc_html__( 'Upload WordPoints Module', 'wordpoints' );
	$parent_file  = 'admin.php';
	$submenu_file = 'admin.php';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	require_once WORDPOINTS_DIR . 'admin/includes/class-wordpoints-module-installer.php';
	require_once WORDPOINTS_DIR . 'admin/includes/class-wordpoints-module-installer-skin.php';

	$upgrader = new WordPoints_Module_Installer(
		new WordPoints_Module_Installer_Skin(
			array(
				'title' => sprintf( esc_html__( 'Installing Module from uploaded file: %s', 'wordpoints' ), esc_html( basename( $file_upload->filename ) ) ),
				'nonce' => 'wordpoints-module-upload',
				'url'   => add_query_arg( array( 'package' => $file_upload->id ), 'update.php?action=upload-wordpoints-module' ),
				'type'  => 'upload',
			)
		)
	);

	$result = $upgrader->install( $file_upload->package );

	if ( $result || is_wp_error( $result ) ) {
		$file_upload->cleanup();
	}

	include ABSPATH . 'wp-admin/admin-footer.php';
}
add_action( 'update-custom_upload-wordpoints-module', 'wordpoints_upload_module_zip' );

/**
 * Notify the user when they try to install a module on the plugins screen.
 *
 * The function is hooked to the upgrader_source_selection action twice. The first
 * time it is called, we just save a local copy of the source path. This is
 * necessary because the second time around the source will be a WP_Error if there
 * are no plugins in it, but we have to have the source location so that we can check
 * if it is a module instead of a plugin.
 *
 * @since 1.9.0
 *
 * @param string|WP_Error $source The module source.
 *
 * @return string|WP_Error The filtered module source.
 */
function wordpoints_plugin_upload_error_filter( $source ) {

	static $_source;

	if ( ! isset( $_source ) ) {

		$_source = $source;

	} else {

		global $wp_filesystem;

		if (
			! is_wp_error( $_source )
			&& is_wp_error( $source )
			&& 'incompatible_archive_no_plugins' === $source->get_error_code()
		) {

			$working_directory = str_replace(
				$wp_filesystem->wp_content_dir()
				, trailingslashit( WP_CONTENT_DIR )
				, $_source
			);

			if ( is_dir( $working_directory ) ) {

				// Check if the folder contains a module.
				foreach ( glob( $working_directory . '*.php' ) as $file ) {

					$info = wordpoints_get_module_data( $file, false, false );

					if ( ! empty( $info['name'] ) ) {
						$source = new WP_Error(
							'wordpoints_module_archive_not_plugin'
							, $source->get_error_message()
							, __( 'This appears to be a WordPoints module archive. Try installing it on the WordPoints module install screen instead.', 'wordpoints' )
						);

						break;
					}
				}
			}
		}

		unset( $_source );
	}

	return $source;
}
add_action( 'upgrader_source_selection', 'wordpoints_plugin_upload_error_filter', 5 );
add_action( 'upgrader_source_selection', 'wordpoints_plugin_upload_error_filter', 20 );

/**
 * Add a sidebar to the general settings page.
 *
 * @since 1.1.0
 *
 * @action wordpoints_admin_settings_bottom 5 Before other items are added.
 */
function wordpoints_admin_settings_screen_sidebar() {

	?>

	<div style="height: 120px;border: none;padding: 1px 12px;background-color: #fff;border-left: 4px solid rgb(122, 208, 58);box-shadow: 0px 1px 1px 0px rgba(0, 0, 0, 0.1);margin-top: 50px;">
		<div style="width:48%;float:left;">
			<h4><?php esc_html_e( 'Like this plugin?', 'wordpoints' ); ?></h4>
			<p><?php echo wp_kses( sprintf( __( 'If you think WordPoints is great, let everyone know by giving it a <a href="%s">5 star rating</a>.', 'wordpoints' ), 'http://wordpress.org/support/view/plugin-reviews/wordpoints?rate=5#postform' ), array( 'a' => array( 'href' => true ) ) ); ?></p>
			<p><?php esc_html_e( 'If you don&#8217;t think this plugin deserves 5 stars, please let us know how we can improve it.', 'wordpoints' ); ?></p>
		</div>
		<div style="width:48%;float:left;">
			<h4><?php esc_html_e( 'Need help?', 'wordpoints' ); ?></h4>
			<p><?php echo wp_kses( sprintf( __( 'Post your feature request or support question in the <a href="%s">support forums</a>', 'wordpoints' ), 'http://wordpress.org/support/plugin/wordpoints' ), array( 'a' => array( 'href' => true ) ) ); ?></p>
			<p><em><?php esc_html_e( 'Thank you for using WordPoints!', 'wordpoints' ); ?></em></p>
		</div>
	</div>

	<?php
}
add_action( 'wordpoints_admin_configure_foot', 'wordpoints_admin_settings_screen_sidebar', 5 );

/**
 * Display notices to the user on the administration panels.
 *
 * @since 1.8.0
 */
function wordpoints_admin_notices() {

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
			wordpoints_delete_network_option( $option );
		}
	}

	if ( current_user_can( 'activate_wordpoints_modules' ) ) {

		if ( is_network_admin() ) {

			$deactivated_modules = get_site_option( 'wordpoints_breaking_deactivated_modules' );

			if ( is_array( $deactivated_modules ) ) {
				wordpoints_show_admin_error(
					sprintf(
						// translators: 1 is plugin version, 2 is list of modules
						__( 'WordPoints has deactivated the following modules because of incompatibilities with WordPoints %1$s: %2$s', 'wordpoints' )
						, WORDPOINTS_VERSION
						, implode( ', ', $deactivated_modules )
					)
					, array(
						'dismissible' => true,
						'option' => 'wordpoints_breaking_deactivated_modules',
					)
				);
			}

			$incompatible_modules = get_site_option( 'wordpoints_incompatible_modules' );

			if ( is_array( $incompatible_modules ) ) {
				wordpoints_show_admin_error(
					sprintf(
						// translators: 1 is plugin version, 2 is list of modules
						__( 'WordPoints has deactivated the following network-active modules because of incompatibilities with WordPoints %1$s: %2$s', 'wordpoints' )
						, WORDPOINTS_VERSION
						, implode( ', ', $incompatible_modules )
					)
					, array(
						'dismissible' => true,
						'option' => 'wordpoints_incompatible_modules',
					)
				);
			}

		} else {

			$incompatible_modules = get_option( 'wordpoints_incompatible_modules' );

			if ( is_array( $incompatible_modules ) ) {
				wordpoints_show_admin_error(
					sprintf(
						// translators: 1 is plugin version, 2 is list of modules
						__( 'WordPoints has deactivated the following modules on this site because of incompatibilities with WordPoints %1$s: %2$s', 'wordpoints' )
						, WORDPOINTS_VERSION
						, implode( ', ', $incompatible_modules )
					)
					, array(
						'dismissible' => true,
						'option' => 'wordpoints_incompatible_modules',
					)
				);
			}
		}
	}
}
add_action( 'admin_notices', 'wordpoints_admin_notices' );

/**
 * Save the screen options.
 *
 * @since 2.0.0
 *
 * @param mixed  $sanitized The sanitized option value, or false if not sanitized.
 * @param string $option    The option being saved.
 * @param mixed  $value     The raw value supplied by the user.
 *
 * @return mixed The option value, sanitized if it is for one of the plugin's screens.
 */
function wordpoints_admin_set_screen_option( $sanitized, $option, $value ) {

	switch ( $option ) {

		case 'wordpoints_page_wordpoints_modules_per_page':
		case 'wordpoints_page_wordpoints_modules_network_per_page':
		case 'toplevel_page_wordpoints_modules_per_page':
			$sanitized = wordpoints_posint( $value );
		break;
	}

	return $sanitized;
}
add_action( 'set-screen-option', 'wordpoints_admin_set_screen_option', 10, 3 );

/**
 * Ajax callback to load the modules admin screen when running module compat checks.
 *
 * We run this Ajax action to check module compatibility before loading modules
 * after WordPoints is updated to a new major version. This avoids breaking the site
 * if some modules aren't compatible with the backward-incompatible changes that are
 * present in a major version.
 *
 * @since 2.0.0
 */
function wordpoints_admin_ajax_breaking_module_check() {

	if ( ! isset( $_GET['wordpoints_module_check'] ) ) {
		wp_die( '', 400 );
	}

	if ( is_network_admin() ) {
		$nonce = get_site_option( 'wordpoints_module_check_nonce' );
	} else {
		$nonce = get_option( 'wordpoints_module_check_nonce' );
	}

	if ( ! $nonce || $nonce !== $_GET['wordpoints_module_check'] ) {
		wp_die( '', 403 );
	}

	// The list table constructor calls WP_Screen::get(), which expects this.
	$GLOBALS['hook_suffix'] = null;

	wordpoints_admin_screen_modules();

	wp_die( '', 200 );
}
add_action( 'wp_ajax_nopriv_wordpoints_breaking_module_check', 'wordpoints_admin_ajax_breaking_module_check' );

// EOF
