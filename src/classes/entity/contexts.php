<?php

/**
 * Entity contexts app class.
 *
 * @package WordPoints
 * @since   2.2.0
 */

/**
 * Registry for entity context classes.
 *
 * The entity contexts app also provides context switching methods.
 *
 * @since 2.2.0
 */
class WordPoints_Entity_Contexts extends WordPoints_Class_Registry {

	/**
	 * A list of switches that have been performed.
	 *
	 * The array for each switch is indexed by context slugs, with the values
	 * indicating whether the context actually had to be switched, or was already
	 * current.
	 *
	 * @since 2.2.0
	 *
	 * @var bool[][]
	 */
	protected $switched = array();

	/**
	 * Switch to a context based on its GUID.
	 *
	 * The GUID for a context is an array indexed by context slug, with the values
	 * being the IDs of the context and its parent contexts. The array should be
	 * ordered in ascending order, from the most nested sub-context to the highest
	 * level context that sits just below the global context.
	 *
	 * @since 2.2.0
	 *
	 * @param array $context_guid The context's GUID — An array of the IDs of the
	 *                            context and its parent contexts, indexed by context
	 *                            slug.
	 *
	 * @return bool Whether the context was switched to successfully.
	 */
	public function switch_to( $context_guid ) {

		/** @var WordPoints_Entity_Context[] $to_switch */
		$to_switch = array();

		// Loop through the contexts from highest to lowest.
		$context_guid = array_reverse( $context_guid );

		foreach ( $context_guid as $slug => $id ) {

			$context = $this->get( $slug );

			if ( ! $context instanceof WordPoints_Entity_Context ) {
				return false;
			}

			$to_switch[ $slug ] = $context;
		}

		$switched = array();

		// We use two separate loops so as to not begin switching contexts until we
		// are sure that we can complete the operation, thus consolidating the back-
		// switching code that runs in the case of a failure.
		foreach ( $to_switch as $slug => $context ) {

			if ( $context_guid[ $slug ] === $context->get_current_id() ) {

				$switched[ $slug ] = false;

			} elseif ( $context->switch_to( $context_guid[ $slug ] ) ) {

				$switched[ $slug ] = true;

			} else {

				// Failed to switch, reset everything back the way it was.
				foreach ( $switched as $switched_slug => $is_switched ) {
					if ( $is_switched ) {
						$to_switch[ $switched_slug ]->switch_back();
					}
				}

				return false;
			}
		}

		$this->switched[] = $switched;

		return true;
	}

	/**
	 * Switch back to the previous context that was switch from using `switch_to()`.
	 *
	 * Note that this method can only be used in coordination with the `switch_to()`
	 * method. If you have switched contexts in some other way beside using this
	 * object, calling this method will probably not have the desired result.
	 *
	 * @since 2.2.0
	 *
	 * @return bool Whether we were able to switch back successfully.
	 */
	public function switch_back() {

		$switched = array_pop( $this->switched );

		if ( ! $switched ) {
			return false;
		}

		foreach ( $switched as $slug => $is_switched ) {
			if ( $is_switched ) {
				if ( ! $this->get( $slug )->switch_back() ) {
					return false;
				}
			}
		}

		return true;
	}
}

// EOF
