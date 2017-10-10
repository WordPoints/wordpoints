<?php

/**
 * Shortcode class.
 *
 * @package WordPoints
 * @since 2.3.0
 */

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
	 * set in this occurrence of the shortcode).
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
	 * @since 2.2.0 The $shortcode parameter was added. It is optional for back-
	 *              compat.
	 *
	 * @param array  $atts      The shortcode's attributes.
	 * @param string $content   The shortcode's content.
	 * @param string $shortcode The shortcode's name.
	 */
	public function __construct( $atts, $content, $shortcode = null ) {

		$this->atts    = $atts;
		$this->content = $content;

		if ( isset( $shortcode ) ) {
			$this->shortcode = $shortcode;
		}
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

		/**
		 * Filter the shortcode attribute values supplied by the user.
		 *
		 * This filter is applied before the shortcode atts are verified, but after
		 * they are passed through {@see shortcode_atts()}.
		 *
		 * If you want to hook into just one shortcode, you can use {@see
		 * "shortcode_atts_{$shortcode}"} instead.
		 *
		 * @since 2.1.0
		 *
		 * @param array  $out       The filtered attribute => value pairs.
		 * @param array  $pairs     The list of atts this shortcode supports, and
		 *                          their default values.
		 * @param array  $atts      The attribute => value pairs supplied by the user.
		 * @param string $shortcode The name of the shortcode the attributes are for.
		 */
		$this->atts = apply_filters(
			'wordpoints_user_supplied_shortcode_atts'
			, $this->atts
			, $this->pairs
			, $this->atts
			, $this->shortcode
		);

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
						// translators: 1. Attribute name; 2. Shortcode name.
						esc_html__( 'The &#8220;%1$s&#8221; attribute of the %2$s shortcode must be used inside of a Post, Page, or other post type.', 'wordpoints' )
						, 'user_id="post_author"'
						, "<code>[{$this->shortcode}]</code>"
					);
				}

				$this->atts['user_id'] = $post->post_author;

			} elseif ( ! $this->atts['user_id'] ) {

				$this->atts['user_id'] = get_current_user_id();

			} elseif ( ! wordpoints_posint( $this->atts['user_id'] ) ) {

				return sprintf(
					// translators: 1. Attribute name; 2. Shortcode name; 3. Expected value.
					esc_html__( 'Unrecognized value for the %1$s attribute of the %2$s shortcode. Expected &#8220;%3$s&#8221; or a user ID.', 'wordpoints' )
					, 'user_id'
					, "<code>[{$this->shortcode}]</code>"
					, 'post_author'
				);
			}
		}

		return null;
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
