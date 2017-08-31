<?php

/**
 * User entity class.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Represents a User.
 *
 * @since 2.1.0
 */
class WordPoints_Entity_User extends WordPoints_Entity_Stored_DB_Table {

	/**
	 * @since 2.1.0
	 */
	protected $wpdb_table_name = 'users';

	/**
	 * @since 2.1.0
	 */
	protected $context = '';

	/**
	 * @since 2.1.0
	 */
	protected $id_field = 'ID';

	/**
	 * @since 2.4.0
	 */
	protected $id_is_int = true;

	/**
	 * @since 2.1.0
	 */
	protected $getter = 'get_userdata';

	/**
	 * @since 2.1.0
	 */
	protected $human_id_field = 'display_name';

	/**
	 * @since 2.1.0
	 */
	public function get_title() {
		return __( 'User', 'wordpoints' );
	}
}

// EOF
