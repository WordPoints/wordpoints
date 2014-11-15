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
 * Deprecated administration-side code.
 *
 * @since 1.7.0
 */
include_once WORDPOINTS_DIR . 'admin/includes/deprecated.php';

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
 * @param string $args    Other optional arguments.
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
 * @since 1.8.0 The $args paramter was added with dismissable and option args.
 *
 * @param string $message The text for the message.
 * @param string $type    The type of message to display. Default is 'updated'.
 * @param array  $args    {
 *        Other optional arguments.
 *
 *        @type bool   $dismissable Whether this notice can be dismissed. Default is
 *                                  false (not dismissable).
 *        @type string $option      An option to delete when if message is dismissed.
 *                                  Required when $dismissable is true.
 * }
 */
function wordpoints_show_admin_message( $message, $type = 'updated', array $args = array() ) {

	$defaults = array(
		'dismissable' => false,
		'option'      => '',
	);

	$args = array_merge( $defaults, $args );

	?>

	<div id="message" class="<?php echo sanitize_html_class( $type, 'updated' ); ?>">
		<p>
			<?php echo wp_kses( $message, 'wordpoints_admin_message' ); ?>
		</p>
		<?php if ( $args['dismissable'] ) : ?>
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
		wp_die( esc_html__( 'You do not have sufficient permissions to install WordPoints modules on this site.', 'wordpoints' ) );
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
	if (
		isset( $_POST['wordpoints_notice'], $_POST['_wpnonce'] )
		&& wp_verify_nonce( $_POST['_wpnonce'], "wordpoints_dismiss_notice-{$_POST['wordpoints_notice']}" )
	) {
		wordpoints_delete_network_option( sanitize_key( $_POST['wordpoints_notice'] ) );
	}

	if ( current_user_can( 'manage_network_plugins' ) ) {

		unset( $message ); // Future proofing.

		// Show a notice if we've skipped part of the install/update process.
		if ( get_site_option( 'wordpoints_network_install_skipped' ) ) {
			$message = esc_html__( 'WordPoints detected a large network and has skipped part of the installation process.', 'wordpoints' );
			$option  = 'wordpoints_network_install_skipped';
		} elseif ( get_site_option( 'wordpoints_network_update_skipped' ) ) {
			$message = esc_html( sprintf( __( 'WordPoints detected a large network and has skipped part of the update process for version %s (and possibly later versions).', 'wordpoints' ), get_site_option( 'wordpoints_network_update_skipped' ) ) );
			$option  = 'wordpoints_network_update_skipped';
		}

		if ( isset( $message ) ) {

			$message .= ' ' . esc_html__( 'The rest of the process needs to be completed manually. If this has not been done already, some parts of the plugin may not function properly.', 'wordpoints' );
			$message .= ' <a href="http://wordpoints.org/user-guide/multisite/" target="_blank">' . esc_html__( 'Learn more.', 'wordpoints' ) . '</a>';

			$args = array(
				'dismissable' => true,
				'option'      => $option,
			);

			wordpoints_show_admin_error( $message, $args );
		}
	}
}
add_action( 'admin_notices', 'wordpoints_admin_notices' );

// EOF
