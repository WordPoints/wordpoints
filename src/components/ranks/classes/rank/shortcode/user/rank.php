<?php

/**
 * User rank shortcode class.
 *
 * @package WordPoints\Ranks
 * @since   2.3.0
 */

/**
 * Handler for the user rank shortcode.
 *
 * @since 1.8.0 As WordPoints_User_Rank_Shortcode.
 * @since 2.3.0
 */
class WordPoints_Rank_Shortcode_User_Rank extends WordPoints_Rank_Shortcode {

	/**
	 * @since 1.8.0 As part of WordPoints_User_Rank_Shortcode.
	 * @since 2.3.0
	 */
	protected $shortcode = 'wordpoints_user_rank';

	/**
	 * @since 1.8.0 As part of WordPoints_User_Rank_Shortcode.
	 * @since 2.3.0
	 */
	protected $pairs = array(
		'user_id'    => 0,
		'rank_group' => '',
	);

	/**
	 * @since 1.8.0 As part of WordPoints_User_Rank_Shortcode.
	 * @since 2.3.0
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
