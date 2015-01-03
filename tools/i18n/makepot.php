<?php

/**
 * Class to generate a POT file for a WordPoints extension.
 *
 * @package WordPoints
 * @since 1.9.0
 */

if ( ! getenv( 'WP_TESTS_DIR' ) ) {
	exit( '$_ENV["WP_TESTS_DIR"] is not set.' . PHP_EOL );
}

/**
 * WordPress's MakePOT class.
 *
 * @since 1.9.0
 */
require_once( getenv( 'WP_TESTS_DIR' ) . '/../../tools/i18n/makepot.php' );

/**
 * Generate a POT file for a WordPoints extension.
 *
 * Currently it can generate a POT file for WordPoints itself, and also for modules.
 *
 * @since 1.9.0
 */
class WordPoints_MakePOT extends MakePOT {

	/**
	 * @since 1.9.0
	 */
	public $projects = array( 'wordpoints', 'wordpoints-module' );

	/**
	 * @since 1.9.0
	 */
	public function __construct() {

		parent::__construct();

		$this->meta['wordpoints'] = $this->meta['wp-plugin'];

		$this->meta['wordpoints-module'] = array(
			'description' => 'Translation of the WordPoints module {name} {version} by {author}',
			'msgid-bugs-address' => '',
			'copyright-holder' => '{author}',
			'package-name' => '{name}',
			'package-version' => '{version}',
		);
	}

	/**
	 * Generate the POT file for WordPoints.
	 *
	 * @since 1.9.0
	 *
	 * @param string $dir    The directory containing WordPoints's source.
	 * @param string $output The path of the output file.
	 *
	 * @return bool Whether the POT file was generated successfully.
	 */
	public function wordpoints( $dir, $output = null ) {

		if ( is_null( $output ) ){
			$output = "{$dir}/languages/wordpoints.pot";
		}

		return $this->wp_plugin( $dir, $output, 'wordpoints' );
	}

	/**
	 * Generate the POT file for a WordPoints module.
	 *
	 * @since 1.9.0
	 *
	 * @param string $dir    The directory containing WordPoints's source.
	 * @param string $output The path of the output file.
	 * @param string $slug   The slug of the module.
	 *
	 * @return bool Whether the POT file was generated successfully.
	 */
	public function wordpoints_module( $dir, $output = null, $slug = null ) {

		if ( is_null( $slug ) ) {
			$slug = $this->guess_plugin_slug( $dir );
		}

		if ( is_null( $output ) ){
			$output = "{$dir}/languages/{$slug}.pot";
		}

		// Escape pattern-matching characters in the path.
		$module_escape_root = str_replace(
			array( '*', '?', '[' )
			, array( '[*]', '[?]', '[[]' )
			, $dir
		);

		// Get the top level files.
		$module_files = glob( "{$module_escape_root}/*.php" );

		if ( empty( $module_files ) ) {
			$this->error( 'No module source files found.' );
			return false;
		}

		$main_file = '';

		foreach ( $module_files as $module_file ) {

			if ( ! is_readable( $module_file ) ) {
				continue;
			}

			$source = $this->get_first_lines( $module_file, $this->max_header_lines );

			// Stop when we find a file with a module name header in it.
			if ( false !== $this->get_addon_header( 'Module Name', $source ) ) {
				$main_file = $module_file;
				break;
			}
		}

		if ( empty( $main_file ) ) {
			$this->error( 'Couldn\'t locate the main module file.' );
			return false;
		}

		$placeholders = array();
		$placeholders['version'] = $this->get_addon_header( 'Version', $source );
		$placeholders['author'] = $this->get_addon_header( 'Author', $source );
		$placeholders['name'] = $this->get_addon_header( 'Module Name', $source );
		$placeholders['slug'] = $slug;

		// Attempt to extract the strings and write them to the POT file.
		$result = $this->xgettext( 'wordpoints-module', $dir, $output, $placeholders );

		if ( ! $result ) {
			return false;
		}

		// Now attempt to append the headers from the module file, so they can be
		// translated too.
		$potextmeta = new WordPoints_PotExtMeta;
		if ( ! $potextmeta->append( $main_file, $output ) ) {
			return false;
		}

		// Adding non-gettexted strings can repeat some phrases, so uniquify them.
		$output_shell = escapeshellarg( $output );
		system( "msguniq {$output_shell} -o {$output_shell}" );

		return true;
	}

	/**
	 * Give an error.
	 *
	 * @since 1.9.0
	 *
	 * @param string $message The error message.
	 */
	public function error( $message ) {
		fwrite( STDERR, $message . "\n" );
	}
}

/**
 * Add metadata strings from a WordPoints module header to a POT file.
 *
 * @since 1.9.0
 */
class WordPoints_PotExtMeta extends PotExtMeta {

	/**
	 * @since 1.9.0
	 */
	public function __construct() {

		$this->headers[] = 'Module Name';
		$this->headers[] = 'Module URI';
	}

	/**
	 * @since 1.9.0
	 */
	public function load_from_file( $ext_filename ) {

		return str_replace(
			' of the plugin/theme'
			, ' of the module'
			, parent::load_from_file( $ext_filename )
		);
	}
}

// Run the CLI only if the file wasn't included.
$included_files = get_included_files();

if ( __FILE__ === $included_files[0] ) {

	$makepot = new WordPoints_MakePOT;

	if ( count( $argv ) >= 3 && in_array( $argv[1], $makepot->projects ) ) {

		$result = call_user_func(
			array( $makepot, str_replace( '-', '_', $argv[1] ) )
			, realpath( $argv[2] )
			, isset( $argv[3] ) ? $argv[3] : null
			, isset( $argv[4] ) ? $argv[4] : null
		);

		if ( false === $result ) {
			$makepot->error( 'Couldn\'t generate POT file!' );
		}

	} else {

		$usage  = "Usage: php makepot.php <project> <directory> [<output> [<slug>]]  \n\n";
		$usage .= "Generate POT file <output> from the files in <directory>\n";
		$usage .= 'Available projects: ' . implode( ', ', $makepot->projects ) . "\n";
		fwrite( STDERR, $usage );
		exit( 1 );
	}
}

// EOF
