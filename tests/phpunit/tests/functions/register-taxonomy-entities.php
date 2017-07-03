<?php

/**
 * Test case for the wordpoints_register_taxonomy_entities() function.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.4.0
 */

/**
 * Tests wordpoints_register_taxonomy_entities().
 *
 * @since 2.4.0
 *
 * @covers ::wordpoints_register_taxonomy_entities
 */
class WordPoints_Functions_Register_Taxonomy_Entities_Test
	extends WordPoints_PHPUnit_TestCase {

	/**
	 * Test that it registers the parent entity for hierarchical taxonomies.
	 *
	 * @since 2.4.0
	 *
	 * @coves ::wordpoints_register_post_type_taxonomy_entities
	 */
	public function test_hierarchical() {

		$this->mock_apps();

		$entities = wordpoints_entities();
		$children = $entities->get_sub_app( 'children' );

		wordpoints_register_taxonomy_entities( 'category' );

		$this->assertTrue( $children->is_registered( 'term\category', 'parent' ) );
	}

	/**
	 * Test it doesn't register the parent entity for non-hierarchical taxonomies.
	 *
	 * @since 2.4.0
	 *
	 * @coves ::wordpoints_register_post_type_taxonomy_entities
	 */
	public function test_non_hierarchical() {

		$this->mock_apps();

		$entities = wordpoints_entities();
		$children = $entities->get_sub_app( 'children' );

		wordpoints_register_taxonomy_entities( 'post_tag' );

		$this->assertFalse( $children->is_registered( 'term\post_tag', 'parent' ) );
	}
}

// EOF
