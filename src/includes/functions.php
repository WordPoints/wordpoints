<?php

/**
 * WordPoints Common Functions.
 *
 * These are general functions that are here to be available to all components,
 * modules, and plugins.
 *
 * @package WordPoints
 * @since 1.0.0
 */

/**
 * Register the installer for WordPoints.
 *
 * @since 2.0.0
 *
 * @WordPress\action plugins_loaded
 */
function wordpoints_register_installer() {

	wordpoints_apps()->get_sub_app( 'installables' )->register(
		'plugin'
		, 'wordpoints'
		, 'WordPoints_Installable_Core'
		, WORDPOINTS_VERSION
		, is_wordpoints_network_active()
	);
}

/**
 * Install the plugin on activation.
 *
 * @since 1.0.0
 *
 * @WordPress\action activate_wordpoints/wordpoints.php
 *
 * @param bool $network_active Whether the plugin is being network activated.
 */
function wordpoints_activate( $network_active ) {

	$filter_func = ( $network_active ) ? '__return_true' : '__return_false';
	add_filter( 'is_wordpoints_network_active', $filter_func );

	// Check if the plugin has been activated/installed before.
	$installed = (bool) wordpoints_get_maybe_network_option( 'wordpoints_data' );

	$installer = new WordPoints_Installer(
		new WordPoints_Installable_Core()
		, $network_active
	);

	$installer->run();

	// Activate the Points component, if this is the first activation.
	if ( false === $installed ) {
		$wordpoints_components = WordPoints_Components::instance();
		$wordpoints_components->load();
		$wordpoints_components->activate( 'points' );
	}

	remove_filter( 'is_wordpoints_network_active', $filter_func );
}

/**
 * Performs actions when the plugin is deactivated.
 *
 * @since 2.4.0
 *
 * @WordPress\action deactivate_wordpoints/wordpoints.php
 */
function wordpoints_deactivate() {

	wp_clear_scheduled_hook( 'wordpoints_check_for_extension_updates' );
}

/**
 * Check module compatibility before breaking updates.
 *
 * @since 2.0.0
 *
 * @WordPress\action plugins_loaded
 */
function wordpoints_breaking_update() {

	if ( is_wordpoints_network_active() ) {
		$wordpoints_data = get_site_option( 'wordpoints_data' );
	} else {
		$wordpoints_data = get_option( 'wordpoints_data' );
	}

	// Normally this should never happen, because the plugins_loaded action won't
	// run until the next request after WordPoints is activated and installs itself.
	if ( empty( $wordpoints_data['version'] ) ) {
		return;
	}

	// The major version is determined by the first number, so we can just cast to
	// an integer. IF the major versions are equal, we don't need to do anything.
	if ( (int) WORDPOINTS_VERSION === (int) $wordpoints_data['version'] ) {
		return;
	}

	$updater = new WordPoints_Updater_Core_Breaking();
	$updater->run();
}

/**
 * Prints the random string stored in the database.
 *
 * @since 2.0.0
 *
 * @WordPress\action shutdown Only when checking module compatibility during a
 *                            breaking update.
 */
function wordpoints_maintenance_shutdown_print_rand_str() {

	if ( ! isset( $_GET['wordpoints_module_check'] ) ) { // WPCS: CSRF OK.
		return;
	}

	if ( is_network_admin() ) {
		$nonce = get_site_option( 'wordpoints_module_check_nonce' );
	} else {
		$nonce = get_option( 'wordpoints_module_check_nonce' );
	}

	if ( ! $nonce || ! hash_equals( $nonce, sanitize_key( $_GET['wordpoints_module_check'] ) ) ) { // WPCS: CSRF OK.
		return;
	}

	if ( is_network_admin() ) {
		$rand_str = get_site_option( 'wordpoints_module_check_rand_str' );
	} else {
		$rand_str = get_option( 'wordpoints_module_check_rand_str' );
	}

	echo esc_html( $rand_str );
}

/**
 * Filters the modules when checking compatibility during a breaking update.
 *
 * @since 2.0.0
 *
 * @WordPress\filter pre_option_wordpoints_active_modules Only when checking module
 *                   compatibility during a breaking update.
 * @WordPress\filter pre_site_option_wordpoints_sitewide_active_modules Only when
 *                   checking module compatibility during a breaking update. Only in
 *                   the network admin.
 *
 * @param array $modules The active modules.
 *
 * @return array The modules whose compatibility is being checked.
 */
function wordpoints_maintenance_filter_modules( $modules ) {

	if ( ! isset( $_GET['check_module'], $_GET['wordpoints_module_check'] ) ) { // WPCS: CSRF OK.
		return $modules;
	}

	if ( is_network_admin() ) {
		$nonce = get_site_option( 'wordpoints_module_check_nonce' );
	} else {
		$nonce = get_option( 'wordpoints_module_check_nonce' );
	}

	if ( ! $nonce || ! hash_equals( $nonce, sanitize_key( $_GET['wordpoints_module_check'] ) ) ) { // WPCS: CSRF OK.
		return $modules;
	}

	$modules = explode(
		','
		, sanitize_text_field( wp_unslash( $_GET['check_module'] ) ) // WPCS: CSRF OK.
	);

	if ( 'pre_site_option_wordpoints_sitewide_active_modules' === current_filter() ) {
		$modules = array_flip( $modules );
	}

	return $modules;
}

/**
 * Whether WordPoints is uninstalling something.
 *
 * @since 2.4.0
 *
 * @return bool Whether an uninstall routine is being run.
 */
function wordpoints_is_uninstalling() {

	$uninstalling = class_exists( 'WordPoints_Uninstaller', false );

	/**
	 * Filters whether WordPoints is running an uninstall routine.
	 *
	 * @since 2.4.0
	 *
	 * @param bool $uninstalling Whether or not we are uninstalling something.
	 */
	return (bool) apply_filters( 'wordpoints_is_uninstalling', $uninstalling );
}

//
// Sanitizing Functions.
//

/**
 * Convert a value to an integer.
 *
 * False is returned if $maybe_int is not an integer, or a float or string whose
 * integer value is equal to its non-integer value (for example, 1.00 or '10', as
 * opposed to 1.3 [evaluates to int 1] or '10 piggies' [10]). This is to ensure that
 * there was intention behind the value.
 *
 * @since 1.0.0
 *
 * @see wordpoints_posint() Convert a value to a positive integer.
 * @see wordpoints_negint() Convert a value to a negative integer.
 *
 * @param mixed $maybe_int The value to convert to an integer.
 *
 * @return int|false False if $maybe_int couldn't reliably be converted to an integer.
 */
function wordpoints_int( &$maybe_int ) {

	$type = gettype( $maybe_int );

	switch ( $type ) {

		case 'integer':
			break;

		case 'string':
			if ( (string) (int) $maybe_int === $maybe_int ) {
				$maybe_int = (int) $maybe_int;
			} else {
				$maybe_int = false;
			}
		break;

		case 'double':
			if ( (float) (int) $maybe_int === $maybe_int ) {
				$maybe_int = (int) $maybe_int;
			} else {
				$maybe_int = false;
			}
		break;

		default:
			$maybe_int = false;
	}

	return $maybe_int;
}

/**
 * Convert a value to a positive integer.
 *
 * If the value is negative, false is returned. I prefer this approach over that used
 * in absint() {@link https://codex.wordpress.org/Function_Reference/absint} because
 * sometimes if you are expecting a positive value and you get a negative value
 * instead, it is unlikely that the absolute value was intended. In many cases we
 * don't want to assume that the absolute value was intended, because if it wasn't
 * there could be big consequences. In many of these cases returning false is much
 * safer.
 *
 * Note that 0 is unsigned, so it is not considered positive by this function.
 *
 * The value is passed by reference, so you can keep things short and sweet.
 *
 * @since 1.0.0
 *
 * @uses wordpoints_int()
 *
 * @param mixed $maybe_int The value to convert to a positive integer.
 *
 * @return int
 */
function wordpoints_posint( &$maybe_int ) {

	$maybe_int = ( wordpoints_int( $maybe_int ) > 0 ) ? $maybe_int : false;
	return $maybe_int;
}

/**
 * Convert a value to a negative integer (0 exclusive).
 *
 * @since 1.0.0
 *
 * @uses wordpoints_int()
 * @see wordpoints_posint()
 *
 * @param mixed $maybe_int The value to convert to an integer.
 *
 * @return int
 */
function wordpoints_negint( &$maybe_int ) {

	$maybe_int = ( wordpoints_int( $maybe_int ) < 0 ) ? $maybe_int : false;
	return $maybe_int;
}

/**
 * Verify an nonce.
 *
 * This is a wrapper for the wp_verify_nonce() function. It was introduced to provide
 * a better bootstrap for verifying an nonce request. In addition, it supplies better
 * input validation and sanitization. Sanitizing the inputs before passing them in to
 * wp_verify_nonce() is especially important, because that function is pluggable, and
 * it is impossible to know how it might be implemented.
 *
 * The possible return values are also certain, which isn't so for wp_verify_nonce(),
 * due to it being pluggable.
 *
 * @since 1.9.0
 * @since 2.0.0 $format_values now accepts a string when there is only one value.
 *
 * @see wp_verify_nonce()
 *
 * @param string          $nonce_key     The key for the nonce in the request parameters array.
 * @param string          $action_format A sprintf()-style format string for the nonce action.
 * @param string|string[] $format_values The keys of the request values to use to format the action.
 * @param string          $request_type  The request array to use, 'get' ($_GET) or 'post' ($_POST).
 *
 * @return int|false Returns 1 or 2 on success, false on failure.
 */
function wordpoints_verify_nonce(
	$nonce_key,
	$action_format,
	$format_values = null,
	$request_type = 'get'
) {

	if ( 'post' === $request_type ) {
		$request = $_POST; // WPCS: CSRF OK.
	} else {
		$request = $_GET; // WPCS: CSRF OK.
	}

	if ( ! isset( $request[ $nonce_key ] ) ) {
		return false;
	}

	if ( ! empty( $format_values ) ) {

		$values = array();

		foreach ( (array) $format_values as $value ) {

			if ( ! isset( $request[ $value ] ) ) {
				return false;
			}

			$values[] = $request[ $value ];
		}

		$action_format = vsprintf( $action_format, $values );
	}

	$is_valid = wp_verify_nonce(
		sanitize_key( $request[ $nonce_key ] )
		, strip_tags( $action_format )
	);

	if ( 1 === $is_valid || 2 === $is_valid ) {
		return $is_valid;
	}

	return false;
}

/**
 * Sanitize a WP_Error object for passing directly to wp_die().
 *
 * The wp_die() function accepts an WP_Error object as the first parameter, but it
 * does not sanitize it's contents before printing it out to the user. By passing
 * the object through this function before giving it to wp_die(), the potential for
 * XSS should be avoided.
 *
 * Example:
 *
 * wp_die( wordpoints_sanitize_wp_error( $error ) );
 *
 * @since 1.10.0
 *
 * @param WP_Error $error The error to sanitize.
 *
 * @return WP_Error The sanitized error.
 */
function wordpoints_sanitize_wp_error( $error ) {

	$code = $error->get_error_code();

	$error_data = $error->error_data;

	if ( isset( $error_data[ $code ]['title'] ) ) {

		$error_data[ $code ]['title'] = wp_kses(
			$error->error_data[ $code ]['title']
			, 'wordpoints_sanitize_wp_error_title'
		);

		$error->error_data = $error_data;
	}

	$all_errors = $error->errors;

	foreach ( $all_errors as $code => $errors ) {

		foreach ( $errors as $key => $message ) {
			$all_errors[ $code ][ $key ] = wp_kses(
				$message
				, 'wordpoints_sanitize_wp_error_message'
			);
		}
	}

	$error->errors = $all_errors;

	return $error;
}

/**
 * Escape a MySQL identifier.
 *
 * Quotes the identifier with backticks and escapes any backticks within it by
 * doubling them.
 *
 * @since 2.1.0
 *
 * @link https://dev.mysql.com/doc/refman/5.7/en/identifiers.html#idm139700789409120
 *
 * @param string $identifier The identifier (column, table, alias, etc.).
 *
 * @return string The escaped identifier. Already quoted, do not place within
 *                backticks.
 */
function wordpoints_escape_mysql_identifier( $identifier ) {
	return '`' . str_replace( '`', '``', $identifier ) . '`';
}

//
// Database Helpers.
//

/**
 * Get an option that must be an array.
 *
 * This is a wrapper for get_option() that will force the return value to be an
 * array.
 *
 * @since 1.0.0
 * @since 1.1.0 The $context parameter was added for site options.
 * @since 1.2.0 The 'network' context was added.
 * @since 2.1.0 The 'network' context was deprecated.
 *
 * @param string $option The name of the option to get.
 * @param string $context The context for the option. Use 'site' to get site options.
 *
 * @return array The option value if it is an array, or an empty array if not.
 */
function wordpoints_get_array_option( $option, $context = 'default' ) {

	switch ( $context ) {

		case 'default':
			$value = get_option( $option, array() );
		break;

		case 'site':
			$value = get_site_option( $option, array() );
		break;

		case 'network':
			_deprecated_argument(
				__FUNCTION__
				, '2.1.0'
				, 'Using "network" as the value of $context is deprecated, use wordpoints_get_maybe_network_array_option() instead.'
			);

			$value = wordpoints_get_maybe_network_option( $option, array() );
		break;

		default:
			_doing_it_wrong( __FUNCTION__, sprintf( 'Unknown option context "%s"', esc_html( $context ) ), '1.2.0' );
			$value = array();
	}

	if ( ! is_array( $value ) ) {
		$value = array();
	}

	return $value;
}

/**
 * Get an option or site option from the database, based on the plugin's status.
 *
 * If the plugin is network activated on a multisite install, this will return a
 * network ('site') option. Otherwise it will return a regular option.
 *
 * @since 1.2.0
 * @deprecated 2.1.0 Use wordpoints_get_maybe_network_option() instead.
 *
 * @param string $option  The name of the option to get.
 * @param mixed  $default A default value to return if the option isn't found.
 *
 * @return mixed The option value if it exists, or $default (false by default).
 */
function wordpoints_get_network_option( $option, $default = false ) {

	_deprecated_function(
		__FUNCTION__
		, '2.1.0'
		, 'wordpoints_get_maybe_network_option()'
	);

	return wordpoints_get_maybe_network_option( $option, null, $default );
}

/**
 * Add an option or site option, based on the plugin's activation status.
 *
 * If the plugin is network activated on a multisite install, this will add a
 * network ('site') option. Otherwise it will create a regular option.
 *
 * @since 1.2.0
 * @deprecated 2.1.0 Use wordpoints_add_maybe_network_option() instead.
 *
 * @param string $option   The name of the option to add.
 * @param mixed  $value    The value for the option.
 * @param string $autoload Whether to automatically load the option. 'yes' (default)
 *                         or 'no'. Does not apply if WordPoints is network active.
 *
 * @return bool Whether the option was added successfully.
 */
function wordpoints_add_network_option( $option, $value, $autoload = 'yes' ) {

	_deprecated_function(
		__FUNCTION__
		, '2.1.0'
		, 'wordpoints_add_maybe_network_option()'
	);

	return wordpoints_add_maybe_network_option( $option, $value, null, $autoload );
}

/**
 * Update an option or site option, based on the plugin's activation status.
 *
 * If the plugin is network activated on a multisite install, this will update a
 * network ('site') option. Otherwise it will update a regular option.
 *
 * @since 1.2.0
 * @deprecated 2.1.0 Use wordpoints_update_maybe_network_option() instead.
 *
 * @param string $option The name of the option to update.
 * @param mixed  $value  The new value for the option.
 *
 * @return bool Whether the option was updated successfully.
 */
function wordpoints_update_network_option( $option, $value ) {

	_deprecated_function(
		__FUNCTION__
		, '2.1.0'
		, 'wordpoints_update_maybe_network_option()'
	);

	return wordpoints_update_maybe_network_option( $option, $value );
}

/**
 * Delete an option or site option, based on the plugin's activation status.
 *
 * If the plugin is network activated on a multisite install, this will delete a
 * network ('site') option. Otherwise it will delete a regular option.
 *
 * @since 1.2.0
 * @deprecated 2.1.0 Use wordpoints_delete_maybe_network_option() instead.
 *
 * @param string $option The name of the option to delete.
 *
 * @return bool Whether the option was successfully deleted.
 */
function wordpoints_delete_network_option( $option ) {

	_deprecated_function(
		__FUNCTION__
		, '2.1.0'
		, 'wordpoints_delete_maybe_network_option()'
	);

	return wordpoints_delete_maybe_network_option( $option );
}

/**
 * Get an option or network option that must be an array.
 *
 * This is a wrapper for {@see wordpoints_get_maybe_network_option()} that will force
 * the return value to be an array.
 *
 * @since 2.1.0
 *
 * @param string $option The name of the option to get.
 * @param bool   $network Whether to retrieve a network option, or a regular option.
 *                        By default a regular option will be retrieved, unless
 *                        WordPoints is network active, in which case a network
 *                        option will be retrieved.
 *
 * @return array The option value if it is an array, or an empty array if not.
 */
function wordpoints_get_maybe_network_array_option( $option, $network = null ) {

	$value = wordpoints_get_maybe_network_option( $option, $network );

	if ( ! is_array( $value ) ) {
		$value = array();
	}

	return $value;
}

/**
 * Get an option or network option from the database.
 *
 * @since 2.1.0
 *
 * @param string $option  The name of the option to get.
 * @param bool   $network Whether to retrieve a network option, or a regular option.
 *                        By default a regular option will be retrieved, unless
 *                        WordPoints is network active, in which case a network
 *                        option will be retrieved.
 * @param mixed  $default A default value to return if the option isn't found.
 *
 * @return mixed The option value if it exists, or $default (false by default).
 */
function wordpoints_get_maybe_network_option( $option, $network = null, $default = false ) {

	if ( $network || ( null === $network && is_wordpoints_network_active() ) ) {
		return get_site_option( $option, $default );
	} else {
		return get_option( $option, $default );
	}
}

/**
 * Add an option or network option.
 *
 * @since 2.1.0
 *
 * @param string $option   The name of the option to add.
 * @param mixed  $value    The value for the option.
 * @param bool   $network  Whether to add a network option, or a regular option. By
 *                         default a regular option will be added, unless WordPoints
 *                         is network active, in which case a network option will be
 *                         added.
 * @param string $autoload Whether to automatically load the option. 'yes' (default)
 *                         or 'no'. Does not apply if WordPoints is network active.
 *
 * @return bool Whether the option was added successfully.
 */
function wordpoints_add_maybe_network_option( $option, $value, $network = null, $autoload = 'yes' ) {

	if ( $network || ( null === $network && is_wordpoints_network_active() ) ) {
		return add_site_option( $option, $value );
	} else {
		return add_option( $option, $value, '', $autoload );
	}
}

/**
 * Update an option or network option.
 *
 * @since 2.1.0
 *
 * @param string $option  The name of the option to update.
 * @param mixed  $value   The new value for the option.
 * @param bool   $network Whether to update a network option, or a regular option.
 *                        By default a regular option will be updated, unless
 *                        WordPoints is network active, in which case a network
 *                        option will be updated.
 *
 * @return bool Whether the option was updated successfully.
 */
function wordpoints_update_maybe_network_option( $option, $value, $network = null ) {

	if ( $network || ( null === $network && is_wordpoints_network_active() ) ) {
		return update_site_option( $option, $value );
	} else {
		return update_option( $option, $value );
	}
}

/**
 * Delete an option or network option.
 *
 * @since 2.1.0
 *
 * @param string $option  The name of the option to delete.
 * @param bool   $network Whether to delete a network option, or a regular option.
 *                        By default a regular option will be deleted, unless
 *                        WordPoints is network active, in which case a network
 *                        option will be deleted.
 *
 * @return bool Whether the option was successfully deleted.
 */
function wordpoints_delete_maybe_network_option( $option, $network = null ) {

	if ( $network || ( null === $network && is_wordpoints_network_active() ) ) {
		return delete_site_option( $option );
	} else {
		return delete_option( $option );
	}
}

/**
 * Prepare an IN or NOT IN condition for a database query.
 *
 * Example return: "'foo','bar','blah'".
 *
 * @since 1.0.0
 *
 * @uses wpdb::prepare() To prepare the query.
 *
 * @param array  $_in    The values that the column must be IN (or NOT IN).
 * @param string $format The format for the values in $_in: '%s', '%d', or '%f'.
 *
 * @return string|false The comma-delimited string of values. False on failure.
 */
function wordpoints_prepare__in( $_in, $format = '%s' ) {

	global $wpdb;

	// Validate $format.
	$formats = array( '%d', '%s', '%f' );

	if ( ! in_array( $format, $formats, true ) ) {

		$format = esc_html( $format );
		_doing_it_wrong( __FUNCTION__, esc_html( "WordPoints Debug Error: invalid format '{$format}', allowed values are %s, %d, and %f" ), '1.0.0' );

		$format = '%s';
	}

	$_in = array_unique( $_in );

	$count = count( $_in );

	if ( 0 === $count ) {

		_doing_it_wrong( __FUNCTION__, 'WordPoints Debug Error: empty array passed as first parameter', '1.0.0' );

		return false;
	}

	// String a bunch of format signs together, one for each value in $_in.
	$in = $format . str_repeat( ",{$format}", $count - 1 );

	return $wpdb->prepare( $in, $_in ); // WPCS: unprepared SQL OK
}

//
// Form Helpers.
//

/**
 * Display a <select> form element filled with a list of post types.
 *
 * The value of the 'selected' option is 'ALL' by default, which corresponds to the
 * 'Any' option ('ALL' is an invalid post type name).
 *
 * @since 1.0.0
 * @since 1.5.1 The 'filter' option was added.
 *
 * @param array $options {
 *        An array of display options.
 *
 *        @type string $name     The value for the name attribute of the element.
 *        @type string $id       The value for the id attribute of the element.
 *        @type string $selected The name of the selected points type.
 *        @type string $class    The value for the class attribute of the element.
 *        @type callable $filter A boolean callback function to filter the post types
 *                               with. It will be passed the post type object.
 * }
 * @param array $args Arguments to pass to get_post_types(). Default is array().
 */
function wordpoints_list_post_types( $options, $args = array() ) {

	$defaults = array(
		'name'     => '',
		'id'       => '',
		'selected' => 'ALL',
		'class'    => '',
		'filter'   => null,
	);

	$options = array_merge( $defaults, $options );

	echo '<select class="' . esc_attr( $options['class'] ) . '" name="' . esc_attr( $options['name'] ) . '" id="' . esc_attr( $options['id'] ) . '">';
	echo '<option value="ALL"' . selected( $options['selected'], 'ALL', false ) . '>' . esc_html_x( 'Any', 'post type', 'wordpoints' ) . '</option>';

	foreach ( get_post_types( $args, 'objects' ) as $post_type ) {

		if ( isset( $options['filter'] ) && ! call_user_func( $options['filter'], $post_type ) ) {
			continue;
		}

		echo '<option value="' . esc_attr( $post_type->name ) . '"' . selected( $options['selected'], $post_type->name, false ) . '>' . esc_html( $post_type->label ) . '</option>';
	}

	echo '</select>';
}

//
// Miscellaneous.
//

/**
 * Check if the plugin is network activated.
 *
 * @since 1.2.0
 *
 * @return bool True if WordPoints is network activated, false otherwise.
 */
function is_wordpoints_network_active() {

	if ( wordpoints_is_uninstalling() ) {
		_doing_it_wrong(
			__FUNCTION__
			, 'You can\'t check activation status during uninstall.'
			, '2.4.0'
		);
	}

	require_once ABSPATH . '/wp-admin/includes/plugin.php';

	$network_active = is_plugin_active_for_network(
		plugin_basename( WORDPOINTS_DIR . 'wordpoints.php' )
	);

	/**
	 * Filter whether the plugin is network active.
	 *
	 * This is primarily used during install, when the above checks won't work. It's
	 * not really intended for general use.
	 *
	 * @since 1.3.0
	 *
	 * @param bool $network_active Whether WordPoints is network activated.
	 */
	return apply_filters( 'is_wordpoints_network_active', $network_active );
}

/**
 * Retrieve an array of the IDs of excluded users.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'wordpoints_excluded_users' with the array of user IDs
 *       and the $context.
 *
 * @param string $context The context in which the list will be used. Useful in
 *        filtering.
 *
 * @return array An array of user IDs to exclude in the current $context.
 */
function wordpoints_get_excluded_users( $context ) {

	$user_ids = wordpoints_get_maybe_network_array_option(
		'wordpoints_excluded_users'
	);

	/**
	 * Excluded users option.
	 *
	 * This option is set on the Settings » General Settings administration panel.
	 *
	 * @since 1.0.0
	 *
	 * @param int[]  $user_ids The ids of the user's to be excluded.
	 * @param string $context  The context in which the function is being called.
	 */
	return apply_filters( 'wordpoints_excluded_users', $user_ids, $context );
}

/**
 * Give a shortcode error.
 *
 * The error is only displayed to those with the 'manage_options' capability, or if
 * the shortcode is being displayed in a post that the current user can edit.
 *
 * @since 1.0.1
 * @since 1.8.0 The $message now supports WP_Error objects.
 *
 * @param string|WP_Error $message The error message.
 *
 * @return string The error message.
 */
function wordpoints_shortcode_error( $message ) {

	if (
		( get_post() && current_user_can( 'edit_post', get_the_ID() ) )
		|| current_user_can( 'manage_options' )
	) {

		if ( is_wp_error( $message ) ) {
			$message = $message->get_error_message();
		}

		return '<p class="wordpoints-shortcode-error">' . esc_html__( 'Shortcode error:', 'wordpoints' ) . ' ' . wp_kses( $message, 'wordpoints_shortcode_error' ) . '</p>';
	}

	return '';
}

/**
 * Get the custom capabilities.
 *
 * @since 1.3.0
 *
 * @return array The custom capabilities as keys, WP core counterparts as values.
 */
function wordpoints_get_custom_caps() {

	return array(
		'install_wordpoints_extensions'        => 'install_plugins',
		'update_wordpoints_extensions'         => 'update_plugins',
		'manage_network_wordpoints_extensions' => 'manage_network_plugins',
		'activate_wordpoints_extensions'       => 'activate_plugins',
		'delete_wordpoints_extensions'         => 'delete_plugins',
	);
}

/**
 * Add custom capabilities to the desired roles.
 *
 * Used during installation.
 *
 * @since 1.3.0
 *
 * @param array $capabilities The capabilities to add as keys, WP core counterparts
 *                            as values.
 */
function wordpoints_add_custom_caps( $capabilities ) {

	/** @var WP_Role $role */
	foreach ( wp_roles()->role_objects as $role ) {

		foreach ( $capabilities as $custom_cap => $core_cap ) {
			if ( $role->has_cap( $core_cap ) ) {
				$role->add_cap( $custom_cap );
			}
		}
	}
}

/**
 * Remove custom capabilities.
 *
 * Used during uninstallation.
 *
 * @since 1.3.0
 *
 * @param string[] $capabilities The list of capabilities to remove.
 */
function wordpoints_remove_custom_caps( $capabilities ) {

	/** @var WP_Role $role */
	foreach ( wp_roles()->role_objects as $role ) {
		foreach ( $capabilities as $custom_cap ) {
			$role->remove_cap( $custom_cap );
		}
	}
}

/**
 * Map custom meta capabilities.
 *
 * @since 1.4.0
 *
 * @WordPress\filter map_meta_cap
 *
 * @param array  $caps    The user's capabilities.
 * @param string $cap     The current capability in question.
 * @param int    $user_id The ID of the user whose caps are being checked.
 *
 * @return array The user's capabilities.
 */
function wordpoints_map_custom_meta_caps( $caps, $cap, $user_id ) {

	$deprecated = array(
		'install_wordpoints_modules'        => 'install_wordpoints_extensions',
		'manage_network_wordpoints_modules' => 'manage_network_wordpoints_extensions',
		'activate_wordpoints_modules'       => 'activate_wordpoints_extensions',
		'delete_wordpoints_modules'         => 'delete_wordpoints_extensions',
	);

	if ( isset( $deprecated[ $cap ] ) ) {

		_deprecated_argument(
			'current_user_can'
			, '2.4.0'
			, esc_html( "{$cap} is deprecated, use {$deprecated[ $cap ]} instead." )
		);

		$caps[] = $deprecated[ $cap ];
	}

	switch ( $cap ) {
		case 'install_wordpoints_extensions':
		case 'update_wordpoints_extensions':
			if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
				$caps[] = 'do_not_allow';
			} elseif ( is_multisite() && ! is_super_admin( $user_id ) ) {
				$caps[] = 'do_not_allow';
			} else {
				$caps[] = $cap;
			}
		break;
	}

	return $caps;
}

/**
 * Component registration wrapper.
 *
 * @since 1.0.0
 *
 * @uses WordPoints_Components::register()
 *
 * @param array $args The component args.
 *
 * @return bool Whether the component was registered.
 */
function wordpoints_component_register( $args ) {

	return WordPoints_Components::instance()->register( $args );
}

/**
 * Component activation check wrapper.
 *
 * @since 1.0.0
 *
 * @uses WordPoints_Components::is_active()
 *
 * @param string $slug The component slug.
 *
 * @return bool Whether the component is active.
 */
function wordpoints_component_is_active( $slug ) {

	return WordPoints_Components::instance()->is_active( $slug );
}

/**
 * Register the points component.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_components_register
 *
 * @uses wordpoints_component_register()
 */
function wordpoints_points_component_register() {

	wordpoints_component_register(
		array(
			'slug'          => 'points',
			'name'          => _x( 'Points', 'component name', 'wordpoints' ),
			'version'       => WORDPOINTS_VERSION,
			'author'        => _x( 'WordPoints', 'component author', 'wordpoints' ),
			'author_uri'    => 'https://wordpoints.org/',
			'component_uri' => 'https://wordpoints.org/',
			'description'   => __( 'Enables a points system for your site.', 'wordpoints' ),
			'file'          => WORDPOINTS_DIR . 'components/points/points.php',
			'installable'   => 'WordPoints_Points_Installable',
		)
	);
}

/**
 * Register the ranks component.
 *
 * @since 1.7.0
 *
 * @WordPress\action wordpoints_components_register
 */
function wordpoints_ranks_component_register() {

	wordpoints_component_register(
		array(
			'slug'          => 'ranks',
			'name'          => _x( 'Ranks', 'component name', 'wordpoints' ),
			'version'       => WORDPOINTS_VERSION,
			'author'        => _x( 'WordPoints', 'component author', 'wordpoints' ),
			'author_uri'    => 'https://wordpoints.org/',
			'component_uri' => 'https://wordpoints.org/',
			'description'   => __( 'Assign users ranks based on their points levels.', 'wordpoints' ),
			'file'          => WORDPOINTS_DIR . 'components/ranks/ranks.php',
			'installable'   => 'WordPoints_Ranks_Installable',
		)
	);
}

/**
 * Initialize the plugin's cache groups.
 *
 * @since 1.10.0
 *
 * @WordPress\action init 5 Earlier than the default so that the groups will be
 *                          registered before any other code runs.
 */
function wordpoints_init_cache_groups() {

	if ( function_exists( 'wp_cache_add_non_persistent_groups' ) ) {
		wp_cache_add_non_persistent_groups( array( 'wordpoints_modules' ) );
	}

	if ( function_exists( 'wp_cache_add_global_groups' ) ) {

		wp_cache_add_global_groups(
			array(
				'wordpoints_hook_periods',
				'wordpoints_hook_period_ids_by_reaction',
			)
		);
	}
}

/**
 * Register scripts and styles.
 *
 * It is run on both the front and back end with a priority of 5, so the scripts will
 * all be registered when we want to enqueue them, usually on the default priority of
 * 10.
 *
 * @since 1.0.0
 *
 * @WordPress\action wp_enqueue_scripts    5 Front-end scripts enqueued.
 * @WordPress\action admin_enqueue_scripts 5 Admin scripts enqueued.
 */
function wordpoints_register_scripts() {}

/**
 * Load the plugin's textdomain.
 *
 * @since 1.1.0
 *
 * @WordPress\action plugins_loaded
 */
function wordpoints_load_textdomain() {

	load_plugin_textdomain( 'wordpoints', false, plugin_basename( WORDPOINTS_DIR ) . '/languages/' );
}

/**
 * Generate a cryptographically secure hash.
 *
 * You can use wp_hash() instead if you need to use a salt. However, at present that
 * function uses the outdated MD5 hashing algorithm, so you will want to take that
 * into consideration as well. The benefit of using this function instead is that it
 * will use a strong hashing algorithm, and the hashes won't be invalidated when the
 * salts change.
 *
 * @since 2.0.1
 *
 * @param string $data The data to generate a hash for.
 *
 * @return string The hash.
 */
function wordpoints_hash( $data ) {
	return hash( 'sha256', $data );
}

/**
 * Construct a class with a variable number of args.
 *
 * @since 2.1.0
 * @since 2.4.0 Up to 5 args are now accepted, instead of just 4.
 *
 * @param string $class_name The name of the class to construct.
 * @param array  $args       Up to 5 args to pass to the constructor.
 *
 * @return object|false The constructed object, or false if to many args were passed.
 */
function wordpoints_construct_class_with_args( $class_name, array $args ) {

	switch ( count( $args ) ) {
		case 0:
			return new $class_name();
		case 1:
			return new $class_name( $args[0] );
		case 2:
			return new $class_name( $args[0], $args[1] );
		case 3:
			return new $class_name( $args[0], $args[1], $args[2] );
		case 4:
			return new $class_name( $args[0], $args[1], $args[2], $args[3] );
		case 5:
			return new $class_name( $args[0], $args[1], $args[2], $args[3], $args[4] );
		default:
			return false;
	}
}

/**
 * Check if a function is disabled by PHP's `disabled_functions` INI directive.
 *
 * @since 2.2.0
 *
 * @param string $function The name of the function to check.
 *
 * @return bool Whether the function is disabled.
 */
function wordpoints_is_function_disabled( $function ) {

	if ( 'set_time_limit' === $function && ini_get( 'safe_mode' ) ) {
		return true;
	}

	return in_array( $function, explode( ',', ini_get( 'disable_functions' ) ), true );
}

/**
 * Prevents any interruptions from occurring during a lengthy operation.
 *
 * @since 2.4.0
 */
function wordpoints_prevent_interruptions() {

	// Don't stop script execution if the client disconnects.
	ignore_user_abort( true );

	// Don't let the script time-out.
	if ( ! wordpoints_is_function_disabled( 'set_time_limit' ) ) {
		set_time_limit( 0 );
	}

	/**
	 * Fires when preventing interruptions to a lengthy operation.
	 *
	 * @since 2.4.0
	 */
	do_action( 'wordpoints_prevent_interruptions' );
}

/**
 * Maps context shortcuts to their canonical elements.
 *
 * For arrays of elements for particular contexts, some shortcuts are provided.
 * These reduce duplication across the contexts, 'single', 'site', and 'network'.
 * These shortcuts make it possible to define, e.g., an option to be uninstalled
 * on a single site and as a network option on multisite installs, in just a
 * single location, using the 'global' shortcut, rather than having to add it to
 * both the 'single' and 'network' arrays.
 *
 * @since 2.4.0
 *
 * @param array $array The array to map the shortcuts for.
 *
 * @return array The mapped array.
 */
function wordpoints_map_context_shortcuts( array $array ) {

	// shortcut => canonicals
	$map = array(
		'local'     => array( 'single', 'site'  /*  -  */ ),
		'global'    => array( 'single', /* - */ 'network' ),
		'universal' => array( 'single', 'site', 'network' ),
	);

	foreach ( $map as $shortcut => $canonicals ) {

		if ( ! isset( $array[ $shortcut ] ) ) {
			continue;
		}

		foreach ( $canonicals as $canonical ) {

			if ( ! isset( $array[ $canonical ] ) ) {

				$array[ $canonical ] = $array[ $shortcut ];

			} else {

				$array[ $canonical ] = array_merge(
					$array[ $canonical ]
					, $array[ $shortcut ]
				);
			}
		}

		unset( $array[ $shortcut ] );
	}

	return $array;
}

// EOF
