<?php

/**
 * Shortcodes class.
 *
 * @package WordPoints
 * @since   2.3.0
 */

/**
 * Container for the plugin's shortcodes.
 *
 * This class was created just to be a container for a list of the available
 * WordPoints shortcodes and the classes which handle each of them. The handler
 * classes for each shortcode need to extend the WordPoints_Shortcode class.
 *
 * Note that the classes aren't stored as objects, but just the class names. The
 * classes are then instantiated for each instance of the shortcode which they
 * generate the content for.
 *
 * @since 1.8.0
 */
final class WordPoints_Shortcodes {

	/**
	 * The registered shortcodes and handler classes.
	 *
	 * This is a list of shortcode handler class names, indexed by slugs of the
	 * shortcodes that they each handle, respectively.
	 *
	 * @since 1.8.0
	 *
	 * @type string[] $shortcodes
	 */
	private static $shortcodes;

	/**
	 * Register a shortcode handler class.
	 *
	 * Note that in the case that a shortcode is already registered, the new handler
	 * class will replace the previous one.
	 *
	 * @since 1.8.0
	 *
	 * @param string $shortcode The shortcode to register a handler class for.
	 * @param string $class     The handler class for this shortcode.
	 */
	public static function register( $shortcode, $class ) {

		self::$shortcodes[ $shortcode ] = $class;

		add_shortcode( $shortcode, array( __CLASS__, 'do_shortcode' ) );
	}

	/**
	 * Get the handler for a shortcode instance.
	 *
	 * This function may be used to retrieve all registered shortcodes, or a handler
	 * object for a specific shortcode instance.
	 *
	 * @since 1.8.0
	 *
	 * @param mixed  $shortcode The shortcode to get. If omitted, all handlers are
	 *                          returned.
	 * @param array  $atts      The attributes of the shortcode. Only used if
	 *                          $shortcode is passed.
	 * @param string $content   The content of the shortcode. Only used if $shortcode
	 *                          is passed.
	 *
	 * @return array|WordPoints_Shortcode|false All of the shortcode handler classes,
	 *                                          or a handler, or false if $shortcode
	 *                                          is invalid.
	 */
	public static function get( $shortcode = null, $atts = null, $content = null ) {

		if ( isset( $shortcode ) ) {

			if ( isset( self::$shortcodes[ $shortcode ] ) ) {
				return new self::$shortcodes[ $shortcode ]( $atts, $content, $shortcode );
			} else {
				return false;
			}
		}

		return self::$shortcodes;
	}

	/**
	 * Execute a shortcode.
	 *
	 * This method is automatically registered with add_shortcode() when a new
	 * shortcode class is registered. It can also be used independently, instead of
	 * an expensive call to the do_shortcode() function.
	 *
	 * If there is an attempt to do a shortcode that was not registered with this
	 * class an error message will be returned, if appropriate.
	 *
	 * @since 1.8.0
	 *
	 * @param array  $atts      The attributes of the shortcode.
	 * @param string $content   The shortcode's contents.
	 * @param string $shortcode The name of the shortcode to execute.
	 *
	 * @return string The content generated for this shortcode.
	 */
	public static function do_shortcode( $atts, $content, $shortcode ) {

		if ( ! isset( self::$shortcodes[ $shortcode ] ) ) {
			return wordpoints_shortcode_error(
				sprintf(
					// translators: Shortcode name.
					esc_html__( 'The %s shortcode was not registered properly.', 'wordpoints' )
					, '<code>[' . esc_html( $shortcode ) . ']</code>'
				)
			);
		}

		/** @var WordPoints_Shortcode $shortcode */
		$shortcode = new self::$shortcodes[ $shortcode ]( $atts, $content, $shortcode );
		return $shortcode->expand();
	}
}

// EOF
