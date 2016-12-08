<?php

/**
 * Mock post type points hook class.
 *
 * @package WordPoints\PHPUnit
 * @since 2.2.0
 */

/**
 * Mock for the base post type points hook class.
 *
 * @since 1.9.0 As WordPoints_Post_Type_Points_Hook_TestDouble.
 * @since 2.2.0
 */
class WordPoints_PHPUnit_Mock_Points_Hook_Post_Type
	extends WordPoints_Post_Type_Points_Hook_Base {

	/**
	 * @since 1.9.0 As part of WordPoints_Post_Type_Points_Hook_TestDouble.
	 * @since 2.2.0
	 */
	protected $defaults = array( 'post_type' => 'ALL', 'auto_reverse' => 1 );
}

// EOF
