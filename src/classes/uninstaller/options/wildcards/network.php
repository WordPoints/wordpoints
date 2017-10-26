<?php

/**
 * Wildcards network options uninstaller class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Uninstaller for network options with names matching a pattern containing wildcards.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Options_Wildcards_Network implements WordPoints_RoutineI {

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
					SELECT `meta_key`
					FROM `{$wpdb->sitemeta}`
					WHERE `meta_key` LIKE %s
						AND `site_id` = %d
				"
				, $this->option_name_pattern
				, $wpdb->siteid
			)
		); // WPCS: cache pass.

		array_map( 'delete_site_option', $options );
	}
}

// EOF
