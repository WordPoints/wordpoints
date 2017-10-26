<?php

/**
 * Wildcards metadata uninstaller class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Uninstaller for all metadata matching a key pattern including wildcards.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Metadata_Wildcards implements WordPoints_RoutineI {

	/**
	 * The type of metadata to uninstall.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The meta key pattern including wildcards.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $key_pattern;

	/**
	 * Whether the meta key should be prefixed with the site DB prefix.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $prefixed;

	/**
	 * @since 2.4.0
	 *
	 * @param string $type        The type of metadata.
	 * @param string $key_pattern The meta key pattern, including wildcards.
	 * @param bool   $prefixed    Whether to prefix the key with the site DB prefix.
	 */
	public function __construct( $type, $key_pattern, $prefixed = false ) {

		$this->key_pattern = $key_pattern;
		$this->type        = $type;
		$this->prefixed    = $prefixed;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		global $wpdb;

		$table = wordpoints_escape_mysql_identifier( _get_meta_table( $this->type ) );

		$key_pattern = $this->key_pattern;

		if ( $this->prefixed ) {
			$key_pattern = $wpdb->get_blog_prefix() . $key_pattern;
		}

		$keys = $wpdb->get_col( // WPCS: unprepared SQL OK.
			$wpdb->prepare( // WPCS: unprepared SQL OK.
				"
						SELECT `meta_key`
						FROM {$table}
						WHERE `meta_key` LIKE %s
					"
				, $key_pattern
			)
		); // WPCS: cache pass.

		foreach ( $keys as $key ) {
			delete_metadata( $this->type, 0, wp_slash( $key ), '', true );
		}
	}
}

// EOF
