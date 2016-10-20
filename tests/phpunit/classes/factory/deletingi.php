<?php

/**
 * Deleting factory interface.
 *
 * @package WordPoints\PHPUnit
 * @since 2.2.0
 */

/**
 * Implemented by object factories for the PHPUnit tests that support deleting.
 *
 * @since 2.2.0
 */
interface WordPoints_PHPUnit_Factory_DeletingI {

	/**
	 * Delete an object of this type.
	 *
	 * @since 2.2.0
	 *
	 * @param int $id The ID of the object to delete.
	 *
	 * @return bool|null Optional. Whether the object was deleted successfully.
	 */
	public function delete( $id );
}

// EOF
