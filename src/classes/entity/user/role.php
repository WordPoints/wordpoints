<?php

/**
 * User Role entity class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents a User Role.
 *
 * @since 2.1.0
 */
class WordPoints_Entity_User_Role
	extends WordPoints_Entity_Stored_Array
	implements WordPoints_Entity_EnumerableI {

	/**
	 * @since 2.1.0
	 */
	protected $id_field = 'name';

	/**
	 * @since 2.1.0
	 */
	protected $getter = 'get_role';

	/**
	 * @since 2.1.0
	 */
	protected function get_entity_human_id( $entity ) {

		$names = wp_roles()->get_names();

		if ( ! isset( $names[ $entity->name ] ) ) {
			return false;
		}

		return translate_user_role( $names[ $entity->name ] );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return __( 'Role', 'wordpoints' );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_enumerated_values() {
		return $this->get_storage_array();
	}

	/**
	 * @since 2.1.0
	 */
	public function get_storage_array() {

		return wp_roles()->role_objects;
	}
}

// EOF
