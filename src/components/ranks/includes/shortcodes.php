<?php

/**
 * Shortcodes of the ranks component.
 *
 * @package WordPoints\Ranks
 * @since 1.8.0
 */

/**
 * Handler for rank shortcodes.
 *
 * @since 1.8.0
 */
abstract class WordPoints_Rank_Shortcode extends WordPoints_Shortcode {

	/**
	 * @since 1.8.0
	 */
	protected function verify_atts() {

		if ( isset( $this->pairs['rank_group'] ) && empty( $this->atts['rank_group'] ) ) {

			return sprintf(
				__( 'The &#8220;%s&#8221; attribute of the %s shortcode must be a rank group slug.', 'wordpoints' )
				,'rank_group'
				, "<code>[{$this->shortcode}]</code>"
			);
		}

		return parent::verify_atts();
	}
}

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
WordPoints_Shortcodes::register( 'wordpoints_user_rank', 'WordPoints_User_Rank_Shortcode' );

// EOF
