<?php

/**
 * Wildcards options uninstaller class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Uninstaller for options with names matching a pattern containing wildcards.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Options_Wildcards implements WordPoints_RoutineI {

	/**
	 * The option name pattern containing wildcards.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $option_name_pattern;

	/**
	 * @since 2.4.0
	 *
	 * @param string $option_name_pattern The option name pattern containing wildcards.
	 */
	public function __construct( $option_name_pattern ) {

		$this->option_name_pattern = $option_name_pattern;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		global $wpdb;

		$options = $wpdb->get_col(
			$wpdb->prepare(
				"
					SELECT `option_name`
					FROM `{$wpdb->options}`
					WHERE `option_name` LIKE %s
				"
				, $this->option_name_pattern
			)
		); // WPCS: cache pass.

		array_map( 'delete_option', $options );
	}
}

// EOF
