<?php

/**
 * User Roles entity relationship class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents the relationship of between a User and their Roles.
 *
 * @since 2.1.0
 */
class WordPoints_Entity_User_Roles
	extends WordPoints_Entity_Relationship
	implements WordPoints_Entityish_StoredI {

	/**
	 * @since 2.1.0
	 */
	protected $primary_entity_slug = 'user';

	/**
	 * @since 2.1.0
	 */
	protected $related_entity_slug = 'user_role{}';

	/**
	 * @since 2.1.0
	 */
	protected $related_ids_field = 'roles';

	/**
	 * @since 2.1.0
	 */
	protected function get_related_entity_ids( WordPoints_Entity $entity ) {
		return $entity->get_the_attr_value( $this->related_ids_field );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return __( 'Roles', 'wordpoints' );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_storage_info() {
		return array(
			'type' => 'db',
			'info' => array(
				'type'             => 'table',
				'table_name'       => $GLOBALS['wpdb']->usermeta,
				'primary_id_field' => 'user_id',
				'related_id_field' => array(
					'type'  => 'serialized_array',
					'field' => 'meta_value',
				),
				'conditions'       => array(
					array(
						'field' => 'meta_key',
						'value' => $GLOBALS['wpdb']->get_blog_prefix() . 'capabilities',
					),
				),
			),
		);
	}
}

// EOF
