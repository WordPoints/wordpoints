<?php

/**
 * Functions related to the PHP version requirement checks for updates to the plugin.
 *
 * @package WordPoints\Admin
 * @since   2.5.0
 */

/**
 * Get the PHP version required for an update for the plugin.
 *
 * @since 2.3.0
 *
 * @return string|false The PHP version number, or false if no requirement could be
 *                      determined. The version may be in x.y or x.y.z format.
 */
function wordpoints_admin_get_php_version_required_for_update() {

	$plugin_basename = plugin_basename( WORDPOINTS_DIR . '/wordpoints.php' );

	// We store this as a special field on the update plugins transient. That way it
	// is cached, and we don't need to worry about keeping the cache in sync with
	// this transient.
	$updates = get_site_transient( 'update_plugins' );

	if ( ! isset( $updates->response[ $plugin_basename ] ) ) {
		return false;
	}

	if ( ! isset( $updates->response[ $plugin_basename ]->wordpoints_required_php ) ) {

		/**
		 * The plugin install functions.
		 *
		 * @since 2.3.0
		 */
		require_once ABSPATH . '/wp-admin/includes/plugin-install.php';

		$info = plugins_api(
			'plugin_information'
			, array(
				'slug'   => 'wordpoints',
				// We need to use the default locale in case the pattern we need to
				// search for would have gotten lost in translation.
				'locale' => 'en_US',
			)
		);

		if ( is_wp_error( $info ) ) {
			return false;
		}

		preg_match(
			'/requires php (\d+\.\d+(?:\.\d)?)/i'
			, $info->sections['description']
			, $matches
		);

		$version = false;

		if ( ! empty( $matches[1] ) ) {
			$version = $matches[1];
		}

		$updates->response[ $plugin_basename ]->wordpoints_required_php = $version;

		set_site_transient( 'update_plugins', $updates );

	} // End if ( PHP requirements not in cache ).

	return $updates->response[ $plugin_basename ]->wordpoints_required_php;
}

/**
 * Checks if the PHP version meets the requirements of the next WordPoints update.
 *
 * @since 2.3.0
 *
 * @return bool Whether the PHP version meets the requirements of the next update.
 */
function wordpoints_admin_is_running_php_version_required_for_update() {

	$required_version = wordpoints_admin_get_php_version_required_for_update();

	// If there is no required version, then the requirement is met.
	if ( ! $required_version ) {
		return true;
	}

	return version_compare( PHP_VERSION, $required_version, '>=' );
}

/**
 * Replaces the update notice with an error message when PHP requirements aren't met.
 *
 * Normally WordPress displays an update notice row in the plugins list table on the
 * Plugins screen. However, if the next version of WordPoints requires a greater PHP
 * version than is currently in use, we replace that row with an error message
 * informing the user of the situation instead.
 *
 * @since 2.3.0
 *
 * @WordPress\action load-plugins.php
 */
function wordpoints_admin_maybe_disable_update_row_for_php_version_requirement() {

	if ( wordpoints_admin_is_running_php_version_required_for_update() ) {
		return;
	}

	$plugin_basename = plugin_basename( WORDPOINTS_DIR . '/wordpoints.php' );

	// Remove the default update row function.
	remove_action( "after_plugin_row_{$plugin_basename}", 'wp_plugin_update_row', 10 );

	// And add a custom function of our own to output an error message.
	add_action(
		"after_plugin_row_{$plugin_basename}"
		, 'wordpoints_admin_not_running_php_version_required_for_update_plugin_row'
		, 10
		, 2
	);
}

/**
 * Outputs an error row for an update requiring a greater PHP version than is in use.
 *
 * This is used to replace the default update notice row that WordPress displays in
 * the plugins table if an update for WordPoints requires a greater version of PHP
 * than the site is currently running. This prevents the user from being able to
 * update, and informs them of the situation so that they can take action to update
 * their version of PHP.
 *
 * @since 2.3.0
 *
 * @WordPress\action after_plugin_row_wordpoints/wordpoints.php
 *
 * @param string $file        Plugin basename.
 * @param array  $plugin_data Plugin data, as returned by the plugins API.
 */
function wordpoints_admin_not_running_php_version_required_for_update_plugin_row(
	$file,
	$plugin_data
) {

	if ( is_multisite() && ! is_network_admin() ) {
		return;
	}

	// First check that there is actually an update available.
	$updates = get_site_transient( 'update_plugins' );

	if ( ! isset( $updates->response[ $file ] ) ) {
		return;
	}

	$response = $updates->response[ $file ];

	$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );

	if ( is_network_admin() ) {
		$active_class = is_plugin_active_for_network( $file ) ? ' active' : '';
	} else {
		$active_class = is_plugin_active( $file ) ? ' active' : '';
	}

	?>

	<tr
		class="plugin-update-tr <?php echo esc_attr( $active_class ); ?>"
		id="<?php echo esc_attr( $response->slug . '-update' ); ?>"
		data-slug="<?php echo esc_attr( $response->slug ); ?>"
		data-plugin="<?php echo esc_attr( $file ); ?>"
	>
		<td
			colspan="<?php echo esc_attr( $wp_list_table->get_column_count() ); ?>"
			class="plugin-update colspanchange"
		>
			<div class="update-message inline notice notice-error notice-alt">
				<p>
					<?php esc_html_e( 'A WordPoints update is available, but your system is not compatible because it is running an outdated version of PHP.', 'wordpoints' ); ?>
					<?php

					echo wp_kses(
						sprintf(
							// translators: URL of WordPoints PHP Compatibility docs.
							__( 'See <a href="%s">the WordPoints user guide</a> for more information.', 'wordpoints' )
							, 'https://wordpoints.org/user-guide/php-compatibility/'
						)
						, array( 'a' => array( 'href' => true ) )
					);

					?>
				</p>
			</div>
		</td>
	</tr>

	<?php
}

/**
 * Prevents updates when they require a greater PHP version than is in use.
 *
 * @since 2.5.0
 *
 * @WordPress\filter upgrader_pre_install
 *
 * @param WP_Error|true $result     Whether or not to install the package.
 * @param array         $hook_extra Information about the package being installed.
 *
 * @return WP_Error|true Whether or not to install the package.
 */
function wordpoints_admin_maybe_prevent_plugin_updates( $result, $hook_extra ) {

	$plugin_basename = plugin_basename( WORDPOINTS_DIR . '/wordpoints.php' );

	if (
		isset( $hook_extra['plugin'] )
		&& $plugin_basename === $hook_extra['plugin']
		&& ! wordpoints_admin_is_running_php_version_required_for_update()
	) {

		$message  = esc_html__( 'Your system is not compatible with this WordPoints update because it is running an outdated version of PHP.', 'wordpoints' );
		$message .= ' ';
		$message .= sprintf(
			// translators: URL of WordPoints PHP Compatibility docs.
			__( 'See the WordPoints user guide for more information: %s', 'wordpoints' )
			, 'https://wordpoints.org/user-guide/php-compatibility/'
		);

		$result = new WP_Error(
			'wordpoints_php_version_incompatible'
			, $message
		);
	}

	return $result;
}

/**
 * Hides the plugin on the Updates screen if the PHP version requirements aren't met.
 *
 * On the Dashboard » Updates screen, WordPress displays a table of the available
 * plugin updates. This function will prevent an update for WordPoints form being
 * displayed in that table, if the PHP version requirements for that update are not
 * met by the site.
 *
 * It is also used to hide the "Install Update Now" button in the plugin information
 * dialog.
 *
 * @since 2.3.0
 *
 * @WordPress\action load-update-core.php
 * @WordPress\action install_plugins_pre_plugin-information
 */
function wordpoints_admin_maybe_remove_from_updates_screen() {

	if ( wordpoints_admin_is_running_php_version_required_for_update() ) {
		return;
	}

	// Add filter to remove WordPoints from the update plugins list.
	add_filter(
		'site_transient_update_plugins'
		, 'wordpoints_admin_remove_wordpoints_from_update_plugins_transient'
	);
}

/**
 * Filter callback to remove WordPoints from the update plugins list.
 *
 * @since 2.3.0
 *
 * @WordPress\filter site_transient_update_plugins
 *                   Added by wordpoints_admin_maybe_remove_from_updates_screen().
 *
 * @param object $data Object of plugin update data.
 *
 * @return object The filtered object.
 */
function wordpoints_admin_remove_wordpoints_from_update_plugins_transient( $data ) {

	$plugin_basename = plugin_basename( WORDPOINTS_DIR . '/wordpoints.php' );

	if ( isset( $data->response[ $plugin_basename ] ) ) {
		unset( $data->response[ $plugin_basename ] );
	}

	return $data;
}

// EOF
