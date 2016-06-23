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
 * In the rare case that autoloading is not enabled, provision is made for loading
 * the classes manually. See self::init() for more details.
 *
 * @since 2.1.0
 */
class WordPoints_Class_Autoloader {

	/**
	 * Whether self::init() has been hooked to the wordpoints_modules_loaded action.
	 *
	 * The action is hooked only when self::register_dir() is called, and we set this
	 * flag so that we don't hook it twice.
	 *
	 * @since 2.1.0
	 *
	 * @var bool
	 */
	protected static $added_action = false;

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
	 * Register a directory to autoload classes from.
	 *
	 * @since 2.1.0
	 *
	 * @param string $dir    The full path of the directory.
	 * @param string $prefix The prefix used for class names in this directory.
	 */
	public static function register_dir( $dir, $prefix ) {

		if ( ! self::$added_action ) {
			add_action( 'wordpoints_modules_loaded', __CLASS__ . '::init', 0 );
			self::$added_action = true;
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

	/**
	 * Initialize the autoloader.
	 *
	 * If the autoloading feature of PHP is enabled, self::load_class() is registered
	 * as an autoloader. The PHP autoloader is provided by the SPL package, which is
	 * always compiled with PHP in version 5.3.0 or later. However, in version 5.2,
	 * it is enabled by default, but PHP can be compiled without it.
	 *
	 * When autoloading is not available the classes need to be included manually
	 * instead. This function allows that to be done by putting code to manually
	 * include all of the classes in an index.php file in the root of the registered
	 * directory that those classes are in. This index.php file will be included if
	 * autoloading is disabled.
	 *
	 * @since 2.1.0
	 */
	public static function init() {

		if ( function_exists( 'spl_autoload_register' ) ) {

			spl_autoload_register( __CLASS__ . '::load_class' );

		} else {

			foreach ( self::$prefixes as $prefix => $data ) {
				foreach ( $data['dirs'] as $dir ) {
					if ( file_exists( $dir . '/index.php' ) ) {
						require( $dir . '/index.php' );
					}
				}
			}
		}
	}
}

// EOF
