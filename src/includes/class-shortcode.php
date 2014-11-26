<?php

/**
 * Class for handling a shortcode.
 *
 * @package WordPoints
 * @since 1.8.0
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
	 * @param mixed  $shortcode The shortcode to get. If ommitted, all handlers are
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
				return new self::$shortcodes[ $shortcode ]( $atts, $content );
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
	 * shortcode class is registered. It can also be used independantly, instead of
	 * an expensive call to the do_shortcode() function.
	 *
	 * If there is an attempt to do a shortocode that was not registered with this
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
					esc_html__( 'The %s shortcode was not registered properly.', 'wordpoints' )
					, '<code>[' . esc_html( $shortcode ) . ']</code>'
				)
			);
		}

		$shortcode = new self::$shortcodes[ $shortcode ]( $atts, $content );
		return $shortcode->expand();
	}
}

/**
 * Handle content generation for a shortcode.
 *
 * This class is to be extended to represent each shortcode, and provides a bootstrap
 * for parsing their attributes and generating their content. Each instance is
 * created and used for a single instance of the shortcode which it handles.
 *
 * @since 1.8.0
 */
abstract class WordPoints_Shortcode {

	/**
	 * The shortcode handled by this class.
	 *
	 * @since 1.8.0
	 *
	 * @type string $shortcode
	 */
	protected $shortcode;

	/**
	 * The supported attributes.
	 *
	 * A list of default attribute values indexed by the attribute names. Only the
	 * attributes listed here will be parsed, see shortcode_atts().
	 *
	 * When you wish to leave a default value empty, it is best to use an empty
	 * string rather than null. This allows its existence to be checked with isset().
	 * Of course, there may be cases that you want to take advantage of this, such
	 * as when an attribute is optional.
	 *
	 * @since 1.8.0
	 *
	 * @type array $pairs
	 */
	protected $pairs = array();

	/**
	 * The attributes of the shortcode being parsed.
	 *
	 * A list of the attribute values of the current shortcode instance, indexed by
	 * the attribute names. This will contain exactly those attributes listed in
	 * self::$pairs, but with the default values defined there replaced with the
	 * actual attribute values from the shortcode (for those attributes that were
	 * set in this occurance of the shortcode).
	 *
	 * @since 1.8.0
	 *
	 * @type array $atts
	 */
	protected $atts = array();

	/**
	 * The content of the shortcode being parsed.
	 *
	 * For shortcodes which have closing and ending tags, this is the content
	 * between those.
	 *
	 * @since 1.8.0
	 *
	 * @type string $content
	 */
	protected $content;

	/**
	 * Get the name shortcode this class represents.
	 *
	 * @since 1.8.0
	 *
	 * @return string The shortcode name.
	 */
	public function get() {
		return $this->shortcode;
	}

	/**
	 * Construct a new instance of this shortcode.
	 *
	 * @since 1.8.0
	 *
	 * @param array  $atts    The shortcode's attributes.
	 * @param string $content The shortcode's content.
	 */
	public function __construct( $atts, $content ) {
		$this->atts = $atts;
		$this->content = $content;
	}

	/**
	 * Expand the shortcode into the generated content.
	 *
	 * This method verifies the shortcodes attributes, and returns an error message
	 * when appropriate if there is a problem. If there aren't any unrecoverable
	 * errors, then it will return the generated content to replace this shortcode.
	 *
	 * @since 1.8.0
	 *
	 * @return string The expanded content to replace the shortcode.
	 */
	public function expand() {

		$this->atts = shortcode_atts( $this->pairs, $this->atts, $this->shortcode );

		$error = $this->verify_atts();

		if ( ! empty( $error ) ) {
			return wordpoints_shortcode_error( $error );
		}

		return $this->generate();
	}

	/**
	 * Verify the shortcode's attributes.
	 *
	 * You can override this method, but it is recommended that you always return
	 * parent::verify_atts() at the end of your method when you do.
	 *
	 * Automatic handling is provided for the user_id attribute, if it is supported
	 * by a shortcode. If it isn't set it will default to the current user. The user
	 * can also specify the author of the current post by setting the value of this
	 * attribute to post_author. This will result in an error message being displayed
	 * to the appropriate users if the shortcode isn't being used within a post.
	 *
	 * @since 1.8.0
	 *
	 * @return string|WP_Error|null An error message or object on failure.
	 */
	protected function verify_atts() {

		// If this shortcode supports the user_id attribute, verify it.
		if ( isset( $this->pairs['user_id'] ) ) {

			if ( 'post_author' === $this->atts['user_id'] ) {

				$post = get_post();

				if ( ! $post ) {
					return sprintf(
						esc_html__( 'The &#8220;%s&#8221; attribute of the %s shortcode must be used inside of a Post, Page, or other post type.', 'wordpoints' )
						, 'user_id="post_author"'
						, "<code>[{$this->shortcode}]</code>"
					);
				}

				$this->atts['user_id'] = $post->post_author;

			} elseif ( ! wordpoints_posint( $this->atts['user_id'] ) ) {

				$this->atts['user_id'] = get_current_user_id();
			}
		}
	}

	/**
	 * Generate the content to replace the shortcode.
	 *
	 * @since 1.8.0
	 *
	 * @return string The expanded content to replace the shortcode.
	 */
	abstract protected function generate();
}

// EOF
