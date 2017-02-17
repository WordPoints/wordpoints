<?php

/**
 * Dynamic entity relationship class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents the relationship between dynamic entities.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Entity_Relationship_Dynamic extends WordPoints_Entity_Relationship {

	/**
	 * @since 2.1.0
	 */
	public function __construct( $slug, $parent_slug = null ) {

		parent::__construct( $slug );

		// Back-compat for pre-2.3.0, just in case.
		if ( ! isset( $parent_slug ) ) {
			$dynamic_slug = $this->slug;
		} else {
			$dynamic_slug = $parent_slug;
		}

		$parts = wordpoints_parse_dynamic_slug( $dynamic_slug );

		if ( $parts['dynamic'] ) {

			$parsed = $this->parse_slug( $this->related_entity_slug );

			$this->primary_entity_slug = "{$this->primary_entity_slug}\\{$parts['dynamic']}";
			$this->related_entity_slug = "{$parsed['slug']}\\{$parts['dynamic']}";

			if ( $parsed['is_array'] ) {
				$this->related_entity_slug .= '{}';
			}
		}
	}

	/**
	 * @since 2.1.0
	 */
	public function get_title() {

		$parsed = $this->parse_slug( $this->related_entity_slug );

		$entity = wordpoints_entities()->get( $parsed['slug'] );

		if ( $entity instanceof WordPoints_Entity ) {
			return $entity->get_title();
		} else {
			return $this->related_entity_slug;
		}
	}
}

// EOF
