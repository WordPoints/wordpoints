<?php

/**
 * Dropdown builder class.
 *
 * @package WordPoints
 * @since   2.3.0
 */

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
				$value  = $option[ $this->args['values_key'] ];
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

// EOF
