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
 * Classes are loaded using a class map approach, where the class files in a
 * directory are returned by the index.php file in that directory. The array
 * returned by the index.php file is expected to be a list of files indexed by the
 * name of the class that they contain. The class names are expected to be all
 * lowercase.
 *
 * If the autoloading feature of PHP is enabled, self::load_class() is registered
 * as an autoloader. The PHP autoloader is provided by the SPL package, which is
 * always compiled with PHP in version 5.3.0 or later. However, in version 5.2,
 * it is enabled by default, but PHP can be compiled without it.
 *
 * In the rare case that autoloading is not available the classes need to be included
 * manually instead. This is done by looping over the class map and including every
 * file in it. For this reason it is important that the classes occur in the correct
 * order in the map. If a class extends another class but occurs earlier in the class
 * map than the class it extends, this will result in a fatal error when autoloading
 * is disabled, as the extended class will not be found when the extending class is
 * included. It is recommended that you generate and verify your class map files
 * using the Grunt task included in the {@link https://github.com/WordPoints/dev-lib/
 * WordPoints dev-lib}.
 *
 * @since 2.1.0
 */
class WordPoints_Class_Autoloader {

	/**
	 * The directories of classes to autoload.
	 *
	 * Array of class => file map arrays, indexed by directory.
	 *
	 * @since 2.1.0
	 *
	 * @var array[]
	 */
	protected static $dirs = array();

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
	 * @param string $dir The full path of the directory.
	 */
	public static function register_dir( $dir ) {

		if ( ! isset( self::$spl_enabled ) ) {

			self::$spl_enabled = function_exists( 'spl_autoload_register' );

			if ( self::$spl_enabled ) {
				spl_autoload_register( __CLASS__ . '::load_class' );
			}
		}

		$dir = trailingslashit( $dir );

		self::$dirs[ $dir ] = require $dir . '/index.php';

		if ( ! self::$spl_enabled ) {
			foreach ( self::$dirs[ $dir ] as $file ) {
				require_once $dir . $file;
			}
		}
	}

	/**
	 * Load a class.
	 *
	 * @since 2.1.0
	 *
	 * @param string $class_name The name fo the class to load.
	 */
	public static function load_class( $class_name ) {

		$class_name = strtolower( $class_name );

		foreach ( self::$dirs as $dir => $map ) {
			if ( isset( $map[ $class_name ] ) ) {
				require_once $dir . $map[ $class_name ];
				return;
			}
		}
	}
}

// EOF
