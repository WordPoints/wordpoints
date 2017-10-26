<?php

/**
 * Merge extension core updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updater for when an extension is merged into core.
 *
 * The extension is deactivated and added to a list of merged extensions so that
 * an appropriate notice can be shown to the admin.
 *
 * @since 2.4.0
 */
class WordPoints_Updater_Core_Extension_Merge implements WordPoints_RoutineI {

	/**
	 * The extension basename.
	 *
	 * @since 2.4.0
	 *
	 * @var string
	 */
	protected $extension;

	/**
	 * @since 2.4.0
	 *
	 * @param string $extension The extension basename.
	 */
	public function __construct( $extension ) {

		$this->extension = $extension;
	}

	/**
	 * @since 2.4.0
	 */
	public function run() {

		if ( true !== wordpoints_validate_module( $this->extension ) ) {
			return;
		}

		$is_multisite = is_multisite();

		// Mark extension as merged.
		$merged = wordpoints_get_maybe_network_array_option(
			'wordpoints_merged_extensions'
			, $is_multisite
		);

		$merged[] = $this->extension;

		wordpoints_update_maybe_network_option(
			'wordpoints_merged_extensions'
			, $merged
			, $is_multisite
		);

		// Deactivate it.
		wordpoints_deactivate_modules(
			array( $this->extension )
			, false
			, $is_multisite
		);
	}
}

// EOF
