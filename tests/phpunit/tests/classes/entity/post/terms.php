<?php

/**
 * Test case for WordPoints_Entity_Post_Terms.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since   2.4.0
 */

/**
 * Tests WordPoints_Entity_Post_Terms.
 *
 * @since 2.4.0
 *
 * @covers WordPoints_Entity_Post_Terms
 */
class WordPoints_Entity_Post_Terms_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test getting the title when the taxonomy is known.
	 *
	 * @since 2.4.0
	 */
	public function test_get_title() {

		$relationship = new WordPoints_Entity_Post_Terms( 'terms\post_tag' );

		$this->assertSame( __( 'Tags' ), $relationship->get_title() );
	}

	/**
	 * Test getting the title when the taxonomy is unknown.
	 *
	 * @since 2.4.0
	 */
	public function test_get_title_nonexistent_taxonomy() {

		$relationship = new WordPoints_Entity_Post_Terms( 'terms\invalid' );

		$this->assertSame(
			'term\invalid{}'
			, $relationship->get_title()
		);
	}
}

// EOF
