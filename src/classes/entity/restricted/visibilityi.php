<?php

/**
 * Entity restricted visibility interface.
 *
 * @package WordPoints\Entities
 * @since 2.1.0
 */

/**
 * Implemented by entities whose visibility may be restricted for some users.
 *
 * @since 2.1.0
 * @deprecated 2.2.0 Use the entity restrictions API instead.
 */
interface WordPoints_Entity_Restricted_VisibilityI {

	/**
	 * Check whether a user has the caps to view this entity.
	 *
	 * Usually when you are implementing this method, you will want to return false
	 * if the entity doesn't exist, because it might not be possible to check if it
	 * was restricted in that case.
	 *
	 * @since 2.1.0
	 *
	 * @param int   $user_id The user's ID.
	 * @param mixed $id      The entity's ID.
	 *
	 * @return bool Whether the user can view the entity.
	 */
	public function user_can_view( $user_id, $id );
}

// EOF
