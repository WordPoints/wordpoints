<?php

/**
 * Rank list shortcode class.
 *
 * @package WordPoints\Ranks
 * @since   2.4.0
 */

/**
 * Handler for the rank list shortcode.
 *
 * @since 2.4.0
 */
class WordPoints_Rank_Shortcode_Rank_List extends WordPoints_Rank_Shortcode {

	/**
	 * @since 2.4.0
	 */
	protected $pairs = array(
		'rank_group' => '',
	);

	/**
	 * @since 2.4.0
	 */
	protected function generate() {

		$headings  = '<tr>';
		$headings .= '<th>';
		$headings .= esc_html__( 'Rank Name', 'wordpoints' );
		$headings .= '</th>';
		$headings .= '<th>';
		$headings .= esc_html__( 'Description', 'wordpoints' );
		$headings .= '</th>';
		$headings .= '</tr>';

		$result  = '<table class="wordpoints-ranks-list">';
		$result .= '<thead>';
		$result .= $headings;
		$result .= '</thead>';

		$group = WordPoints_Rank_Groups::get_group( $this->atts['rank_group'] );

		foreach ( $group->get_ranks() as $rank_id ) {

			$rank = wordpoints_get_rank( $rank_id );

			$result .= '<tr>';
			$result .= '<td>';
			$result .= esc_html( $rank->name );
			$result .= '</td>';

			$type = WordPoints_Rank_Types::get_type( $rank->type );

			if ( $type instanceof WordPoints_Rank_Type_Rank_DescribingI ) {
				$description = $type->get_rank_description( $rank );
			} else {
				$description = '';
			}

			$result .= '<td>';
			$result .= esc_html( $description );
			$result .= '</td>';
			$result .= '</tr>';
		}

		$result .= '<tfoot>';
		$result .= $headings;
		$result .= '</tfoot>';
		$result .= '</table>';

		return $result;
	}
}

// EOF
