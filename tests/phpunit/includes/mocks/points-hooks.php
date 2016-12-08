<?php

/**
 * Contains empty classes that extend the hook classes and are used as test doubles.
 *
 * @package WordPoints\tests
 * @since 1.9.0
 * @deprecated 2.2.0
 */

/**
 * Test double for the base points hook class.
 *
 * @since 1.9.0
 * @deprecated 2.2.0 Use WordPoints_PHPUnit_Mock_Points_Hook instead.
 */
class WordPoints_Points_Hook_TestDouble extends WordPoints_Points_Hook {}

/**
 * Test double for the base Post hook class.
 *
 * @since 1.9.0
 * @deprecated 2.2.0 Use WordPoints_PHPUnit_Mock_Points_Hook_Post_Type instead.
 */
class WordPoints_Post_Type_Points_Hook_TestDouble
	extends WordPoints_PHPUnit_Mock_Points_Hook_Post_Type {}

// EOF
