<?php

/**
 * A test extension.
 *
 * @package Test6
 */

wordpoints_register_extension(
	'
		Extension Name: Test 6
		Version:        1.0.0
		Author:         WordPoints Tester
		Author URI:     https://www.example.com/
		Extension URI:  https://www.example.com/test-6/
		Description:    Another test module.
		Text Domain:    test-6
		Namespace:      Test_6
	'
	, __FILE__
);

WordPoints_Class_Autoloader::register_dir( dirname( __FILE__ ) . '/classes' );

// EOF
