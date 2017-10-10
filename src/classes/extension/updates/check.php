<?php

/**
 * Extension updates check class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Performs a check for extension updates.
 *
 * @since 2.4.0
 */
class WordPoints_Extension_Updates_Check {

	/**
	 * The required cache freshness to avoid a recheck.
	 *
	 * @since 2.4.0
	 *
	 * @var int|null
	 */
	protected $cache_timeout;

	/**
	 * @since 2.4.0
	 *
	 * @param int $cache_timeout Required cache freshness. The cache must be less
	 *                           than this number of seconds old to be used instead
	 *                           of rechecking the remote servers. Default is 12
	 *                           hours.
	 */
	public function __construct( $cache_timeout = null ) {

		if ( is_null( $cache_timeout ) ) {
			$cache_timeout = 12 * HOUR_IN_SECONDS;
		}

		$this->cache_timeout = $cache_timeout;
	}

	/**
	 * Run the update check.
	 *
	 * @since 2.4.0
	 *
	 * @return WordPoints_Extension_UpdatesI|false The updates, or false if the check
	 *                                             was not run (due to the cache
	 *                                             being fresh enough, or some other
	 *                                             reason).
	 */
	public function run() {

		if ( wp_installing() ) {
			return false;
		}

		$updates    = new WordPoints_Extension_Updates();
		$extensions = wordpoints_get_modules();
		$cache      = wordpoints_get_extension_updates();

		$updates->set_versions_checked( wp_list_pluck( $extensions, 'version' ) );

		/*
		 * If the cache is fresh enough that we aren't required to check again, first
		 * see if any extension versions have changed since then, and only bail out
		 * if they haven't. (It's possible that the extension changes have included
		 * code changes that will affect the update routine, and thus the updates
		 * available.)
		 */
		if (
			$this->cache_timeout > ( time() - $cache->get_time_checked() )
			&& ! $this->extensions_have_changed( $cache, $updates )
		) {
			return false;
		}

		// Update the time to prevent multiple requests if the request hangs.
		if ( $cache instanceof WordPoints_Extension_Updates ) {
			$cache->set_time_checked( time() );
			$cache->save();
		}

		/**
		 * Fires before a check for any extension updates.
		 *
		 * @since 2.4.0
		 *
		 * @param WordPoints_Extension_UpdatesI $updates The updates being checked for.
		 */
		do_action( 'wordpoints_before_extension_update_check', $updates );

		foreach ( $extensions as $file => $extension ) {

			$server = wordpoints_get_server_for_extension( $extension );

			if ( ! $server ) {
				continue;
			}

			$api = $server->get_api();

			if ( ! $api instanceof WordPoints_Extension_Server_API_UpdatesI ) {
				continue;
			}

			$extension_data = new WordPoints_Extension_Server_API_Extension_Data(
				$extension['ID']
				, $server
			);

			$extension_data->delete( 'latest_version' );

			$latest_version = $api->get_extension_latest_version( $extension_data );

			if (
				false === $latest_version
				|| ! version_compare( $latest_version, $extension['version'], '>' )
			) {
				continue;
			}

			$updates->set_new_version( $file, $latest_version );
		}

		$updates->save();

		/**
		 * Fires when a check for any extension updates is completed.
		 *
		 * @since 2.4.0
		 *
		 * @param WordPoints_Extension_UpdatesI $updates The discovered updates.
		 */
		do_action( 'wordpoints_extension_update_check_completed', $updates );

		return $updates;
	}

	/**
	 * Checks if the extensions have changed since an update check was performed.
	 *
	 * @since 2.4.0
	 *
	 * @param WordPoints_Extension_UpdatesI $cache   The cache to check against.
	 * @param WordPoints_Extension_UpdatesI $updates The extensions being checked now.
	 *
	 * @return bool Whether any extensions have changed.
	 */
	protected function extensions_have_changed(
		WordPoints_Extension_UpdatesI $cache,
		WordPoints_Extension_UpdatesI $updates
	) {

		$checked         = $updates->get_versions_checked();
		$current_checked = $cache->get_versions_checked();

		ksort( $checked );
		ksort( $current_checked );

		if ( $checked !== $current_checked ) {
			return true;
		}

		return false;
	}
}

// EOF
