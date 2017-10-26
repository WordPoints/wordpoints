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

		parent::__construct( $slug, $parent_slug );

		$parts = wordpoints_parse_dynamic_slug( $this->slug );

		if ( ! $parts['dynamic'] ) {
			// Back-compat for pre-2.3.0, just in case of manual construction without
			// the parent slug.
			if ( isset( $parent_slug ) ) {
				$parts = wordpoints_parse_dynamic_slug( $parent_slug );
			}
		}

		if ( $parts['dynamic'] ) {

			// Back compat for pre-2.4.0, in case of manual construction.
			if ( ! isset( $parent_slug ) ) {
				$this->primary_entity_slug = "{$this->primary_entity_slug}\\{$parts['dynamic']}";
			}

			$parsed = $this->parse_slug( $this->related_entity_slug );

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
