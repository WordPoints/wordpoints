<?php

/**
 * Shortcodes of the ranks component.
 *
 * @package WordPoints\Ranks
 * @since 1.8.0
 */

/**
 * Handler for the user rank shortcode.
 *
 * @since 1.8.0
 */
class WordPoints_User_Rank_Shortcode extends WordPoints_Rank_Shortcode {

	/**
	 * @since 1.8.0
	 */
	protected $shortcode = 'wordpoints_user_rank';

	/**
	 * @since 1.8.0
	 */
	protected $pairs = array(
		'user_id'    => 0,
		'rank_group' => '',
	);

	/**
	 * @since 1.8.0
	 */
	protected function generate() {

		return wordpoints_get_formatted_user_rank(
			$this->atts['user_id']
			, $this->atts['rank_group']
			, 'user_rank_shortcode'
		);
	}
}

// EOF
