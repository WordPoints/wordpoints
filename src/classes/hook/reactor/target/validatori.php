<?php

/**
 * Target validator hook reactor interface..
 *
 * @package WordPoints
 * @since   2.4.2
 */

/**
 * Interface for hook reactors that need to validate the target.
 *
 * Sometimes a reactor cannot hit just any target. Implementing this interface gives
 * the option of validating the target before a hit is attempted. Note that this may
 * happen before it is determined whether or not a hit should actually occur.
 *
 * @since 2.4.2
 */
interface WordPoints_Hook_Reactor_Target_ValidatorI {

	/**
	 * Checks if the reactor can hit the given target.
	 *
	 * @since 2.4.2
	 *
	 * @param WordPoints_EntityishI $target The target to be hit.
	 * @param WordPoints_Hook_Fire  $fire   The fire the target would be hit by.
	 *
	 * @return bool Whether the target can be hit by the reactor or not.
	 */
	public function can_hit( WordPoints_EntityishI $target, WordPoints_Hook_Fire $fire );
}

// EOF
