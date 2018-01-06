<?php

/**
 * Admin-side extensions-related functions.
 *
 * @package WordPoints\Admin
 * @since   2.5.0
 */

/**
 * Display the upload module from zip form.
 *
 * @since 1.1.0
 *
 * @WordPress\action wordpoints_install_extensions-upload
 */
function wordpoints_install_modules_upload() {

	wp_enqueue_style( 'wordpoints-admin-general' );

	?>

	<div class="upload-plugin wordpoints-upload-module wordpoints-upload-extension">
		<p class="install-help"><?php esc_html_e( 'If you have an extension in a .zip format, you may install it by uploading it here.', 'wordpoints' ); ?></p>
		<form method="post" enctype="multipart/form-data" class="wp-upload-form" action="<?php echo esc_url( self_admin_url( 'update.php?action=upload-wordpoints-module' ) ); ?>">
			<?php wp_nonce_field( 'wordpoints-module-upload' ); ?>
			<label class="screen-reader-text" for="modulezip"><?php esc_html_e( 'Extension zip file', 'wordpoints' ); ?></label>
			<input type="file" id="modulezip" name="modulezip" />
			<?php submit_button( __( 'Install Now', 'wordpoints' ), '', 'install-module-submit', false ); ?>
		</form>
	</div>

	<?php
}

/**
 * Perform module upload from .zip file.
 *
 * @since 1.1.0
 *
 * @WordPress\action update-custom_upload-wordpoints-module
 */
function wordpoints_upload_module_zip() {

	global $title, $parent_file, $submenu_file;

	if ( ! current_user_can( 'install_wordpoints_extensions' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to install WordPoints extensions on this site.', 'wordpoints' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( 'wordpoints-module-upload' );

	$file_upload = new File_Upload_Upgrader( 'modulezip', 'package' );

	$title        = esc_html__( 'Upload WordPoints Extension', 'wordpoints' );
	$parent_file  = 'admin.php';
	$submenu_file = 'admin.php';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	$upgrader = new WordPoints_Module_Installer(
		new WordPoints_Module_Installer_Skin(
			array(
				// translators: File name.
				'title' => sprintf( esc_html__( 'Installing Extension from uploaded file: %s', 'wordpoints' ), esc_html( basename( $file_upload->filename ) ) ),
				'nonce' => 'wordpoints-module-upload',
				'url'   => add_query_arg( array( 'package' => $file_upload->id ), self_admin_url( 'update.php?action=upload-wordpoints-module' ) ),
				'type'  => 'upload',
			)
		)
	);

	$result = $upgrader->install( $file_upload->package );

	if ( $result || is_wp_error( $result ) ) {
		$file_upload->cleanup();
	}

	require ABSPATH . 'wp-admin/admin-footer.php';
}

/**
 * Handles a request to upgrade an extension, displaying the extension upgrade screen.
 *
 * @since 2.4.0
 *
 * @WordPress\action update-custom_wordpoints-upgrade-extension
 */
function wordpoints_admin_screen_upgrade_extension() {

	global $title, $parent_file;

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to update WordPoints extensions for this site.', 'wordpoints' ), 403 );
	}

	$extension = ( isset( $_REQUEST['extension'] ) )
		? sanitize_text_field( wp_unslash( $_REQUEST['extension'] ) ) // WPCS: CSRF OK.
		: '';

	check_admin_referer( 'upgrade-extension_' . $extension );

	$title       = __( 'Update WordPoints Extension', 'wordpoints' );
	$parent_file = 'admin.php';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	$upgrader = new WordPoints_Extension_Upgrader(
		new WordPoints_Extension_Upgrader_Skin(
			array(
				'title'     => $title,
				'nonce'     => 'upgrade-extension_' . $extension,
				'url'       => 'update.php?action=wordpoints-upgrade-extension&extension=' . rawurlencode( $extension ),
				'extension' => $extension,
			)
		)
	);

	$upgrader->upgrade( $extension );

	require ABSPATH . 'wp-admin/admin-footer.php';
}

/**
 * Reactivates an extension in an iframe after it was updated.
 *
 * @since 2.4.0
 *
 * @WordPress\action update-custom_wordpoints-reactivate-extension
 */
function wordpoints_admin_iframe_reactivate_extension() {

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to update WordPoints extensions for this site.', 'wordpoints' ), 403 );
	}

	$extension = ( isset( $_REQUEST['extension'] ) )
		? sanitize_text_field( wp_unslash( $_REQUEST['extension'] ) ) // WPCS: CSRF OK.
		: '';

	check_admin_referer( 'reactivate-extension_' . $extension );

	// First, activate the extension.
	if ( ! isset( $_GET['failure'] ) && ! isset( $_GET['success'] ) ) {

		$nonce = sanitize_key( $_GET['_wpnonce'] ); // @codingStandardsIgnoreLine
		$url   = admin_url( 'update.php?action=wordpoints-reactivate-extension&extension=' . rawurlencode( $extension ) . '&_wpnonce=' . $nonce );

		wp_safe_redirect( $url . '&failure=true' );
		wordpoints_activate_module( $extension, '', ! empty( $_GET['network_wide'] ), true );
		wp_safe_redirect( $url . '&success=true' );

		die();
	}

	// Then we redirect back here to display the success or error message.
	iframe_header( __( 'WordPoints Extension Reactivation', 'wordpoints' ) );

	if ( isset( $_GET['success'] ) ) {

		echo '<p>' . esc_html__( 'Extension reactivated successfully.', 'wordpoints' ) . '</p>';

	} elseif ( isset( $_GET['failure'] ) ) {

		echo '<p>' . esc_html__( 'Extension failed to reactivate due to a fatal error.', 'wordpoints' ) . '</p>';

		// Ensure that Fatal errors are displayed.
		// @codingStandardsIgnoreStart
		error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
		@ini_set( 'display_errors', true );
		// @codingStandardsIgnoreEnd

		$file = wordpoints_extensions_dir() . '/' . $extension;
		WordPoints_Module_Paths::register( $file );
		include $file;
	}

	iframe_footer();
}

/**
 * Handle updating multiple extensions on the extensions administration screen.
 *
 * @since 2.4.0
 */
function wordpoints_admin_screen_update_selected_extensions() {

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to update WordPoints extensions for this site.', 'wordpoints' ), 403 );
	}

	global $parent_file;

	check_admin_referer( 'bulk-wordpoints-extensions', 'nonce' );

	if ( isset( $_GET['extensions'] ) ) {
		$extensions = explode( ',', sanitize_text_field( wp_unslash( $_GET['extensions'] ) ) );
	} elseif ( isset( $_POST['checked'] ) ) {
		$extensions = array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['checked'] ) );
	} else {
		$extensions = array();
	}

	$url = self_admin_url( 'update.php?action=update-selected-wordpoints-extensions&amp;extensions=' . rawurlencode( implode( ',', $extensions ) ) );
	$url = wp_nonce_url( $url, 'bulk-update-extensions' );

	$parent_file = 'admin.php';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	wp_enqueue_style( 'wordpoints-admin-general' );

	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Update WordPoints Extensions', 'wordpoints' ); ?></h1>

		<iframe name="wordpoints_extension_updates" src="<?php echo esc_url( $url ); ?>" class="wordpoints-extension-updates-iframe"></iframe>
	</div>

	<?php

	require_once ABSPATH . 'wp-admin/admin-footer.php';

	exit;
}

/**
 * Handle bulk extension update requests from within an iframe.
 *
 * @since 2.4.0
 */
function wordpoints_iframe_update_extensions() {

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to update WordPoints extensions for this site.', 'wordpoints' ), 403 );
	}

	check_admin_referer( 'bulk-update-extensions' );

	$extensions = array();

	if ( isset( $_GET['extensions'] ) ) {
		$extensions = explode( ',', sanitize_text_field( wp_unslash( $_GET['extensions'] ) ) );
	}

	$extensions = array_map( 'rawurldecode', $extensions );

	wp_enqueue_script( 'jquery' );
	iframe_header();

	$upgrader = new WordPoints_Extension_Upgrader(
		new WordPoints_Extension_Upgrader_Skin_Bulk(
			array(
				'nonce' => 'bulk-update-extensions',
				'url'   => 'update.php?action=update-selected-wordpoints-extensions&amp;extensions=' . rawurlencode( implode( ',', $extensions ) ),
			)
		)
	);

	$upgrader->bulk_upgrade( $extensions );

	iframe_footer();
}

/**
 * Sets up the action hooks to display the extension update rows.
 *
 * @since 2.4.0
 */
function wordpoints_extension_update_rows() {

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		return;
	}

	$updates = wordpoints_get_extension_updates();

	foreach ( $updates->get_new_versions() as $extension_file => $version ) {
		add_action( "wordpoints_after_module_row_{$extension_file}", 'wordpoints_extension_update_row', 10, 2 );
	}
}

/**
 * Displays the update message for an extension in the extensions list table.
 *
 * @since 2.4.0
 *
 * @WordPress\action wordpoints_after_module_row_{$extension_file} Added by
 *                   wordpoints_extension_update_rows().
 */
function wordpoints_extension_update_row( $file, $extension_data ) {

	$updates = wordpoints_get_extension_updates();

	if ( ! $updates->has_update( $file ) ) {
		return;
	}

	$server = wordpoints_get_server_for_extension( $extension_data );

	if ( ! $server ) {
		return;
	}

	$api = $server->get_api();

	if ( ! $api instanceof WordPoints_Extension_Server_API_UpdatesI ) {
		return;
	}

	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );

	$new_version = $updates->get_new_version( $file );

	$extension_name = wp_kses(
		$extension_data['name']
		, array(
			'a'       => array( 'href' => array(), 'title' => array() ),
			'abbr'    => array( 'title' => array() ),
			'acronym' => array( 'title' => array() ),
			'code'    => array(),
			'em'      => array(),
			'strong'  => array(),
		)
	);

	if ( is_network_admin() ) {
		$is_active = is_wordpoints_module_active_for_network( $file );
	} else {
		$is_active = is_wordpoints_module_active( $file );
	}

	?>

	<tr class="plugin-update-tr wordpoints-extension-update-tr <?php echo ( $is_active ) ? 'active' : 'inactive'; ?>">
		<td colspan="<?php echo (int) WordPoints_Admin_List_Table_Extensions::instance()->get_column_count(); ?>" class="plugin-update wordpoints-extension-update colspanchange">
			<div class="update-message notice inline notice-warning notice-alt">
				<p>
					<?php

					printf( // WPCS: XSS OK.
						// translators: Extension name.
						esc_html__( 'There is a new version of %1$s available.', 'wordpoints' )
						, $extension_name
					);

					?>

					<?php if ( $api instanceof WordPoints_Extension_Server_API_Updates_ChangelogI ) : ?>
						<?php

						// translators: 1. Extension name; 2. Version.
						$message = __( 'View %1$s version %2$s details', 'wordpoints' );

						?>
						<a
							href="<?php echo esc_url( admin_url( 'update.php?action=wordpoints-iframe-extension-changelog&extension=' . rawurlencode( $file ) ) ); ?>"
							class="thickbox wordpoints-open-extension-details-modal"
							aria-label="<?php echo esc_attr( sprintf( $message, $extension_name, $new_version ) ); ?>"
						>
							<?php

							printf(
								// translators: Version number.
								esc_html__( 'View version %1$s details', 'wordpoints' )
								, esc_html( $new_version )
							);

							?>
						</a>
					<?php endif; ?>

					<?php if ( current_user_can( 'update_wordpoints_extensions' ) ) : ?>
						<span class="wordpoints-update-action-separator">|</span>
						<?php if ( $api instanceof WordPoints_Extension_Server_API_Updates_InstallableI ) : ?>
							<?php

							// translators: Extension name.
							$message = sprintf( __( 'Update %s now', 'wordpoints' ), $extension_name );

							?>
							<a
								href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=wordpoints-upgrade-extension&extension=' ) . $file, 'upgrade-extension_' . $file ) ); ?>"
								aria-label="<?php echo esc_attr( $message ); ?>"
							>
								<?php esc_html_e( 'Update now', 'wordpoints' ); ?>
							</a>
						<?php else : ?>
							<em>
								<?php esc_html_e( 'Automatic update is unavailable for this extension.', 'wordpoints' ); ?>
							</em>
						<?php endif; ?>
					<?php endif; ?>

					<?php

					/**
					 * Fires at the end of the update message container in each row
					 * of the extensions list table.
					 *
					 * The dynamic portion of the hook name, `$file`, refers to the
					 * path of the extension's primary file relative to the
					 * extensions directory.
					 *
					 * @since 2.4.0
					 *
					 * @param array  $extension_data The extension's data.
					 * @param string $new_version    The new version of the extension.
					 */
					do_action( "wordpoints_in_extension_update_message-{$file}", $extension_data, $new_version );

					?>
				</p>
			</div>
		</td>
	</tr>

	<?php
}

/**
 * Save extension license forms on submit.
 *
 * @since 2.4.0
 *
 * @WordPress\action wordpoints_modules_list_table_items
 */
function wordpoints_admin_save_extension_licenses( $extensions ) {

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		return $extensions;
	}

	foreach ( $extensions['all'] as $extension ) {

		if ( empty( $extension['ID'] ) ) {
			continue;
		}

		$server = wordpoints_get_server_for_extension( $extension );

		if ( ! $server ) {
			continue;
		}

		$api = $server->get_api();

		if ( ! $api instanceof WordPoints_Extension_Server_API_LicensesI ) {
			continue;
		}

		$extension_data = new WordPoints_Extension_Server_API_Extension_Data(
			$extension['ID']
			, $server
		);

		$url = sanitize_title_with_dashes( $server->get_slug() );

		if ( ! isset( $_POST[ "license_key-{$url}-{$extension['ID']}" ] ) ) {
			continue;
		}

		$license_key = sanitize_key(
			$_POST[ "license_key-{$url}-{$extension['ID']}" ]
		);

		$license = $api->get_extension_license_object( $extension_data, $license_key );

		if (
			isset(
				$_POST[ "activate-license-{$extension['ID']}" ]
				, $_POST[ "wordpoints_activate_license_key-{$extension['ID']}" ]
			)
			&& wordpoints_verify_nonce(
				"wordpoints_activate_license_key-{$extension['ID']}"
				, "wordpoints_activate_license_key-{$extension['ID']}"
				, null
				, 'post'
			)
		) {

			if ( ! $license instanceof WordPoints_Extension_Server_API_Extension_License_ActivatableI ) {
				continue;
			}

			$result = $license->activate();

			if ( true === $result ) {
				wordpoints_show_admin_message( esc_html__( 'License activated.', 'wordpoints' ) );
				$extension_data->set( 'license_key', $license_key );
				wordpoints_check_for_extension_updates_now();
			} elseif ( is_wp_error( $result ) ) {
				// translators: Error message.
				wordpoints_show_admin_error( sprintf( esc_html__( 'Sorry, there was an error while trying to activate the license: %s', 'wordpoints' ), $result->get_error_message() ) );
			} elseif ( ! $license->is_valid() ) {
				wordpoints_show_admin_error( esc_html__( 'That license key is invalid.', 'wordpoints' ) );
			} elseif ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ExpirableI && $license->is_expired() ) {
				if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_RenewableI && $license->is_renewable() ) {
					if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_Renewable_URLI ) {
						wordpoints_show_admin_error(
							esc_html__( 'Sorry, that license key is expired, and must be renewed.', 'wordpoints' )
							. ' <a href="' . esc_url( $license->get_renewal_url() ) . '">' . esc_html__( 'Renew License', 'wordpoints' ) . '</a>'
						);
					} else {
						wordpoints_show_admin_error( esc_html__( 'Sorry, that license key is expired, and must be renewed.', 'wordpoints' ) );
					}
				} else {
					wordpoints_show_admin_error( esc_html__( 'Sorry, that license key is expired.', 'wordpoints' ) );
				}

				$extension_data->set( 'license_key', $license_key );
			} else {
				wordpoints_show_admin_error( esc_html__( 'Sorry, that license key cannot be activated.', 'wordpoints' ) );
			}

		} elseif (
			isset(
				$_POST[ "deactivate-license-{$extension['ID']}" ]
				, $_POST[ "wordpoints_deactivate_license_key-{$extension['ID']}" ]
			)
			&& wordpoints_verify_nonce(
				"wordpoints_deactivate_license_key-{$extension['ID']}"
				, "wordpoints_deactivate_license_key-{$extension['ID']}"
				, null
				, 'post'
			)
		) {

			if ( ! $license instanceof WordPoints_Extension_Server_API_Extension_License_DeactivatableI ) {
				continue;
			}

			$result = $license->deactivate();

			if ( true === $result ) {
				wordpoints_show_admin_message( esc_html__( 'License deactivated.', 'wordpoints' ) );
			} elseif ( is_wp_error( $result ) ) {
				// translators: Error message.
				wordpoints_show_admin_error( sprintf( esc_html__( 'Sorry, there was an error while trying to deactivate the license: %s', 'wordpoints' ), $result->get_error_message() ) );
			} else {
				wordpoints_show_admin_error( esc_html__( 'Sorry, there was an unknown error while trying to deactivate that license key.', 'wordpoints' ) );
			}

		} // End if ( activating license ) elseif ( deactivating license ).

	} // End foreach ( extension ).

	return $extensions;
}

/**
 * Filter the classes for a row in the WordPoints extensions list table.
 *
 * @since 2.4.0
 *
 * @WordPress\filter wordpoints_module_list_row_class
 *
 * @param string $classes        The HTML classes for this extension row.
 * @param string $extension_file The extension file.
 * @param array  $extension_data The extension data.
 *
 * @return string The filtered classes.
 */
function wordpoints_extension_list_row_license_classes( $classes, $extension_file, $extension_data ) {

	// Add license information if this user is allowed to see it.
	if ( empty( $extension_data['ID'] ) || ! current_user_can( 'update_wordpoints_extensions' ) ) {
		return $classes;
	}

	$server = wordpoints_get_server_for_extension( $extension_data );

	if ( ! $server ) {
		return $classes;
	}

	$api = $server->get_api();

	if ( ! $api instanceof WordPoints_Extension_Server_API_LicensesI ) {
		return $classes;
	}

	$extension_data = new WordPoints_Extension_Server_API_Extension_Data(
		$extension_data['ID'],
		$server
	);

	if ( ! $api->extension_requires_license( $extension_data ) ) {
		return $classes;
	}

	$classes .= ' wordpoints-extension-has-license';

	$license = $api->get_extension_license_object(
		$extension_data,
		$extension_data->get( 'license_key' )
	);

	if ( $license->is_valid() ) {
		$classes .= ' wordpoints-extension-license-valid';
	} else {
		$classes .= ' wordpoints-extension-license-invalid';
	}

	if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ActivatableI ) {
		if ( $license->is_active() ) {
			$classes .= ' wordpoints-extension-license-active';
		} else {
			$classes .= ' wordpoints-extension-license-inactive';
		}
	}

	if (
		$license instanceof WordPoints_Extension_Server_API_Extension_License_ExpirableI
		&& $license->is_expired()
	) {
		$classes .= ' wordpoints-extension-license-expired';
	}

	return $classes;
}

/**
 * Add the license key rows to the extensions list table.
 *
 * @since 2.4.0
 *
 * @WordPress\action wordpoints_after_module_row
 */
function wordpoints_extension_license_row( $extension_file, $extension ) {

	if ( empty( $extension['ID'] ) || ! current_user_can( 'update_wordpoints_extensions' ) ) {
		return;
	}

	$server = wordpoints_get_server_for_extension( $extension );

	if ( ! $server ) {
		return;
	}

	$api = $server->get_api();

	if ( ! $api instanceof WordPoints_Extension_Server_API_LicensesI ) {
		return;
	}

	$extension_id = $extension['ID'];

	$extension_data = new WordPoints_Extension_Server_API_Extension_Data(
		$extension_id
		, $server
	);

	if ( ! $api->extension_requires_license( $extension_data ) ) {
		return;
	}

	$license_key = $extension_data->get( 'license_key' );
	$license     = $api->get_extension_license_object( $extension_data, $license_key );
	$server_url  = sanitize_title_with_dashes( $server->get_slug() );

	$notice_type = 'error';

	if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ActivatableI ) {
		if ( ! empty( $license_key ) && $license->is_active() ) {
			$notice_type = 'success';
		} elseif ( empty( $license_key ) || $license->is_activatable() ) {
			$notice_type = 'error';
		}
	}

	if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ExpirableI ) {
		if ( ! empty( $license_key ) && $license->is_expired() ) {
			if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_RenewableI ) {
				if ( $license->is_renewable() ) {
					$notice_type = 'warning';
				}
			}
		}
	}

	// translators: Extension name.
	$aria_label = __( 'License key for %s', 'wordpoints' );

	?>
	<tr class="wordpoints-extension-license-tr plugin-update-tr <?php echo ( is_wordpoints_module_active( $extension_file ) ) ? 'active' : 'inactive'; ?>">
		<td colspan="<?php echo (int) WordPoints_Admin_List_Table_Extensions::instance()->get_column_count(); ?>" class="colspanchange">
			<div class="wordpoints-license-box notice inline notice-alt notice-<?php echo esc_attr( $notice_type ); ?>">
				<p>
					<label class="description" for="license_key-<?php echo esc_attr( $server_url ); ?>-<?php echo esc_attr( $extension_id ); ?>" aria-label="<?php echo esc_attr( sprintf( $aria_label, $extension['name'] ) ); ?>">
						<?php esc_html_e( 'License key:', 'wordpoints' ); ?>
					</label>
					<input
						id="license_key-<?php echo esc_attr( $server_url ); ?>-<?php echo esc_attr( $extension_id ); ?>"
						name="license_key-<?php echo esc_attr( $server_url ); ?>-<?php echo esc_attr( $extension_id ); ?>"
						type="password"
						class="regular-text"
						autocomplete="off"
						value="<?php echo esc_attr( $license_key ); ?>"
					/>
					<?php if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ActivatableI ) : ?>
						<?php if ( ! empty( $license_key ) && $license->is_active() ) : ?>
							<?php if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_DeactivatableI && $license->is_deactivatable() ) : ?>
								<?php

								wp_nonce_field( "wordpoints_deactivate_license_key-{$extension_id}", "wordpoints_deactivate_license_key-{$extension_id}" );

								// translators: Extension name.
								$aria_label = __( 'Deactivate License for %s', 'wordpoints' );

								?>
								<input type="submit" name="deactivate-license-<?php echo esc_attr( $extension_id ); ?>" class="button" value="<?php esc_attr_e( 'Deactivate License', 'wordpoints' ); ?>" aria-label="<?php echo esc_attr( sprintf( $aria_label, $extension['name'] ) ); ?>" />
							<?php endif; ?>
						<?php elseif ( empty( $license_key ) || $license->is_activatable() ) : ?>
							<?php

							wp_nonce_field( "wordpoints_activate_license_key-{$extension_id}", "wordpoints_activate_license_key-{$extension_id}" );

							// translators: Extension name.
							$aria_label = __( 'Activate License for %s', 'wordpoints' );

							?>
							<input type="submit" name="activate-license-<?php echo esc_attr( $extension_id ); ?>" class="button" value="<?php esc_attr_e( 'Activate License', 'wordpoints' ); ?>" aria-label="<?php echo esc_attr( sprintf( $aria_label, $extension['name'] ) ); ?>" />
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ExpirableI ) : ?>
						<?php if ( ! empty( $license_key ) && $license->is_expired() ) : ?>
							<?php if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_RenewableI && $license->is_renewable() ) : ?>
								<?php esc_html_e( 'This license key is expired and must be renewed.', 'wordpoints' ); ?>
								<?php if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_Renewable_URLI ) : ?>
									<?php

									// translators: Extension name.
									$aria_label = __( 'Renew License for %s', 'wordpoints' );

									?>
									<a href="<?php echo esc_url( $license->get_renewal_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( $aria_label, $extension['name'] ) ); ?>"><?php esc_html_e( 'Renew License', 'wordpoints' ); ?></a>
								<?php endif; ?>
							<?php else : ?>
								<?php esc_html_e( 'This license key is expired.', 'wordpoints' ); ?>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				</p>
			</div>
		</td>
	</tr>
	<?php
}

/**
 * Displays the changelog for an extension.
 *
 * @since 2.4.0
 */
function wordpoints_iframe_extension_changelog() {

	if ( ! defined( 'IFRAME_REQUEST' ) ) {
		define( 'IFRAME_REQUEST', true ); // WPCS: prefix OK.
	}

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to update WordPoints extensions for this site.', 'wordpoints' ), 403 );
	}

	if ( empty( $_GET['extension'] ) ) { // WPCS: CSRF OK.
		wp_die( esc_html__( 'No extension supplied.', 'wordpoints' ), 200 );
	}

	$extension_file = sanitize_text_field( rawurldecode( wp_unslash( $_GET['extension'] ) ) ); // WPCS: CSRF, sanitization OK.

	$extensions = wordpoints_get_modules();

	if ( ! isset( $extensions[ $extension_file ] ) ) {
		wp_die( esc_html__( 'That extension does not exist.', 'wordpoints' ), 200 );
	}

	$server = wordpoints_get_server_for_extension( $extensions[ $extension_file ] );

	if ( ! $server ) {
		wp_die( esc_html__( 'There is no server specified for this extension.', 'wordpoints' ), 200 );
	}

	$api = $server->get_api();

	if ( ! $api instanceof WordPoints_Extension_Server_API_Updates_ChangelogI ) {
		wp_die( esc_html__( 'The server for this extension uses an unsupported API.', 'wordpoints' ), 200 );
	}

	$extension_data = new WordPoints_Extension_Server_API_Extension_Data(
		$extensions[ $extension_file ]['ID']
		, $server
	);

	iframe_header();

	echo '<div>';
	echo wp_kses(
		$api->get_extension_changelog( $extension_data )
		, 'wordpoints_extension_changelog'
	);
	echo '</div>';

	iframe_footer();
}

/**
 * Supply the list of HTML tags allowed in an extension changelog.
 *
 * @since 2.4.0
 *
 * @WordPress\filter wp_kses_allowed_html
 */
function wordpoints_extension_changelog_allowed_html( $allowed_tags, $context ) {

	if ( 'wordpoints_extension_changelog' !== $context ) {
		return $allowed_tags;
	}

	return array(
		'a'       => array( 'href' => array(), 'title' => array(), 'target' => array() ),
		'abbr'    => array( 'title' => array() ),
		'acronym' => array( 'title' => array() ),
		'code'    => array(),
		'pre'     => array(),
		'em'      => array(),
		'strong'  => array(),
		'div'     => array( 'class' => array() ),
		'span'    => array( 'class' => array() ),
		'p'       => array(),
		'ul'      => array(),
		'ol'      => array(),
		'li'      => array(),
		'h1'      => array(),
		'h2'      => array(),
		'h3'      => array(),
		'h4'      => array(),
		'h5'      => array(),
		'h6'      => array(),
		'img'     => array( 'src' => array(), 'class' => array(), 'alt' => array() ),
	);
}

/**
 * List the available extension updates on the Updates screen.
 *
 * @since 2.4.0
 */
function wordpoints_list_extension_updates() {

	wp_enqueue_style( 'wordpoints-admin-extension-updates-table' );

	$updates      = wordpoints_get_extension_updates();
	$new_versions = $updates->get_new_versions();

	?>

	<h2><?php esc_html_e( 'WordPoints Extensions', 'wordpoints' ); ?></h2>

	<?php if ( empty( $new_versions ) ) : ?>
		<p><?php esc_html_e( 'Your extensions are all up to date.', 'wordpoints' ); ?></p>
		<?php return; // @codingStandardsIgnoreLine ?>
	<?php endif; ?>

	<p><?php esc_html_e( 'The following extensions have new versions available. Check the ones you want to update and then click &#8220;Update Extensions&#8221;.', 'wordpoints' ); ?></p>

	<form method="post" action="update-core.php?action=do-wordpoints-extension-upgrade" name="upgrade-wordpoints-extensions" class="upgrade">
		<?php wp_nonce_field( 'bulk-wordpoints-extensions', 'nonce' ); ?>

		<p><input id="upgrade-wordpoints-extensions" class="button" type="submit" value="<?php esc_attr_e( 'Update Extensions', 'wordpoints' ); ?>" name="upgrade" /></p>

		<table class="widefat" id="update-wordpoints-extensions-table">
			<thead>
			<tr>
				<td scope="col" class="manage-column check-column">
					<input type="checkbox" id="wordpoints-extensions-select-all" />
				</td>
				<th scope="col" class="manage-column">
					<label for="wordpoints-extensions-select-all"><?php esc_html_e( 'Select All', 'wordpoints' ); ?></label>
				</th>
			</tr>
			</thead>

			<tbody class="wordpoints-extensions">
			<?php foreach ( $new_versions as $extension_file => $new_version ) : ?>
				<?php $extension_data = wordpoints_get_module_data( wordpoints_extensions_dir() . $extension_file ); ?>
				<tr>
					<th scope="row" class="check-column">
						<input id="checkbox_<?php echo esc_attr( sanitize_key( $extension_file ) ); ?>" type="checkbox" name="checked[]" value="<?php echo esc_attr( $extension_file ); ?>" />
						<label for="checkbox_<?php echo esc_attr( sanitize_key( $extension_file ) ); ?>" class="screen-reader-text">
							<?php

							echo esc_html(
								sprintf(
									// translators: Extension name.
									__( 'Select %s', 'wordpoints' )
									, $extension_data['name']
								)
							);

							?>
						</label>
					</th>
					<td>
						<p>
							<strong><?php echo esc_html( $extension_data['name'] ); ?></strong>
							<br />
							<?php

							echo esc_html(
								sprintf(
									// translators: 1. Installed version number; 2. Update version number.
									__( 'You have version %1$s installed. Update to %2$s.', 'wordpoints' )
									, $extension_data['version']
									, $new_version
								)
							);

							?>
							<a href="<?php echo esc_url( self_admin_url( 'update.php?action=wordpoints-iframe-extension-changelog&extension=' . rawurlencode( $extension_file ) . '&TB_iframe=true&width=640&height=662' ) ); ?>" class="thickbox" title="<?php echo esc_attr( $extension_data['name'] ); ?>">
								<?php

								echo esc_html(
									sprintf(
										// translators: Version number.
										__( 'View version %1$s details.', 'wordpoints' )
										, $new_version
									)
								);

								?>
							</a>
						</p>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>

			<tfoot>
			<tr>
				<td scope="col" class="manage-column check-column">
					<input type="checkbox" id="wordpoints-extensions-select-all-2" />
				</td>
				<th scope="col" class="manage-column">
					<label for="wordpoints-extensions-select-all-2"><?php esc_html_e( 'Select All', 'wordpoints' ); ?></label>
				</th>
			</tr>
			</tfoot>
		</table>
		<p><input id="upgrade-wordpoints-extensions-2" class="button" type="submit" value="<?php esc_attr_e( 'Update Extensions', 'wordpoints' ); ?>" name="upgrade" /></p>
	</form>

	<?php
}

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
 * @WordPress\action upgrader_source_selection See above for more info.
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

				$files = glob( $working_directory . '*.php' );

				if ( is_array( $files ) ) {

					// Check if the folder contains a module.
					foreach ( $files as $file ) {

						$info = wordpoints_get_module_data( $file, false, false );

						if ( ! empty( $info['name'] ) ) {
							$source = new WP_Error(
								'wordpoints_module_archive_not_plugin'
								, $source->get_error_message()
								, __( 'This appears to be a WordPoints extension archive. Try installing it on the WordPoints extension install screen instead.', 'wordpoints' )
							);

							break;
						}
					}
				}
			}
		}

		unset( $_source );

	} // End if ( ! isset( $_source ) ) else.

	return $source;
}

/**
 * Displays notices to admins when extension licenses are invalid, expired, etc.
 *
 * @since 2.4.0
 *
 * @WordPress\action admin_notices
 */
function wordpoints_admin_show_extension_license_notices() {

	// Don't show them on the extensions screen, because they would be shown before
	// license activation notices, etc.
	if ( isset( $_GET['page'] ) && 'wordpoints_extensions' === $_GET['page'] ) { // WPCS: CSRF OK.
		return;
	}

	if ( ! current_user_can( 'update_wordpoints_extensions' ) ) {
		return;
	}

	foreach ( wordpoints_get_modules() as $extension ) {

		if ( empty( $extension['ID'] ) ) {
			continue;
		}

		$server = wordpoints_get_server_for_extension( $extension );

		if ( ! $server ) {
			continue;
		}

		$api = $server->get_api();

		if ( ! $api instanceof WordPoints_Extension_Server_API_LicensesI ) {
			continue;
		}

		$extension_data = new WordPoints_Extension_Server_API_Extension_Data(
			$extension['ID']
			, $server
		);

		if ( ! $api->extension_requires_license( $extension_data ) ) {
			continue;
		}

		$license_key = $extension_data->get( 'license_key' );

		if ( empty( $license_key ) ) {

			wordpoints_show_admin_error(
				sprintf(
					// translators: Extension name.
					esc_html__( 'Please fill in your license key for the %s extension for WordPoints, so that you can receive updates.', 'wordpoints' )
					, $extension['name']
				)
				. ' <a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_extensions' ) ) . '">' . esc_html__( 'WordPoints Extensions screen &raquo;', 'wordpoints' ) . '</a>'
			);

			continue;
		}

		$license = $api->get_extension_license_object( $extension_data, $license_key );

		if ( ! $license->is_valid() ) {

			wordpoints_show_admin_error(
				sprintf(
					// translators: Extension name.
					esc_html__( 'Your license key for the %s extension for WordPoints appears to be invalid. Please enter a valid license key so that you can receive updates.', 'wordpoints' )
					, $extension['name']
				)
				. ' <a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_extensions' ) ) . '">' . esc_html__( 'WordPoints Extensions screen &raquo;', 'wordpoints' ) . '</a>'
			);

		} elseif ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ExpirableI && $license->is_expired() ) {

			if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_RenewableI && $license->is_renewable() ) {

				if ( $license instanceof WordPoints_Extension_Server_API_Extension_License_Renewable_URLI ) {

					wordpoints_show_admin_error(
						sprintf(
							// translators: Extension name.
							esc_html__( 'Your license key for the %s extension for WordPoints is expired. Please renew your license key so that you can receive updates.', 'wordpoints' )
							, $extension['name']
						)
						. ' <a href="' . esc_url( $license->get_renewal_url() ) . '">' . esc_html__( 'Renew License', 'wordpoints' ) . '</a>'
					);

				} else {

					wordpoints_show_admin_error(
						sprintf(
							// translators: Extension name.
							esc_html__( 'Your license key for the %s extension for WordPoints is expired. Please renew your license key so that you can receive updates.', 'wordpoints' )
							, $extension['name']
						)
						. ' <a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_extensions' ) ) . '">' . esc_html__( 'WordPoints Extensions screen &raquo;', 'wordpoints' ) . '</a>'
					);
				}

			} else {

				wordpoints_show_admin_error(
					sprintf(
						// translators: Extension name.
						esc_html__( 'Your license key for the %s extension for WordPoints is expired. Please enter a valid license key so that you can receive updates.', 'wordpoints' )
						, $extension['name']
					)
					. ' <a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_extensions' ) ) . '">' . esc_html__( 'WordPoints Extensions screen &raquo;', 'wordpoints' ) . '</a>'
				);
			}

		} elseif ( $license instanceof WordPoints_Extension_Server_API_Extension_License_ActivatableI && $license->is_activatable() && ! $license->is_active() ) {

			$extension_id = $extension['ID'];
			$server_url   = sanitize_title_with_dashes( $server->get_slug() );

			// translators: Extension name.
			$aria_label = __( 'Activate License for %s WordPoints Extension', 'wordpoints' );

			?>
			<div class="notice notice-error">
				<p>
					<?php

					echo esc_html(
						sprintf(
							// translators: Extension name.
							__( 'Your license key for the %s extension for WordPoints is not active. Please activate it so that you can receive updates.', 'wordpoints' )
							, $extension['name']
						)
					);

					?>
				</p>
				<form method="post" action="<?php echo esc_url( self_admin_url( 'admin.php?page=wordpoints_extensions' ) ); ?>">
					<input
						id="license_key-<?php echo esc_attr( $server_url ); ?>-<?php echo esc_attr( $extension_id ); ?>"
						name="license_key-<?php echo esc_attr( $server_url ); ?>-<?php echo esc_attr( $extension_id ); ?>"
						type="hidden"
						class="regular-text"
						autocomplete="off"
						value="<?php echo esc_attr( $license_key ); ?>"
					/>
					<?php wp_nonce_field( "wordpoints_activate_license_key-{$extension_id}", "wordpoints_activate_license_key-{$extension_id}" ); ?>
					<p>
						<input
							type="submit"
							name="activate-license-<?php echo esc_attr( $extension_id ); ?>"
							class="button"
							value="<?php esc_attr_e( 'Activate License', 'wordpoints' ); ?>"
							aria-label="<?php echo esc_attr( sprintf( $aria_label, $extension_data['name'] ) ); ?>"
						/>
					</p>
				</form>
			</div>
			<?php
		}
	}
}

// EOF
