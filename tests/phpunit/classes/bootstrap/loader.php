<?php

/**
 * The PHPUnit bootstrap loader.
 *
 * This file is only kept here for modules that are using version 2.5.0 of the
 * dev-lib.
 *
 * See https://github.com/WordPoints/wordpoints/issues/587#issuecomment-266244843
 *
 * @package WordPoints\PHPUnit
 * @since 2.2.0
 * @deprecated 2.3.0
 */

/**
 * The real PHPUnit bootstrap loader
 *
 * @since 2.3.0
 */
require_once dirname( __FILE__ ) . '/../../../../dev-lib/phpunit/classes/bootstrap/loader.php';

// EOF
