<?php

/**
 * Reversing hook event interface.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Implemented by events that are reversible.
 *
 * @since 2.1.0
 */
interface WordPoints_Hook_Event_ReversingI {

	/**
	 * Get a string describing the reversal event in the past tense.
	 *
	 * @since 2.1.0
	 *
	 * @return string The reversal text.
	 */
	public function get_reversal_text();
}

// EOF
