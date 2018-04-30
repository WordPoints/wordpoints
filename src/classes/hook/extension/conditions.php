<?php

/**
 * Conditions hook extension class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Requires the event args to meet certain conditions for the target to be hit.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Extension_Conditions
	extends WordPoints_Hook_Extension
	implements WordPoints_Hook_UI_Script_Data_ProviderI {

	/**
	 * @since 2.1.0
	 */
	protected $slug = 'conditions';

	/**
	 * The conditions registry.
	 *
	 * @since 2.1.0
	 *
	 * @var WordPoints_Class_Registry_Children
	 */
	protected $conditions;

	/**
	 * @since 2.1.0
	 */
	public function __construct( $slug = null ) {

		parent::__construct( $slug );

		$this->conditions = wordpoints_hooks()->get_sub_app( 'conditions' );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_ui_script_data() {

		$conditions_data = array();

		foreach ( $this->conditions->get_all() as $data_type => $conditions ) {
			foreach ( $conditions as $slug => $condition ) {

				if ( ! ( $condition instanceof WordPoints_Hook_ConditionI ) ) {
					continue;
				}

				$conditions_data[ $data_type ][ $slug ] = array(
					'slug'      => $slug,
					'data_type' => $data_type,
					'title'     => $condition->get_title(),
					'fields'    => $condition->get_settings_fields(),
				);
			}
		}

		return array(
			'conditions' => $conditions_data,
			'l10n'       => array(
				'added_condition'   => __( 'Condition added.', 'wordpoints' ),
				'deleted_condition' => __( 'Condition removed.', 'wordpoints' ),
			),
		);
	}

	/**
	 * @since 2.1.0
	 */
	protected function validate_action_type_settings( $settings ) {
		return $this->validate_conditions( $settings );
	}

	/**
	 * Validate the conditions.
	 *
	 * @since 2.1.0
	 *
	 * @param array                      $conditions The args and their conditions.
	 * @param WordPoints_Hook_Event_Args $event_args The event args object.
	 *
	 * @return array The validated settings.
	 */
	public function validate_conditions( $conditions, WordPoints_Hook_Event_Args $event_args = null ) {

		if ( $event_args ) {
			$this->event_args = $event_args;
			$this->validator  = $event_args->get_validator();
		}

		if ( ! is_array( $conditions ) ) {

			$this->validator->add_error(
				__( 'Conditions do not match expected format.', 'wordpoints' )
			);

			return array();
		}

		foreach ( $conditions as $arg_slug => $sub_args ) {

			if ( '_conditions' === $arg_slug ) {

				$this->validator->push_field( $arg_slug );

				foreach ( $sub_args as $index => $settings ) {

					$this->validator->push_field( $index );

					$condition = $this->validate_condition( $settings );

					if ( $condition ) {
						$sub_args[ $index ] = $condition;
					}

					$this->validator->pop_field();
				}

				$this->validator->pop_field();

			} else {

				if ( ! $this->event_args->descend( $arg_slug ) ) {
					continue;
				}

				$sub_args = $this->validate_action_type_settings( $sub_args );

				$conditions[ $arg_slug ] = $sub_args;

				$this->event_args->ascend();
			}

			$conditions[ $arg_slug ] = $sub_args;

		} // End foreach ( $conditions ).

		return $conditions;
	}

	/**
	 * Validate a condition's settings.
	 *
	 * @since 2.1.0
	 *
	 * @param array $settings The condition settings.
	 *
	 * @return array|false The validated conditions settings, or false if unable to
	 *                     validate.
	 */
	protected function validate_condition( $settings ) {

		if ( ! isset( $settings['type'] ) ) {
			$this->validator->add_error( __( 'Condition type is missing.', 'wordpoints' ) );
			return false;
		}

		$arg = $this->event_args->get_current();

		$data_type = $this->get_data_type( $arg );

		if ( ! $data_type ) {
			$this->validator->add_error(
				__( 'This type of condition does not work for the selected attribute.', 'wordpoints' )
			);

			return false;
		}

		/** @var WordPoints_Hook_ConditionI $condition */
		$condition = $this->conditions->get( $data_type, $settings['type'] );

		if ( ! $condition ) {

			$this->validator->add_error(
				sprintf(
					// translators: Condition type slug.
					__( 'Unknown condition type &#8220;%s&#8221;.', 'wordpoints' )
					, $settings['type']
				)
				, 'type'
			);

			return false;
		}

		if ( ! isset( $settings['settings'] ) ) {
			$this->validator->add_error( __( 'Condition settings are missing.', 'wordpoints' ) );
			return false;
		}

		$this->validator->push_field( 'settings' );

		// The condition may call this object's validate_settings() method to
		// validate some sub-conditions. When that happens, these properties will be
		// reset, so we need to back up their values and then restore them below.
		$backup = array( $this->validator, $this->event_args );

		$settings['settings'] = $condition->validate_settings(
			$arg
			, $settings['settings']
			, $this->validator
		);

		list( $this->validator, $this->event_args ) = $backup;

		$this->validator->pop_field();

		return $settings;
	}

	/**
	 * @since 2.1.0
	 */
	public function should_hit( WordPoints_Hook_Fire $fire ) {

		$conditions = $this->get_settings_from_fire( $fire );

		if ( $conditions && ! $this->conditions_are_met( $conditions, $fire->event_args ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the event args meet the conditions.
	 *
	 * @since 2.1.0
	 *
	 * @param array                      $conditions The conditions.
	 * @param WordPoints_Hook_Event_Args $event_args The event args.
	 *
	 * @return bool Whether the conditions are met.
	 */
	public function conditions_are_met(
		$conditions,
		WordPoints_Hook_Event_Args $event_args
	) {

		foreach ( $conditions as $arg_slug => $sub_args ) {

			$event_args->descend( $arg_slug );

			if ( isset( $sub_args['_conditions'] ) ) {

				foreach ( $sub_args['_conditions'] as $settings ) {

					$condition = $this->conditions->get(
						$this->get_data_type( $event_args->get_current() )
						, $settings['type']
					);

					$is_met = $condition->is_met( $settings['settings'], $event_args );

					if ( ! $is_met ) {
						$event_args->ascend();
						return false;
					}
				}

				unset( $sub_args['_conditions'] );
			}

			$are_met = true;

			if ( ! empty( $sub_args ) ) {
				$are_met = $this->conditions_are_met( $sub_args, $event_args );
			}

			$event_args->ascend();

			if ( ! $are_met ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the data type of an entity.
	 *
	 * @since 2.1.0
	 *
	 * @param WordPoints_EntityishI $arg An entity object.
	 *
	 * @return string|false The data type, or false.
	 */
	protected function get_data_type( $arg ) {

		if ( $arg instanceof WordPoints_Entity_Attr ) {
			$data_type = $arg->get_data_type();
		} elseif ( $arg instanceof WordPoints_Entity_Array ) {
			$data_type = 'entity_array';
		} elseif ( $arg instanceof WordPoints_Entity ) {
			$data_type = 'entity';
		} else {
			$data_type = false;
		}

		return $data_type;
	}
}

// EOF
