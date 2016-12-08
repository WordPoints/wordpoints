<?php

/**
 * Slugless multilevel deep class registry class.
 *
 * @package WordPoints
 * @since 2.2.0
 */

/**
 * A registry where classes are grouped together at any level in a deep hierarchy.
 *
 * Differs only from the base multilevel registry in that it does not pass the class
 * slugs to the constructors when the objects are created.
 *
 * @since 2.2.0
 */
class WordPoints_Class_Registry_Deep_Multilevel_Slugless
	extends WordPoints_Class_Registry_Deep_Multilevel {

	/**
	 * @since 2.2.0
	 */
	protected $settings = array(
		'pass_slugs' => false,
	);
}

// EOF
