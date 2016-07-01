<?php

/**
 * WordPoints class autoloader.
 *
 * @package WordPoints
 * @since 2.1.0
 */

/**
 * Autoloads classes.
 *
 * {@link http://www.php-fig.org/psr/psr-0/ PSR-0} is loosely followed, with the
 * following differences:
 * - Currently, no provision is made for namespaces.
 * - The file names are expected to be all lowercase.
 *
 * If the autoloading feature of PHP is enabled, self::load_class() is registered
 * as an autoloader. The PHP autoloader is provided by the SPL package, which is
 * always compiled with PHP in version 5.3.0 or later. However, in version 5.2,
 * it is enabled by default, but PHP can be compiled without it.
 *
 * In the rare case that autoloading is not available the classes need to be included
 * manually instead. This class allows that to be done by putting code to manually
 * include all of the classes in an index.php file in the root of the registered
 * directory that those classes are in. This index.php file will be included if
 * autoloading is disabled.
 *
 * @since 2.1.0
 */
class WordPoints_Class_Autoloader {

	/**
	 * The prefixes of classes to autoload.
	 *
	 * @since 2.1.0
	 *
	 * @var array[]
	 */
	protected static $prefixes = array();

	/**
	 * Whether the registered directories have been sorted.
	 *
	 * We use this flag to prevent us from resorting the directories unnecessarily.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	protected static $sorted = false;

	/**
	 * Whether the SPL autoloader is available.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	protected static $spl_enabled;

	/**
	 * Register a directory to autoload classes from.
	 *
	 * @since 2.1.0
	 *
	 * @param string $dir    The full path of the directory.
	 * @param string $prefix The prefix used for class names in this directory.
	 */
	public static function register_dir( $dir, $prefix ) {

		if ( ! isset( self::$spl_enabled ) ) {

			self::$spl_enabled = function_exists( 'spl_autoload_register' );

			if ( self::$spl_enabled ) {
				spl_autoload_register( __CLASS__ . '::load_class' );
			}
		}

		if ( ! self::$spl_enabled ) {
			if ( file_exists( $dir . '/index.php' ) ) {
				require( $dir . '/index.php' );
			}
		}

		self::$prefixes[ $prefix ]['length'] = strlen( $prefix );
		self::$prefixes[ $prefix ]['dirs'][] = trailingslashit( $dir );

		self::$sorted = false;
	}

	/**
	 * Load a class.
	 *
	 * Checks if the class name matches any of the registered prefixes, and if so,
	 * checks whether a file for that class exists in the registered directories for
	 * that prefix. If the file does exist, it is included.
	 *
	 * @since 2.1.0
	 *
	 * @param string $class_name The name fo the class to load.
	 */
	public static function load_class( $class_name ) {

		if ( ! self::$sorted ) {
			arsort( self::$prefixes );
			self::$sorted = true;
		}

		foreach ( self::$prefixes as $prefix => $data ) {

			if ( substr( $class_name, 0, $data['length'] ) !== $prefix ) {
				continue;
			}

			$trimmed_class_name = substr( $class_name, $data['length'] );

			$file_name = str_replace( '_', '/', strtolower( $trimmed_class_name ) );
			$file_name = $file_name . '.php';

			foreach ( $data['dirs'] as $dir ) {

				if ( ! file_exists( $dir . $file_name ) ) {
					continue;
				}

				require_once( $dir . $file_name );

				return;
			}
		}
	}
}

// EOF
