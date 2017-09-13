<?php

/**
 * A test module.
 *
 * @package Test6
 */

WordPoints_Modules::register(
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

require_once dirname( __FILE__ ) . '/classes/installable.php';

// EOF
