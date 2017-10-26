<?php

/**
 * Term parent entity relationship class.
 *
 * @package WordPoints\Entities
 * @since 2.4.0
 */

/**
 * Represents the relationship between a Term and its parent Term.
 *
 * @since 2.4.0
 */
class WordPoints_Entity_Term_Parent
	extends WordPoints_Entity_Relationship_Dynamic
	implements WordPoints_Entityish_StoredI {

	/**
	 * @since 2.4.0
	 */
	protected $primary_entity_slug = 'term';

	/**
	 * @since 2.4.0
	 */
	protected $related_entity_slug = 'term';

	/**
	 * @since 2.4.0
	 */
	protected $related_ids_field = 'parent';

	/**
	 * @since 2.4.0
	 */
	public function get_title() {

		$taxonomy = get_taxonomy( substr( $this->related_entity_slug, 5 /* term\ */ ) );

		if ( $taxonomy ) {

			return sprintf(
				// translators: Taxonomy name.
				_x( 'Parent %s', 'term entity', 'wordpoints' )
				, $taxonomy->labels->singular_name
			);

		} else {

			return _x( 'Parent Term', 'term entity', 'wordpoints' );
		}
	}

	/**
	 * @since 2.4.0
	 */
	public function get_storage_info() {
		return array(
			'type' => 'db',
			'info' => array(
				'type'             => 'table',
				'table_name'       => $GLOBALS['wpdb']->term_taxonomy,
				'primary_id_field' => 'term_id',
				'related_id_field' => 'parent',
			),
		);
	}
}

// EOF
