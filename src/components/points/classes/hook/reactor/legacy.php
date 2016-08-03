<?php

/**
 * Legacy points hook reactor class.
 *
 * @package WordPoints\Points
 * @since 2.1.0
 */

/**
 * Hook reactor to award user points on legacy sites.
 *
 * @since 2.1.0
 */
class WordPoints_Points_Hook_Reactor_Legacy extends WordPoints_Points_Hook_Reactor {

	/**
	 * @since 2.1.0
	 */
	protected $slug = 'points_legacy';

	/**
	 * @since 2.1.0
	 */
	public function update_settings(
		WordPoints_Hook_ReactionI $reaction,
		array $settings
	) {

		if ( isset( $settings['legacy_log_type'] ) ) {
			$reaction->update_meta(
				'legacy_log_type',
				$settings['legacy_log_type']
			);
		}

		if ( isset( $settings['legacy_meta_key'] ) ) {
			$reaction->update_meta(
				'legacy_meta_key',
				$settings['legacy_meta_key']
			);
		}

		parent::update_settings( $reaction, $settings );
	}

	/**
	 * @since 2.1.0
	 */
	public function get_ui_script_data() {

		$data = parent::get_ui_script_data();

		$data['reversals_extension_slug'] = 'points_legacy_reversals';

		return $data;
	}

	/**
	 * @since 2.1.0
	 */
	public function reverse_hit( WordPoints_Hook_Fire $fire ) {

		if ( isset( $fire->data['points_legacy_reversals']['points_logs'] ) ) {

			$this->reverse_logs(
				$fire->data['points_legacy_reversals']['points_logs']
				, $fire
			);

		} else {
			parent::reverse_hit( $fire );
		}
	}

	/**
	 * @since 2.1.0
	 */
	protected function get_hit_ids_to_be_reversed( WordPoints_Hook_Fire $fire ) {

		// We closely integrate with the legacy reversals extension to get the IDs.
		if ( ! isset( $fire->data['points_legacy_reversals']['hit_ids'] ) ) {
			return array();
		}

		return $fire->data['points_legacy_reversals']['hit_ids'];
	}
}

// EOF
