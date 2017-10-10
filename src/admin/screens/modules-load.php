<?php

/**
 * Set up for the modules screen.
 *
 * @package WordPoints\Administration
 * @since 1.1.0
 */

global $status, $wp_version, $parent_file;

if ( isset( $_POST['clear-recent-list'] ) ) {
	$action = 'clear-recent-list';
} elseif ( isset( $_REQUEST['action'] ) && -1 !== (int) $_REQUEST['action'] ) { // WPCS: CSRF OK.
	$action = sanitize_key( $_REQUEST['action'] );
} elseif ( isset( $_REQUEST['action2'] ) && -1 !== (int) $_REQUEST['action2'] ) { // WPCS: CSRF OK.
	$action = sanitize_key( $_REQUEST['action2'] );
} else {
	$action = '';
}

$page   = ( isset( $_REQUEST['paged'] ) ) ? max( 1, absint( $_REQUEST['paged'] ) ) : 1; // WPCS: CSRF OK.
$module = ( isset( $_REQUEST['module'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['module'] ) ) : ''; // WPCS: CSRF OK.
$s      = ( isset( $_REQUEST['s'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; // WPCS: CSRF OK.

// Clean up request URI from temporary args for screen options/paging URI's to work as expected.
$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'error', 'deleted', 'activate', 'activate-multi', 'deactivate', 'deactivate-multi', '_error_nonce' ) );

$redirect_url = self_admin_url( "admin.php?page=wordpoints_extensions&module_status={$status}&paged={$page}&s={$s}" );

switch ( $action ) {

	case '':
		break;

	// Activate a single module.
	case 'activate':
		if ( ! current_user_can( 'activate_wordpoints_extensions' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to activate extensions for this site.', 'wordpoints' ), '', array( 'response' => 403 ) );
		}

		if ( is_multisite() && ! is_network_admin() && is_network_only_wordpoints_module( $module ) ) {

			wp_safe_redirect( $redirect_url );
			exit;
		}

		check_admin_referer( 'activate-module_' . $module );

		$result = wordpoints_activate_module( $module, self_admin_url( 'admin.php?page=wordpoints_extensions&error=true&module=' . $module ), is_network_admin() );

		if ( is_wp_error( $result ) ) {

			if ( 'unexpected_output' === $result->get_error_code() ) {

				wp_safe_redirect(
					add_query_arg(
						array(
							'_error_nonce' => wp_create_nonce( 'module-activation-error_' . $module ),
							'module'       => $module,
							'error'        => true,
							'charsout'     => strlen( $result->get_error_data() ),
						)
						, $redirect_url
					)
				);

				exit;

			} else {

				wp_die( wordpoints_sanitize_wp_error( $result ) );
			}
		}

		if ( ! is_network_admin() ) {

			$recent = wordpoints_get_array_option( 'wordpoints_recently_activated_modules' );
			unset( $recent[ $module ] );
			update_option( 'wordpoints_recently_activated_modules', $recent );
		}

		wp_safe_redirect( add_query_arg( 'activate', 'true', $redirect_url ) );
	exit;

	// Activate multiple modules.
	case 'activate-selected':
		if ( ! current_user_can( 'activate_wordpoints_extensions' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to activate extensions for this site.', 'wordpoints' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( 'bulk-modules' );

		$modules = isset( $_POST['checked'] )
			? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['checked'] ) )
			: array();

		// Only activate modules which are not already active.
		if ( is_network_admin() ) {

			foreach ( $modules as $i => $module ) {
				if ( is_wordpoints_module_active_for_network( $module ) ) {
					unset( $modules[ $i ] );
				}
			}

		} else {

			foreach ( $modules as $i => $module ) {
				if ( is_wordpoints_module_active( $module ) || is_network_only_wordpoints_module( $module ) ) {
					unset( $modules[ $i ] );
				}
			}
		}

		if ( empty( $modules ) ) {
			wp_safe_redirect( $redirect_url );
			exit;
		}

		$redirect = self_admin_url( 'admin.php?page=wordpoints_extensions&error=true' );

		foreach ( $modules as $module ) {

			wordpoints_activate_module( $module, add_query_arg( 'module', $module, $redirect ), is_network_admin() );
		}

		if ( ! is_network_admin() ) {

			$recent = wordpoints_get_array_option( 'wordpoints_recently_activated_modules' );

			foreach ( $modules as $module ) {
				unset( $recent[ $module ] );
			}

			update_option( 'wordpoints_recently_activated_modules', $recent );
		}

		wp_safe_redirect( add_query_arg( 'activate-multi', 'true', $redirect_url ) );
	exit;

	// Get the fatal error from a module.
	case 'error_scrape':
		if ( ! current_user_can( 'activate_wordpoints_extensions' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to activate extensions for this site.', 'wordpoints' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( 'module-activation-error_' . $module );

		$valid = wordpoints_validate_module( $module );

		if ( is_wp_error( $valid ) ) {
			wp_die( wordpoints_sanitize_wp_error( $valid ), '', array( 'response' => 400 ) );
		}

		// Ensure that Fatal errors are displayed.
		if ( ! WP_DEBUG ) {
			error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR ); // @codingStandardsIgnoreLine
		}

		@ini_set( 'display_errors', true ); // @codingStandardsIgnoreLine

		/**
		 * Go back to "sandbox" scope so we get the same errors as before.
		 *
		 * @since 1.1.0
		 * @access private
		 *
		 * @param string $module The base path to the module to error scrape.
		 */
		function wordpoints_module_sandbox_scrape( $module ) {

			$modules_dir = wordpoints_extensions_dir();
			WordPoints_Module_Paths::register( $modules_dir . '/' . $module );
			include $modules_dir . '/' . $module;
		}

		wordpoints_module_sandbox_scrape( $module );

		/**
		 * @see wordpoints_activate_module()
		 */
		do_action( "wordpoints_module_activate-{$module}" );
	exit;

	// Deactivate a module.
	case 'deactivate':
		if ( ! current_user_can( 'activate_wordpoints_extensions' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to deactivate extensions for this site.', 'wordpoints' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( 'deactivate-module_' . $module );

		if ( ! is_network_admin() && is_wordpoints_module_active_for_network( $module ) ) {
			wp_safe_redirect( $redirect_url );
			exit;
		}

		wordpoints_deactivate_modules( $module, false, is_network_admin() );

		if ( ! is_network_admin() ) {
			update_option( 'wordpoints_recently_activated_modules', array( $module => time() ) + wordpoints_get_array_option( 'wordpoints_recently_activated_modules' ) );
		}

		$redirect_url = add_query_arg( 'deactivate', 'true', $redirect_url );

		if ( headers_sent() ) {
			echo '<meta http-equiv="refresh" content="0;url=' . esc_url( $redirect_url ) . '" />';
		} else {
			wp_safe_redirect( $redirect_url );
		}
	exit;

	// Deactivate multiple modules.
	case 'deactivate-selected':
		if ( ! current_user_can( 'activate_wordpoints_extensions' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to deactivate extensions for this site.', 'wordpoints' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( 'bulk-modules' );

		$modules = isset( $_POST['checked'] )
			? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['checked'] ) )
			: array();

		$network_modules = array_filter( $modules, 'is_wordpoints_module_active_for_network' );

		// Do not deactivate modules which are already deactivated.
		if ( is_network_admin() ) {
			$modules = $network_modules;
		} else {
			$modules = array_diff( array_filter( $modules, 'is_wordpoints_module_active' ), $network_modules );
		}

		if ( empty( $modules ) ) {
			wp_safe_redirect( $redirect_url );
			exit;
		}

		wordpoints_deactivate_modules( $modules, false, is_network_admin() );

		if ( ! is_network_admin() ) {

			$deactivated = array();

			foreach ( $modules as $module ) {
				$deactivated[ $module ] = time();
			}

			update_option( 'wordpoints_recently_activated_modules', $deactivated + wordpoints_get_array_option( 'wordpoints_recently_activated_modules' ) );
		}

		wp_safe_redirect( add_query_arg( 'deactivate-multi', 'true', $redirect_url ) );
	exit;

	// Delete multiple modules.
	case 'delete-selected':
		if ( ! current_user_can( 'delete_wordpoints_extensions' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to delete extensions for this site.', 'wordpoints' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( 'bulk-modules' );

		// $_POST = from the module form; $_GET = from the FTP details screen.
		$modules = isset( $_REQUEST['checked'] )
			? array_map( 'sanitize_text_field', (array) wp_unslash( $_REQUEST['checked'] ) )
			: array();

		if ( empty( $modules ) ) {
			wp_safe_redirect( $redirect_url );
			exit;
		}

		// Do not allow to delete activated modules.
		foreach ( $modules as $key => $module ) {

			if (
				true !== wordpoints_validate_module( $module )
				|| is_wordpoints_module_active( $module )
			) {
				unset( $modules[ $key ] );
			}
		}

		if ( empty( $modules ) ) {
			wp_safe_redirect( add_query_arg( array( 'error' => 'true', 'main' => 'true' ), $redirect_url ) );
			exit;
		}

		require_once ABSPATH . 'wp-admin/update.php';

		$parent_file = 'admin.php';

		if ( ! isset( $_REQUEST['verify-delete'] ) ) {

			wp_enqueue_script( 'jquery' );
			require_once ABSPATH . 'wp-admin/admin-header.php';

			?>

			<div class="wrap">

				<?php

				$module_dir               = wordpoints_extensions_dir();
				$module_info              = array();
				$files_to_delete          = array();
				$have_non_network_modules = false;

				foreach ( $modules as $module ) {

					if ( '.' === dirname( $module ) ) {

						$files_to_delete[] = $module_dir . '/' . $module;
						$data              = wordpoints_get_module_data( $module_dir . '/' . $module );

						if ( ! empty( $data ) ) {

							$module_info[ $module ] = $data;
							$module_info[ $module ]['is_uninstallable'] = is_uninstallable_wordpoints_module( $module );

							if ( ! $module_info[ $module ]['network'] ) {
								$have_non_network_modules = true;
							}
						}

					} else {

						// Locate all the files in that folder.
						$files = list_files( $module_dir . '/' . dirname( $module ) );

						if ( $files ) {
							$files_to_delete = array_merge( $files_to_delete, $files );
						}

						// Get modules list from that folder.
						$folder_modules = wordpoints_get_modules( '/' . dirname( $module ) );

						foreach ( $folder_modules as $module_file => $data ) {

							$module_info[ $module_file ] = $data;
							$module_info[ $module_file ]['is_uninstallable'] = is_uninstallable_wordpoints_module( $module );

							if ( ! $module_info[ $module_file ]['network'] ) {
								$have_non_network_modules = true;
							}
						}

					} // End if ( single file module ) else.

				} // End foreach( $modules ).

				$modules_to_delete = count( $module_info );

				echo '<h1>' . esc_html( _n( 'Delete extension', 'Delete extensions', $modules_to_delete, 'wordpoints' ) ) . '</h1>';

				if ( $have_non_network_modules && is_network_admin() ) {
					wordpoints_show_admin_message(
						'<strong>' . esc_html__( 'Caution:', 'wordpoints' ) . '</strong>'
							. esc_html(
								_n(
									'This extension may be active on other sites in the network.'
									, 'These extensions may be active on other sites in the network.'
									, $modules_to_delete
									, 'wordpoints'
								)
							)
						, 'warning'
					);
				}

				?>

				<p><?php echo esc_html( _n( 'You are about to remove the following extension:', 'You are about to remove the following extensions:', $modules_to_delete, 'wordpoints' ) ); ?></p>
					<ul class="ul-disc">

						<?php

						$data_to_delete = false;

						foreach ( $module_info as $module ) {

							if ( $module['is_uninstallable'] ) {

								// translators: 1. Extension name; 2. Extension author.
								echo '<li>', wp_kses( sprintf( __( '<strong>%1$s</strong> by <em>%2$s</em> (will also <strong>delete its data</strong>)', 'wordpoints' ), esc_html( $module['name'] ), esc_html( $module['author_name'] ) ), array( 'strong' => array(), 'em' => array() ) ), '</li>';
								$data_to_delete = true;

							} else {

								// translators: 1. Extension name; 2. Extension author.
								echo '<li>', wp_kses( sprintf( __( '<strong>%1$s</strong> by <em>%2$s</em>', 'wordpoints' ), esc_html( $module['name'] ), esc_html( $module['author_name'] ) ), array( 'strong' => array(), 'em' => array() ) ), '</li>';
							}
						}

						?>

					</ul>
				<p>
					<?php

					if ( $data_to_delete ) {
						esc_html_e( 'Are you sure you wish to delete these files and data?', 'wordpoints' );
					} else {
						esc_html_e( 'Are you sure you wish to delete these files?', 'wordpoints' );
					}

					?>
				</p>

				<form method="post" style="display:inline;">
					<input type="hidden" name="verify-delete" value="1" />
					<input type="hidden" name="action" value="delete-selected" />
					<?php foreach ( (array) $modules as $module ) : ?>
						<input type="hidden" name="checked[]" value="<?php echo esc_attr( $module ); ?>" />
					<?php endforeach; ?>
					<?php wp_nonce_field( 'bulk-modules' ); ?>
					<?php submit_button( $data_to_delete ? __( 'Yes, Delete these files and data', 'wordpoints' ) : __( 'Yes, Delete these files', 'wordpoints' ), 'button', 'submit', false ); ?>
				</form>
				<form method="post" action="<?php echo esc_url( wp_get_referer() ); ?>" style="display:inline;">
					<?php submit_button( __( 'No, Return me to the extension list', 'wordpoints' ), 'button', 'submit', false ); ?>
				</form>

				<p><a href="#" onclick="jQuery('#files-list').toggle(); return false;"><?php esc_html_e( 'Click to view entire list of files which will be deleted', 'wordpoints' ); ?></a></p>
				<div id="files-list" class="hidden">
					<ul class="code">
						<?php foreach ( (array) $files_to_delete as $file ) : ?>
							<li><?php echo esc_html( str_replace( $module_dir, '', $file ) ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<?php

			require_once ABSPATH . 'wp-admin/admin-footer.php';
			exit;

		}  // End if ( not requesting verification before delete ).

		$delete_result = wordpoints_delete_modules( $modules );

		// Store the result in a cache rather than a URL param due to object type & length
		set_transient( 'wordpoints_modules_delete_result_' . get_current_user_id(), $delete_result );

		wp_safe_redirect( add_query_arg( 'deleted', 'true', $redirect_url ) );
	exit;

	case 'clear-recent-list':
		if ( ! is_network_admin() ) {
			update_option( 'wordpoints_recently_activated_modules', array() );
		}
	break;

	default:
		/**
		 * Custom action on the extensions screen.
		 *
		 * @since 2.4.0
		 */
		do_action( "wordpoints_extensions_screen-{$action}" );

		/**
		 * Custom action on the modules screen.
		 *
		 * @since 1.1.0
		 * @deprecated 2.4.0 Use 'wordpoints_extensions_screen-{$action}' instead.
		 */
		do_action_deprecated( "wordpoints_modules_screen-{$action}", array(), '2.4.0', "wordpoints_extensions_screen-{$action}" );

} // End switch ( $action ).

add_screen_option( 'per_page', array( 'default' => 999 ) );

$screen = get_current_screen();

$screen->add_help_tab(
	array(
		'id'      => 'overview',
		'title'   => __( 'Overview', 'wordpoints' ),
		'content' =>
			'<p>' . esc_html__( 'Extensions extend and expand the functionality of WordPoints. Once an extension is installed, you may activate it or deactivate it here.', 'wordpoints' ) . '</p>' .
			// translators: 1. URL of extension directory on WordPoints.org; 2. URL of Install Extension admin screen; 3. Directory name.
			'<p>' . wp_kses( sprintf( __( 'You can find extensions for your site by browsing the <a href="%1$s">WordPoints Extensions Directory</a>. To install an extension you generally just need to <a href="%2$s">upload the extension file</a> into your %3$s directory. Once an extension has been installed, you can activate it here.', 'wordpoints' ), 'https://wordpoints.org/extensions/', esc_url( self_admin_url( 'admin.php?page=wordpoints_install_extensions' ) ), '<code>/wp-content/wordpoints-extensions</code>' ), array( 'a' => array( 'href' => true, 'target' => true ), 'code' => array() ) ) . '</p>',
	)
);

$screen->add_help_tab(
	array(
		'id'      => 'compatibility-problems',
		'title'   => __( 'Troubleshooting', 'wordpoints' ),
		'content' =>
			'<p>' . esc_html__( 'Most of the time, extensions play nicely with the core of WordPoints and with other extensions. Sometimes, though, an extension&#8217;s code will get in the way of another extension, causing compatibility issues. If your site starts doing strange things, this may be the problem. Try deactivating all your extension and re-activating them in various combinations until you isolate which one(s) caused the issue.', 'wordpoints' ) . '</p>' .
			// translators: Directory name.
			'<p>' . sprintf( esc_html__( 'If something goes wrong with an extension and you can&#8217;t use WordPoints, delete or rename that file in the %s directory and it will be automatically deactivated.', 'wordpoints' ), '<code>' . esc_html( wordpoints_extensions_dir() ) . '</code>' ) . '</p>', // XSS OK WPCS
	)
);

$screen->set_help_sidebar(
	'<p><strong>' . esc_html__( 'For more information:', 'wordpoints' ) . '</strong></p>
	<p><a href="https://wordpoints.org/developer-guide/extensions/">' . esc_html__( 'Developer Documentation', 'wordpoints' ) . '</a></p>
	<p><a href="https://wordpress.org/support/plugin/wordpoints">' . esc_html__( 'Support Forums', 'wordpoints' ) . '</a></p>'
);

register_column_headers(
	$screen
	, array(
		'cb'          => '<input type="checkbox" />',
		'name'        => _x( 'Extension', 'extensions table heading', 'wordpoints' ),
		'description' => _x( 'Description', 'extensions table heading', 'wordpoints' ),
	)
);

$screen->set_screen_reader_content(
	array(
		'heading_views'      => __( 'Filter extensions list', 'wordpoints' ),
		'heading_pagination' => __( 'Extensions list navigation', 'wordpoints' ),
		'heading_list'       => __( 'Extensions list', 'wordpoints' ),
	)
);

wp_enqueue_style( 'wordpoints-admin-extensions-list-table' );

// EOF
