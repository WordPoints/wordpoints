<?php

/**
 * How To Get Points shortcode class.
 *
 * @package WordPoints
 * @since   2.3.0
 */

/**
 * Handler for the How To Get Points shortcode.
 *
 * @since 1.8.0 As WordPoints_How_To_Get_Points_Shortcode.
 * @since 2.3.0
 */
class WordPoints_Points_Shortcode_HTGP extends WordPoints_Points_Shortcode {

	/**
	 * @since 1.8.0 As part of WordPoints_How_To_Get_Points_Shortcode.
	 * @since 2.3.0
	 */
	protected $shortcode = 'wordpoints_how_to_get_points';

	/**
	 * @since 1.8.0 As part of WordPoints_How_To_Get_Points_Shortcode.
	 * @since 2.3.0
	 */
	protected $pairs = array(
		'points_type' => '',
	);

	/**
	 * @since 1.8.0 As part of WordPoints_How_To_Get_Points_Shortcode.
	 * @since 2.3.0
	 */
	protected function generate() {

		/**
		 * Filter the extra HTML classes for the how-to-get-points table element.
		 *
		 * @since 1.6.0
		 *
		 * @param string[] $extra_classes The extra classes for the table element.
		 * @param array    $atts          The arguments for table display from the shortcode.
		 */
		$extra_classes = apply_filters( 'wordpoints_how_to_get_points_table_extra_classes', array(), $this->atts );

		$points_heading = _x( 'Points', 'column name', 'wordpoints' );

		$points_type_name = wordpoints_get_points_type_setting(
			$this->atts['points_type']
			, 'name'
		);

		if ( ! empty( $points_type_name ) ) {
			$points_heading = $points_type_name;
		}

		$html = '<table class="wordpoints-how-to-get-points ' . esc_attr( implode( ' ', $extra_classes ) ) . '">
			<thead>
				<tr><th style="padding-right: 10px">' . esc_html( $points_heading ) . '</th>
				<th>' . esc_html_x( 'Action', 'column name', 'wordpoints' ) . '</th></tr>
			</thead>
			<tbody>';

		$html .= $this->list_reactions( 'points' );
		$html .= $this->list_points_hooks();

		if ( is_wordpoints_network_active() ) {

			WordPoints_Points_Hooks::set_network_mode( true );
			$html .= $this->list_points_hooks();
			WordPoints_Points_Hooks::set_network_mode( false );
		}

		$html .= '</tbody></table>';

		return $html;
	}

	/**
	 * List the points hooks for the current points type.
	 *
	 * @since 1.8.0 As part of WordPoints_How_To_Get_Points_Shortcode.
	 * @since 2.3.0
	 *
	 * @return string The HTML for the table rows.
	 */
	protected function list_points_hooks() {

		$network = '';

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$network = 'network_';
		}

		$hooks = WordPoints_Points_Hooks::get_points_type_hooks(
			$this->atts['points_type']
		);

		$html = '';

		foreach ( $hooks as $hook_id ) {

			$hook = WordPoints_Points_Hooks::get_handler( $hook_id );

			if ( ! $hook ) {
				continue;
			}

			$points = $hook->get_points( $network . $hook->get_number() );

			if ( ! $points ) {
				continue;
			}

			$points = wordpoints_format_points(
				$points
				, $this->atts['points_type']
				, 'how-to-get-points-shortcode'
			);

			$html .= '<tr>
				<td>' . $points . '</td>
				<td>' . esc_html( $hook->get_description() ) . '</td>
				</tr>';
		}

		return $html;
	}

	/**
	 * List the reactions in a set of reaction stores.
	 *
	 * Will list reactions from stores with this slug from all modes.
	 *
	 * @since 2.1.0 As part of WordPoints_How_To_Get_Points_Shortcode.
	 * @since 2.3.0
	 *
	 * @param string $store_slug The slug of the reaction stores to list reactions
	 *                           from.
	 *
	 * @return string The HTML for the table rows.
	 */
	protected function list_reactions( $store_slug ) {

		$html = '';

		foreach ( wordpoints_hooks()->get_reaction_stores( $store_slug ) as $store ) {

			foreach ( $store->get_reactions() as $reaction ) {

				if ( $reaction->get_meta( 'points_type' ) !== $this->atts['points_type'] ) {
					continue;
				}

				$points = $this->get_points_from_reaction( $reaction );

				if ( false === $points ) {
					continue;
				}

				$html .= '<tr>
					<td>' . wp_kses( $points, 'wordpoints_points_shortcode_htgp' ) . '</td>
					<td>' . esc_html( $reaction->get_meta( 'description' ) ) . '</td>
					</tr>';
			}
		}

		return $html;
	}

	/**
	 * Gets the value to show for a reaction in the points column.
	 *
	 * @since 2.3.0
	 *
	 * @param WordPoints_Hook_ReactionI $reaction The reaction object.
	 *
	 * @return string|false The value of the points column, or false to hide the row.
	 */
	protected function get_points_from_reaction( $reaction ) {

		$points = $reaction->get_meta( 'points' );

		if ( $points ) {
			$points = wordpoints_format_points(
				$points
				, $this->atts['points_type']
				, 'how-to-get-points-shortcode'
			);
		}

		/**
		 * Filters the value of the points column in the How To Get Points shortcode.
		 *
		 * A value of false will prevent the row from being displayed.
		 *
		 * @since 2.3.0
		 *
		 * @param string|false              $points   The value of the points column.
		 * @param WordPoints_Hook_ReactionI $reaction The reaction object.
		 */
		return apply_filters(
			'wordpoints_htgp_shortcode_reaction_points'
			, $points
			, $reaction
		);
	}
}

// EOF
