<?php

/**
 * Metadata uninstaller class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Uninstalls object metadata.
 *
 * @since 2.4.0
 */
class WordPoints_Uninstaller_Metadata implements WordPoints_RoutineI {

	/**
	 * The type of metadata being uninstalled.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The meta keys to uninstall.
	 *
	 * @since 2.4.0
	 *
	 * @var string[]
	 */
	protected $keys;

	/**
	 * Whether to prefix the meta keys with the site DB prefix.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $prefixed;

	/**
	 * @since 2.4.0
	 *
	 * @param string   $type     The type of metadata.
	 * @param string[] $keys     The meta keys to delete.
	 * @param bool     $prefixed Whether to prefix the keys with the site DB prefix.
	 */
	public function __construct( $type, $keys, $prefixed = false ) {

		$this->type     = $type;
		$this->keys     = $keys;
		$this->prefixed = $prefixed;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		global $wpdb;

		$prefix = '';

		if ( $this->prefixed ) {
			$prefix = $wpdb->get_blog_prefix();
		}

		foreach ( $this->keys as $key ) {
			delete_metadata( $this->type, 0, wp_slash( $prefix . $key ), '', true );
		}
	}
}

// EOF
