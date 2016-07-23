<?php

/**
 * Miss listener hook extension interface.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Implemented by extensions that want to listen for misses.
 *
 * @since 2.1.0
 */
interface WordPoints_Hook_Extension_Miss_ListenerI {

	/**
	 * Method to be called after a hook fire is known to be a miss and not a hit.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_Hook_Fire $fire The fire that was a miss.
	 */
	public function after_miss( WordPoints_Hook_Fire $fire );
}

// EOF
