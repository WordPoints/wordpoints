<?php

/**
 * Condition contains 2.4.0 updater class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Updates the entity array contains conditions to address a bug.
 *
 * There was a bug in prior versions of the code that interpreted a max that was set
 * but empty as being a max of zero, rather than no maximum. The code was updated to
 * change how empty values are processed during validation, but to avoid users being
 * surprised by a sudden change in behavior when they update existing reactions
 * affected by this, we explicitly set the max to 0. This maintains previous behavior
 * for now, and ensures that the user expects a change in behavior if they change
 * this setting.
 *
 * @since 2.4.0
 */
class WordPoints_Points_Updater_2_4_0_Condition_Contains
	implements WordPoints_RoutineI {

	/**
	 * Whether the settings for the reaction currently being processed were changed.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $changed = false;

	/**
	 * @since 2.4.0
	 */
	public function run() {

		$reaction_stores = wordpoints_hooks()->get_reaction_stores( 'points' );

		foreach ( $reaction_stores as $reaction_store ) {

			$reactions = $reaction_store->get_reactions();

			foreach ( $reactions as $reaction ) {

				$conditions = $reaction->get_meta( 'conditions' );

				if ( empty( $conditions ) ) {
					continue;
				}

				foreach ( $conditions as $action_type => $args ) {
					$conditions[ $action_type ] = $this->walk_args( $args );
				}

				if ( $this->changed ) {
					$reaction->update_meta( 'conditions', $conditions );
					$this->changed = false;
				}
			}
		}
	}

	/**
	 * Walks over an arg hierarchy and process the conditions embedded in it.
	 *
	 * @since 2.4.0
	 *
	 * @param array $args The arg hierarchy to walk.
	 *
	 * @return mixed array The processed arg hierarchy.
	 */
	protected function walk_args( $args ) {

		foreach ( $args as $arg_slug => $sub_args ) {

			if ( '_conditions' === $arg_slug ) {
				continue;
			}

			if ( isset( $sub_args['_conditions'] ) && '}' === substr( $arg_slug, -1 ) ) {
				$sub_args['_conditions'] = $this->process_conditions(
					$sub_args['_conditions']
				);
			}

			$args[ $arg_slug ] = $this->walk_args( $sub_args );
		}

		return $args;
	}

	/**
	 * Processes an array of conditions.
	 *
	 * @since 2.4.0
	 *
	 * @param array $conditions The conditions to process.
	 *
	 * @return array The processed conditions.
	 */
	protected function process_conditions( $conditions ) {

		foreach ( $conditions as $index => $condition ) {

			if ( 'contains' === $condition['type'] ) {
				$condition['settings'] = $this->process_condition(
					$condition['settings']
				);
			}

			$conditions[ $index ] = $condition;
		}

		return $conditions;
	}

	/**
	 * Processes the settings of an entity array contains condition.
	 *
	 * @since 2.4.0
	 *
	 * @param array $settings The settings.
	 *
	 * @return array The processed settings.
	 */
	protected function process_condition( $settings ) {

		if ( isset( $settings['max'] ) && empty( $settings['max'] ) ) {

			$settings['max'] = 0;

			$this->changed = true;
		}

		if ( isset( $settings['conditions'] ) ) {
			$settings['conditions'] = $this->walk_args( $settings['conditions'] );
		}

		return $settings;
	}
}

// EOF
