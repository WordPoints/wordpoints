<?php

/**
 * Module paths class.
 *
 * @package WordPoints
 * @since   2.3.0
 */

/**
 * Store and resolve module paths.
 *
 * Used to make symlinked modules work correctly.
 *
 * @since 1.6.0
 */
final class WordPoints_Module_Paths {

	/**
	 * The path to the modules directory.
	 *
	 * @since 1.6.0
	 *
	 * @var string $modules_dir
	 */
	private static $modules_dir;

	/**
	 * The registered module paths.
	 *
	 * Each element has the following keys:
	 * - 'module_path' The path to the module directory.
	 * - 'module_realpath' The path to the module directory, with symlinks resolved.
	 * - 'realpath_length' The string length of the realpath.
	 *
	 * @since 1.6.0
	 *
	 * @var array $paths
	 */
	private static $paths = array();

	/**
	 * Whether the paths have been sorted.
	 *
	 * Saves us from sorting them multiple times.
	 *
	 * @since 1.6.0
	 *
	 * @var bool $paths_sorted
	 */
	private static $paths_sorted = false;

	/**
	 * Register a module's real path.
	 *
	 * The real path is used to resolve symlinked modules.
	 *
	 * Single-file modules symlinks aren't resolved, because they don't include any
	 * assets, so they have no need to get the URL relative to themselves.
	 *
	 * @since 1.6.0
	 *
	 * @param string $file The known path to the module's main file.
	 *
	 * @return bool Whether the path was able to be registered.
	 */
	public static function register( $file ) {

		$file = wp_normalize_path( $file );

		// We store this so that we don't have to keep normalizing a constant value.
		if ( ! isset( self::$modules_dir ) ) {
			self::$modules_dir = wp_normalize_path( wordpoints_extensions_dir() );
		}

		$module_path = wp_normalize_path( dirname( $file ) );

		// It was a single-file module.
		if ( $module_path . '/' === self::$modules_dir ) {
			return false;
		}

		$module_realpath = wp_normalize_path( dirname( realpath( $file ) ) );

		if ( $module_path !== $module_realpath ) {

			$realpath_length = strlen( $module_realpath );

			// Use unique keys, but still easy to sort by realpath length.
			self::$paths[ $realpath_length . '-' . $module_path ] = array(
				'module_path'     => $module_path,
				'module_realpath' => $module_realpath,
				'realpath_length' => $realpath_length,
			);

			self::$paths_sorted = false;
		}

		return true;
	}

	/**
	 * Reverse resolve a module symlink path from the realpath.
	 *
	 * @since 1.6.0
	 *
	 * @param string $file The real path of the main module file.
	 *
	 * @return string The path to the symlink in the modules directory.
	 */
	public static function resolve( $file ) {

		$file = wp_normalize_path( $file );

		// Sort the paths by the realpath length, see https://core.trac.wordpress.org/ticket/28441.
		if ( ! self::$paths_sorted ) {
			krsort( self::$paths );
			self::$paths_sorted = true;
		}

		foreach ( self::$paths as $path ) {
			if ( 0 === strpos( $file, $path['module_realpath'] ) ) {
				$file = $path['module_path'] . substr( $file, $path['realpath_length'] );
			}
		}

		return $file;
	}
}

// EOF
