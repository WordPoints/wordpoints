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

	WordPoints_Installables::register(
		'plugin'
		, 'wordpoints'
		, array(
			'version'      => WORDPOINTS_VERSION,
			'un_installer' => WORDPOINTS_DIR . '/includes/class-un-installer.php',
			'network_wide' => is_wordpoints_network_active(),
		)
	);
}
add_action( 'plugins_loaded', 'wordpoints_register_installer' );

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

	// The installer won't be registered yet.
	wordpoints_register_installer();

	WordPoints_Installables::install( 'plugin', 'wordpoints', $network_active );
}
register_activation_hook( __FILE__, 'wordpoints_activate' );

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

	// The major version is determined by the first number, so we can just cast to
	// an integer. IF the major versions are equal, we don't need to do anything.
	if ( (int) WORDPOINTS_VERSION === (int) $wordpoints_data['version'] ) {
		return;
	}

	/**
	 * The base un/installer class.
	 *
	 * @since 2.0.0
	 */
	require_once( WORDPOINTS_DIR . '/includes/class-un-installer-base.php' );

	/**
	 * The breaking updater.
	 *
	 * @since 2.0.0
	 */
	require_once( WORDPOINTS_DIR . '/includes/class-breaking-updater.php' );

	$updater = new WordPoints_Breaking_Updater( 'wordpoints_breaking', WORDPOINTS_VERSION );
	$updater->update();
}
add_action( 'plugins_loaded', 'wordpoints_breaking_update' );

/**
 * Prints the random string stored in the database.
 *
 * @since 2.0.0
 *
 * @WordPress\action shutdown Only when checking module compatibility during a
 *                            breaking update.
 */
function wordpoints_maintenance_shutdown_print_rand_str() {

	if ( ! isset( $_GET['wordpoints_module_check'] ) ) {
		return;
	}

	if ( is_network_admin() ) {
		$nonce = get_site_option( 'wordpoints_module_check_nonce' );
	} else {
		$nonce = get_option( 'wordpoints_module_check_nonce' );
	}

	if ( ! $nonce || ! hash_equals( $nonce, sanitize_key( $_GET['wordpoints_module_check'] ) ) ) {
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
 * @param array $modules The active modules.
 *
 * @return array The modules whose compatibility is being checked.
 */
function wordpoints_maintenance_filter_modules( $modules ) {

	if ( ! isset( $_GET['check_module'], $_GET['wordpoints_module_check'] ) ) {
		return $modules;
	}

	if ( is_network_admin() ) {
		$nonce = get_site_option( 'wordpoints_module_check_nonce' );
	} else {
		$nonce = get_option( 'wordpoints_module_check_nonce' );
	}

	if ( ! $nonce || ! hash_equals( $nonce, sanitize_key( $_GET['wordpoints_module_check'] ) ) ) {
		return $modules;
	}

	$modules = explode(
		','
		, sanitize_text_field( wp_unslash( $_GET['check_module'] ) )
	);

	if ( 'pre_site_option_wordpoints_sitewide_active_modules' === current_filter() ) {
		$modules = array_flip( $modules );
	}

	return $modules;
}

if ( isset( $_GET['wordpoints_module_check'], $_GET['check_module'] ) ) {

	add_action( 'shutdown', 'wordpoints_maintenance_shutdown_print_rand_str' );

	if ( is_network_admin() ) {
		$filter = 'pre_site_option_wordpoints_sitewide_active_modules';
	} else {
		$filter = 'pre_option_wordpoints_active_modules';
	}

	add_filter( $filter, 'wordpoints_maintenance_filter_modules' );
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

		case 'integer': break;

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
 * in absint() {@link http://codex.wordpress.org/Function_Reference/absint} because
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
 * a better bootstrap for verifing an nonce request. In addition, it supplies better
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
		$request = $_GET;
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
			$value = wordpoints_get_network_option( $option, array() );
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
 *
 * @param string $option  The name of the option to get.
 * @param mixed  $default A default value to return if the option isn't found.
 *
 * @return mixed The option value if it exists, or $default (false by default).
 */
function wordpoints_get_network_option( $option, $default = false ) {

	if ( is_wordpoints_network_active() ) {
		return get_site_option( $option, $default );
	} else {
		return get_option( $option, $default );
	}
}

/**
 * Add an option or site option, based on the plugin's activation status.
 *
 * If the plugin is network activated on a multisite install, this will add a
 * network ('site') option. Otherwise it will create a regular option.
 *
 * @since 1.2.0
 *
 * @param string $option   The name of the option to add.
 * @param mixed  $value    The value for the option.
 * @param string $autoload Whether to automaticaly load the option. 'yes' (default)
 *                         or 'no'. Does not apply if WordPoints is network active.
 *
 * @return bool Whether the option was added successfully.
 */
function wordpoints_add_network_option( $option, $value, $autoload = 'yes' ) {

	if ( is_wordpoints_network_active() ) {
		return add_site_option( $option, $value );
	} else {
		return add_option( $option, $value, '', $autoload );
	}
}

/**
 * Update an option or site option, based on the plugin's activation status.
 *
 * If the plugin is network activated on a multisite install, this will update a
 * network ('site') option. Otherwise it will update a regular option.
 *
 * @since 1.2.0
 *
 * @param string $option The name of the option to update.
 * @param mixed  $value  The new value for the option.
 *
 * @return bool Whether the option was updated successfully.
 */
function wordpoints_update_network_option( $option, $value ) {

	if ( is_wordpoints_network_active() ) {
		return update_site_option( $option, $value );
	} else {
		return update_option( $option, $value );
	}
}

/**
 * Delete an option or site option, based on the plugin's activation status.
 *
 * If the plugin is network activated on a multisite install, this will delete a
 * network ('site') option. Otherwise it will delete a regular option.
 *
 * @since 1.2.0
 *
 * @param string $option The name of the option to delete.
 *
 * @return bool Whether the option was successfully deleted.
 */
function wordpoints_delete_network_option( $option ) {

	if ( is_wordpoints_network_active() ) {
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

	if ( ! in_array( $format, $formats ) ) {

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

	return $wpdb->prepare( $in, $_in );
}

//
// Form Helpers.
//

/**
 * Helper for building form dropdowns (<select> elements).
 *
 * @since 1.0.0
 */
class WordPoints_Dropdown_Builder {

	/**
	 * The options for the dropdown.
	 *
	 * @since 1.0.0
	 *
	 * @type array $options
	 */
	private $options;

	/**
	 * The arguments for dropdown display.
	 *
	 * @since 1.0.0
	 *
	 * @see WordPoints_Dropdown_Builder::__construct() For a description of the
	 *      arguments.
	 *
	 * @type array $args
	 */
	private $args = array(
		'selected'         => '',
		'id'               => '',
		'name'             => '',
		'class'            => '',
		'show_option_none' => '',
	);

	/**
	 * Construct the class with the options and arguments.
	 *
	 * The options may be an associative array with the option values as keys and the
	 * options' text as values. It can also be an array of arrays or objects, where
	 * the options' text and value are elements of the array. To specify the key for
	 * the values and option text, use the 'values_key' and 'options_key' arguments,
	 * respectively.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 The $options argument may now be passed as an array of arrays or
	 *              objects, and the 'options_key' and 'values_key' args were added.
	 *
	 * @param (string|array|object)[] $options The options for the <select> element.
	 * @param array                   $args    {
	 *        Arguments for the display of the select element.
	 *
	 *        @type string $selected         The value selected. Default is none.
	 *        @type string $id               The value for the id attribute of the
	 *                                       element. The default is empty.
	 *        @type string $name             The value for the name attribute of the
	 *                                       element. The default is empty.
	 *        @type string $class            The value for the class attribute of the
	 *                                       element. The default is empty.
	 *        @type string $show_option_none The text for a "none" option. The value
	 *                                       will always be '-1'. Default is empty
	 *                                       (does not show an option).
	 *        @type string $options_key      The key for the option name if each
	 *                                       option is an array.
	 *        @type string $values_key       The key for the option value if each
	 *                                       option is an array or object.
	 * }
	 */
	public function __construct( array $options, array $args ) {

		$this->args = array_merge( $this->args, $args );

		$this->options = $options;
	}

	/**
	 * Display the dropdown.
	 *
	 * @since 1.0.0
	 */
	public function display() {

		$this->start();
		$this->options();
		$this->end();
	}

	/**
	 * Output the start of the <select> element.
	 *
	 * @since 1.0.0
	 */
	public function start() {

		echo '<select id="', esc_attr( $this->args['id'] ), '" name="', esc_attr( $this->args['name'] ), '" class="', esc_attr( $this->args['class'] ), '">';
	}

	/**
	 * Output the <option>s.
	 *
	 * @since 1.0.0
	 */
	public function options() {

		if ( ! empty( $this->args['show_option_none'] ) ) {

			echo '<option value="-1"', selected( '-1', $this->args['selected'], false ), '>', esc_html( $this->args['show_option_none'] ), '</option>';
		}

		foreach ( $this->options as $value => $option ) {

			if ( isset( $this->args['values_key'], $this->args['options_key'] ) ) {
				$option = (array) $option;
				$value = $option[ $this->args['values_key'] ];
				$option = $option[ $this->args['options_key'] ];
			}

			echo '<option value="', esc_attr( $value ), '"', selected( $value, $this->args['selected'], false ), '>', esc_html( $option ), '</option>';
		}
	}

	/**
	 * Output the end of the <select> element.
	 *
	 * @since 1.0.0
	 */
	public function end() {

		echo '</select>';
	}
}

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

	$user_ids = wordpoints_get_array_option( 'wordpoints_excluded_users', 'network' );

	/**
	 * Excluded users option.
	 *
	 * This option is set on the Configure > Settings administration panel.
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
		'install_wordpoints_modules'        => 'install_plugins',
		'manage_network_wordpoints_modules' => 'manage_network_plugins',
		'activate_wordpoints_modules'       => 'activate_plugins',
		'delete_wordpoints_modules'         => 'delete_plugins',
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

	global $wp_roles;

	if ( ! $wp_roles instanceof WP_Roles ) {
		$wp_roles = new WP_Roles;
	}

	foreach ( $wp_roles->role_objects as $role ) {

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

	global $wp_roles;

	if ( ! $wp_roles instanceof WP_Roles ) {
		$wp_roles = new WP_Roles;
	}

	foreach ( $wp_roles->role_objects as $role ) {
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
 * @filter map_meta_cap
 *
 * @param array  $caps    The user's capabilities.
 * @param string $cap     The current capability in question.
 * @param int    $user_id The ID of the user whose caps are being checked.
 *
 * @return array The user's capabilities.
 */
function wordpoints_map_custom_meta_caps( $caps, $cap, $user_id ) {

	switch ( $cap ) {
		case 'install_wordpoints_modules':
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
add_filter( 'map_meta_cap', 'wordpoints_map_custom_meta_caps', 10, 3 );

/**
 * Register the points component.
 *
 * @since 1.0.0
 *
 * @action wordpoints_components_register
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
			'author_uri'    => 'http://wordpoints.org/',
			'component_uri' => 'http://wordpoints.org/',
			'description'   => __( 'Enables a points system for your site.', 'wordpoints' ),
			'file'          => WORDPOINTS_DIR . 'components/points/points.php',
			'un_installer'  => WORDPOINTS_DIR . 'components/points/includes/class-un-installer.php',
		)
	);
}
add_action( 'wordpoints_components_register', 'wordpoints_points_component_register' );

/**
 * Register the ranks component.
 *
 * @since 1.7.0
 *
 * @action wordpoints_components_register
 */
function wordpoints_ranks_component_register() {

	wordpoints_component_register(
		array(
			'slug'           => 'ranks',
			'name'           => _x( 'Ranks', 'component name', 'wordpoints' ),
			'version'        => WORDPOINTS_VERSION,
			'author'         => _x( 'WordPoints', 'component author', 'wordpoints' ),
			'author_uri'     => 'http://wordpoints.org/',
			'component_uri'  => 'http://wordpoints.org/',
			'description'    => __( 'Assign users ranks based on their points levels.', 'wordpoints' ),
			'file'           => WORDPOINTS_DIR . 'components/ranks/ranks.php',
			'un_installer'   => WORDPOINTS_DIR . 'components/ranks/includes/class-un-installer.php',
		)
	);
}
add_action( 'wordpoints_components_register', 'wordpoints_ranks_component_register' );

/**
 * Initialize the plugin's cache groups.
 *
 * @since 1.10.0
 */
function wordpoints_init_cache_groups() {

	if ( function_exists( 'wp_cache_add_non_persistent_groups' ) ) {
		wp_cache_add_non_persistent_groups( array( 'wordpoints_modules' ) );
	}
}
add_action( 'init', 'wordpoints_init_cache_groups', 5 );

/**
 * Register scripts and styles.
 *
 * It is run on both the front and back end with a priority of 5, so the scripts will
 * all be registered when we want to enqueue them, usually on the default priority of
 * 10.
 *
 * @since 1.0.0
 *
 * @action wp_enqueue_scripts    5 Front-end scripts enqueued.
 * @action admin_enqueue_scripts 5 Admin scripts enqueued.
 */
function wordpoints_register_scripts() {}
add_action( 'wp_enqueue_scripts', 'wordpoints_register_scripts', 5 );
add_action( 'admin_enqueue_scripts', 'wordpoints_register_scripts', 5 );

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

// EOF
