<?php

/**
 * Post terms entity relationship class.
 *
 * @package WordPoints
 * @since 2.4.0
 */

/**
 * Represents the relationship between a Post and its Terms.
 *
 * @since 2.4.0
 */
class WordPoints_Entity_Post_Terms
	extends WordPoints_Entity_Relationship_Dynamic
	implements WordPoints_Entityish_StoredI {

	/**
	 * @since 2.4.0
	 */
	protected $related_entity_slug = 'term{}';

	/**
	 * @since 2.4.0
	 */
	public function get_title() {

		$taxonomy = get_taxonomy( substr( $this->slug, 6 /* terms\ */ ) );

		if ( $taxonomy ) {
			return $taxonomy->labels->name;
		} else {
			return parent::get_title();
		}
	}

	/**
	 * @since 2.4.0
	 */
	protected function get_related_entity_ids( WordPoints_Entity $entity ) {

		$id = $entity->get_the_id();

		$taxonomy = substr( $this->slug, 6 /* terms\ */ );

		return wp_get_object_terms( $id, $taxonomy, array( 'fields' => 'ids' ) );
	}

	/**
	 * @since 2.4.0
	 */
	public function get_storage_info() {
		return array(
			'type' => 'db',
			'info' => array(
				'type'             => 'table',
				'table_name'       => $GLOBALS['wpdb']->term_relationships,
				'primary_id_field' => 'object_id',
				'related_id_field' => array(
					'table_name' => $GLOBALS['wpdb']->term_taxonomy,
					'on'         => array(
						'primary_field' => 'term_taxonomy_id',
						'join_field'    => 'term_taxonomy_id',
					),
				),
			),
		);
	}
}

// EOF
