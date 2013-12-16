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
 * Add admin screens to the administration menu.
 *
 * @since 1.0.0
 *
 * @action admin_menu
 */
function wordpoints_admin_menu() {

	$wordpoints = __( 'WordPoints', 'wordpoints' );

	// Main page.
	add_menu_page(
		$wordpoints
		,$wordpoints
		,'manage_options'
		,'wordpoints_configure'
		,'wordpoints_admin_screen_configure'
	);

	// Settings page.
	add_submenu_page(
		'wordpoints_configure'
		,__( 'WordPoints - Configure', 'wordpoints' )
		,__( 'Configure', 'wordpoints' )
		,'manage_options'
		,'wordpoints_configure'
		,'wordpoints_admin_screen_configure'
	);

	// Modules page.
	add_submenu_page(
		'wordpoints_configure'
		,__( 'WordPoints - Modules', 'wordpoints' )
		,__( 'Modules', 'wordpoints' )
		,'activate_wordpoints_modules'
		,'wordpoints_modules'
		,'wordpoints_display_admin_screen'
	);

	// Module install page.
	add_submenu_page(
		'wordpoints_modules'
		,__( 'WordPoints - Install Modules', 'wordpoints' )
		,__( 'Install Modules', 'wordpoints' )
		,'install_wordpoints_modules'
		,'wordpoints_install_modules'
		,'wordpoints_admin_screen_install_modules'
	);
}
add_action( 'admin_menu', 'wordpoints_admin_menu' );

/**
 * Display one of the administration screens.
 *
 * @since 1.1.0
 */
function wordpoints_display_admin_screen() {

	$screen = str_replace( 'wordpoints_page_wordpoints_', '', current_filter() );

	require WORDPOINTS_DIR . "admin/screens/{$screen}.php";
}

/**
 * Set up for the modules screen.
 *
 * @since 1.1.0
 *
 * @action
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
 *
 * @uses wordpoints_show_admin_message()
 *
 * @param string $message The text for the error message.
 */
function wordpoints_show_admin_error( $message ) {

	wordpoints_show_admin_message( $message, 'error' );
}

/**
 * Display an update message.
 *
 * Note that $type is expected to be properly sanitized as needed (e.g., esc_attr()).
 * But you should use {@see wordpoints_show_admin_error()} instead for showing error
 * messages. Currently there aren't wrappers for the other types, as they aren't used
 * in WordPoints core.
 *
 * @since 1.0.0
 *
 * @param string $message The text for the message. Must be pre-validated if needed.
 * @param string $type    The type of message to display. Default is 'updated'.
 */
function wordpoints_show_admin_message( $message, $type = 'updated' ) {

	?>

	<div id="message" class="<?php echo $type; ?>">
		<p>
			<?php echo $message; ?>
		</p>
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

		$tab = $_GET['tab'];
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

		echo '<h2>', esc_html( sprintf( __( 'WordPoints - %s', 'wordpoints' ), $tabs[ $current ] ) ), '</h2>';
	}

    echo '<h2 class="nav-tab-wrapper">';

	$page = rawurlencode( $_GET['page'] );

    foreach ( $tabs as $tab => $name ) {

        $class = ( $tab == $current ) ? ' nav-tab-active' : '';

        echo '<a class="nav-tab', $class, '" href="?page=', $page, '&amp;tab=', rawurlencode( $tab ), '">', esc_html( $name ), '</a>';
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

	<h4><?php _e( 'Install a module in .zip format', 'wordpoints' ); ?></h4>
	<p class="install-help"><?php _e( 'If you have a module in a .zip format, you may install it by uploading it here.', 'wordpoints' ); ?></p>
	<form method="post" enctype="multipart/form-data" class="wp-upload-form" action="<?php echo self_admin_url( 'update.php?action=upload-wordpoints-module' ); ?>">
		<?php wp_nonce_field( 'wordpoints-module-upload'); ?>
		<label class="screen-reader-text" for="modulezip"><?php _e( 'Module zip file', 'wordpoints' ); ?></label>
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
		wp_die( __( 'You do not have sufficient permissions to install WordPoints modules on this site.', 'wordpoints' ) );
	}

	check_admin_referer( 'wordpoints-module-upload' );

	$file_upload = new File_Upload_Upgrader( 'modulezip', 'package' );

	$title = __( 'Upload WordPoints Module', 'wordpoints' );
	$parent_file = 'admin.php';
	$submenu_file = 'admin.php';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	require_once WORDPOINTS_DIR . 'admin/includes/class-wordpoints-module-installer.php';
	require_once WORDPOINTS_DIR . 'admin/includes/class-wordpoints-module-installer-skin.php';

	$upgrader = new WordPoints_Module_Installer(
		new WordPoints_Module_Installer_Skin(
			array(
				'title' => sprintf( __( 'Installing Module from uploaded file: %s', 'wordpoints' ), esc_html( basename( $file_upload->filename ) ) ),
				'nonce' => 'wordpoints-module-upload',
				'url'   => add_query_arg( array( 'package' => $file_upload->id ), 'update.php?action=upload-wordpoints-module' ),
				'type'  => 'upload',
			)
		)
	);

	$result = $upgrader->install( $file_upload->package );

	if ( $result || is_wp_error($result) ) {
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

	<div style="height: 120px;border: none;padding: 1px 12px;background-color: #fff;border-left: 4px solid rgb(122, 208, 58);box-shadow: 0px 1px 1px 0px rgba(0, 0, 0, 0.1);">
		<div style="width:48%;float:left;">
			<h4><?php _e( 'Like this plugin?', 'wordpoints' ); ?></h4>
			<p><?php printf( __( 'If you think WordPoints is great, let everyone know by giving it a <a href="%s">5 star rating</a>.', 'wordpoints' ), 'http://wordpress.org/support/view/plugin-reviews/wordpoints?rate=5#postform' ); ?></p>
			<p><?php _e( 'If you don&#8217;t think this plugin deserves 5 stars, please let us know how we can improve it.', 'wordpoints' ); ?></p>
		</div>
		<div style="width:48%;float:left;">
			<h4><?php _e( 'Need help?', 'wordpoints' ); ?></h4>
			<p><?php printf( __( 'Post your feature request or support question in the <a href="%s">support forums</a>', 'wordpoints' ), 'http://wordpress.org/support/plugin/wordpoints' ); ?></p>
			<p><em><?php _e( 'Thank you for using WordPoints!', 'wordpoints' ); ?></em></p>
		</div>
	</div>

	<?php
}
add_action( 'wordpoints_admin_configure_foot', 'wordpoints_admin_settings_screen_sidebar', 5 );

// end of file /admin/admin.php
