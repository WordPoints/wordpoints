<?php

/**
 * Test case for WordPoints_Entity_Relationship_Dynamic.
 *
 * @package WordPoints\PHPUnit\Tests
 * @since 2.1.0
 */

/**
 * Tests WordPoints_Entity_Relationship_Dynamic.
 *
 * @since 2.1.0
 *
 * @covers WordPoints_Entity_Relationship_Dynamic
 */
class WordPoints_Entity_Relationship_Dynamic_Test extends WordPoints_PHPUnit_TestCase_Hooks {

	/**
	 * Test getting the arg value.
	 *
	 * @since        1.0.0
	 *
	 * @dataProvider data_provider_relationships
	 *
	 * @param string $related_slug      The slug of the related entity.
	 * @param string $relationship_slug The slug of the relationship.
	 * @param array  $primary_slug      The slug of the primary entity.
	 */
	public function test_get_value( $related_slug, $relationship_slug, $primary_slug ) {

		if ( '{}' === substr( $related_slug, -2 ) ) {

			$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship_Dynamic_Array(
				$relationship_slug
			);

			$entity_slug = substr( $related_slug, 0, -2 );

		} else {

			$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship_Dynamic(
				$relationship_slug
			);

			$entity_slug = $related_slug;
		}

		$entity = $this->factory->wordpoints->entity->create_and_get(
			array( 'slug' => $entity_slug )
		);

		$this->assertSame( $relationship_slug, $relationship->get_slug() );
		$this->assertSame( $related_slug, $relationship->get_related_entity_slug() );
		$this->assertSame( $primary_slug, $relationship->get_primary_entity_slug() );
		$this->assertSame( $entity->get_title(), $relationship->get_title() );
	}

	/**
	 * Provides a list of sets hook arg configurations.
	 *
	 * @since 2.1.0
	 *
	 * @return array[]
	 */
	public function data_provider_relationships() {

		return array(
			'entity' => array( 'test_entity', 'relationship', 'primary_entity' ),
			'dynamic' => array( 'test_entity\a', 'relationship\a', 'primary_entity\a' ),
			'array' => array( 'test_entity{}', 'relationship', 'primary_entity' ),
			'array_dynamic' => array( 'test_entity\a{}', 'relationship\a', 'primary_entity\a' ),
			'double_dynamic' => array( 'test_entity\a\b', 'relationship\a\b', 'primary_entity\a\b' ),
			'array_double_dynamic' => array( 'test_entity\a\b{}', 'relationship\a\b', 'primary_entity\a\b' ),
		);
	}

	/**
	 * Test getting the title when the entity is not found.
	 *
	 * @since 2.1.0
	 */
	public function test_get_title_unknown_entity() {

		$relationship = new WordPoints_PHPUnit_Mock_Entity_Relationship_Dynamic(
			'relationship'
		);

		$this->assertSame( 'test_entity', $relationship->get_title() );
	}
}

// EOF
