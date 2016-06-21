<?php

/**
 * Class for hook reactions whose settings are stored as options.
 *
 * @package WordPoints\Hooks
 * @since 2.1.0
 */

/**
 * Represents a hook reaction whose settings are stored using the options API.
 *
 * @since 2.1.0
 */
class WordPoints_Hook_Reaction_Options extends WordPoints_Hook_Reaction {

	/**
	 * @since 2.1.0
	 *
	 * @var WordPoints_Hook_Reaction_Store_Options
	 */
	protected $store;

	/**
	 * @since 2.1.0
	 */
	public function get_event_slug() {
		return $this->store->get_reaction_event_from_index( $this->ID );
	}

	/**
	 * @since 2.1.0
	 */
	public function update_event_slug( $event_slug ) {
		return $this->store->update_reaction_event_in_index(
			$this->ID
			, $event_slug
		);
	}

	/**
	 * @since 2.1.0
	 */
	public function get_meta( $key ) {

		$settings = $this->get_settings();

		if ( ! is_array( $settings ) || ! isset( $settings[ $key ] ) ) {
			return false;
		}

		return $settings[ $key ];
	}

	/**
	 * @since 2.1.0
	 */
	public function add_meta( $key, $value ) {

		$settings = $this->get_settings();

		if ( ! is_array( $settings ) || isset( $settings[ $key ] ) ) {
			return false;
		}

		$settings[ $key ] = $value;

		return $this->update_settings( $settings );
	}

	/**
	 * @since 2.1.0
	 */
	public function update_meta( $key, $value ) {

		$settings = $this->get_settings();

		if ( ! is_array( $settings ) ) {
			return false;
		}

		$settings[ $key ] = $value;

		return $this->update_settings( $settings );
	}

	/**
	 * @since 2.1.0
	 */
	public function delete_meta( $key ) {

		$settings = $this->get_settings();

		if ( ! is_array( $settings ) || ! isset( $settings[ $key ] ) ) {
			return false;
		}

		unset( $settings[ $key ] );

		return $this->update_settings( $settings );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_all_meta() {
		return $this->get_settings();
	}

	/**
	 * Gets the settings for this reaction from the database.
	 *
	 * @since 2.1.0
	 *
	 * @return array|false The settings, or false if none.
	 */
	protected function get_settings() {

		return $this->store->get_option(
			$this->store->get_settings_option_name( $this->ID )
		);
	}

	/**
	 * Updates the settings for this reaction.
	 *
	 * @since 2.1.0
	 *
	 * @param array $settings The settings for this reaction.
	 *
	 * @return bool Whether the settings were updated successfully.
	 */
	protected function update_settings( $settings ) {

		return $this->store->update_option(
			$this->store->get_settings_option_name( $this->ID )
			, $settings
		);
	}
}

// EOF
