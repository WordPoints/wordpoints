<?php

/**
 * Parse the arguments used to run the tests.
 *
 * @package WordPoints\Tests
 * @since 1.0.1
 */

/**
 * A child class of the PHP test runner.
 *
 * Not actually used as a runner. Rather, used to access the protected
 * longOptions property, to parse the arguments passed to the script.
 *
 * @since 1.0.1
 */
class WordPoints_PHPUnit_TextUI_Command extends PHPUnit_TextUI_Command {

	/**
	 * Parse the arguments and give messages about excluded groups.
	 *
	 * @since 1.0.1
	 */
	function __construct( $argv ) {

		$options = PHPUnit_Util_Getopt::getopt(
			$argv
			,'d:c:hv'
			,array_keys( $this->longOptions )
		);

		$uninstall_message = $ui_message = true;

		foreach ( $options[0] as $option ) {

			switch ( $option[0] ) {

				case '--exclude-group' :
					$uninstall_message = $ui_message = false;
				continue 2;

				case '--group' :
					$groups = explode( ',', $option[1] );

					$uninstall_message = ! in_array( 'uninstall', $groups );
					$ui_message        = ! in_array( 'ui', $groups );
				continue 2;
			}
		}

		if ( $uninstall_message )
			echo 'Not running WordPoints install/uninstall tests... To execute these, use --group uninstall.' . PHP_EOL;

    	if ( $ui_message ) {

    		echo 'Not running WordPoints UI tests... To execute these, use --group ui.' . PHP_EOL;

    	} else {

			echo 'Running WordPoints UI tests...', PHP_EOL;

			if ( ! wordpointstests_symlink_plugin( 'wordpoints/wordpoints.php', WORDPOINTS_DIR ) ) {

				exit( 'Error: Unable to run the tests.'
					. PHP_EOL . 'You need to create a symlink to WordPoints /src in /wp-content/plugins named /wordpoints.'
					. PHP_EOL
				);
			}

    		if ( ! class_exists( 'PHPUnit_Extensions_Selenium2TestCase' ) ) {

    			exit( 'Error: Unable to run the tests, the PHPUnit Selenium extension is not installed.'
    				. PHP_EOL . 'See <http://phpunit.de/manual/current/en/selenium.html#selenium.installation> for installation instructions.'
    				. PHP_EOL
    			);
    		}

			if ( ! defined( 'WORDPOINTS_TEST_BROWSER' ) ) {

				exit( 'Error: Unable to run the tests, WORDPOINTS_TEST_BROWSER is not defined.'
					. PHP_EOL . 'Add the following to your wp-tests-config.php, for example:'
					. PHP_EOL . 'define( \'WORDPOINTS_TEST_BROWSER\', \'firefox\' );'
					. PHP_EOL
				);
			}

    		if ( ! wordpointstests_selenium_is_running() ) {

    			echo 'Attempting to start Selenium...', PHP_EOL;

    			if ( ! wordpointstests_start_selenium() ) {

    				exit( 'Error: Unable to run the tests, Selenium does not appear to be running.'
						. PHP_EOL . 'See <http://phpunit.de/manual/current/en/selenium.html#selenium.installation> for instructions.'
						. PHP_EOL
    				);
    			}

    			echo 'Selenium started successfully...' . PHP_EOL;
    		}
    	}
    }
}

// end of file /tests/phpunit/includes/class-wordpoints-phpunit-textui-command.php
