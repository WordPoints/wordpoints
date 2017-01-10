<?php

/**
 * Hook condition class.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Bootstrap for hook conditions.
 *
 * @since 2.1.0
 */
abstract class WordPoints_Hook_Condition implements WordPoints_Hook_ConditionI {

	/**
	 * @since 2.1.0
	 */
	public function validate_settings(
		$arg,
		array $settings,
		WordPoints_Hook_Reaction_Validator $validator
	) {

		if ( ! isset( $settings['value'] ) || '' === $settings['value'] ) {

			$settings_fields = $this->get_settings_fields();

			$validator->add_error(
				sprintf(
					// translators: Form field name.
					__( '%s is required.', 'wordpoints' )
					, $settings_fields['value']['label']
				)
				, 'value'
			);

		} elseif ( $arg instanceof WordPoints_Entity_Attr ) {

			$data_types = wordpoints_apps()->get_sub_app( 'data_types' );

			$data_type = $data_types->get( $arg->get_data_type() );

			// If this data type isn't recognized, that's probably OK. Validation is
			// just to help the user know that they've made a mistake anyway.
			if ( ! ( $data_type instanceof WordPoints_Data_TypeI ) ) {
				return $settings;
			}

			$validated_value = $data_type->validate_value( $settings['value'] );

			if ( is_wp_error( $validated_value ) ) {

				$settings_fields = $this->get_settings_fields();

				$validator->add_error(
					sprintf(
						$validated_value->get_error_message()
						, $settings_fields['value']['label']
					)
					, 'value'
				);

				return $settings;
			}

			$settings['value'] = $validated_value;

		} elseif ( $arg instanceof WordPoints_Entity ) {

			if ( ! $arg->exists( $settings['value'] ) ) {
				$validator->add_error(
					sprintf(
						// translators: 1. Singular item type name; 2. Item ID/slug.
						__( '%1$s &#8220;%2$s&#8221; not found.', 'wordpoints' )
						, $arg->get_title()
						, $settings['value']
					)
					, 'value'
				);
			}

		} // End if ( missing ) elseif ( attribute ) elseif ( entity ).

		return $settings;
	}
}

// EOF
