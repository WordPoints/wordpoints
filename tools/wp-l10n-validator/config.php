<?php

/**
 * Configure the WP L10n validator.
 */

WP_L10n_Validator::register_config_callback( 'wordpoints_l10n_validator_config' );

/**
 * Configure the l10n parser for WordPoints.
 *
 * @since $ver$
 *
 * @param WP_L10n_Validator The L10n validator.
 */
function wordpoints_l10n_validator_config( $parser ) {
/*
	$parser->add_ignored_functions(
		array(
			// Functions.
			'wordpoints_add_points'       => true,
			'wordpoints_alter_points'     => true,
			'wordpoints_debug_message'              => true, // This could change in future.
			'wordpoints_dir_include'                => true,
			'wordpoints_display_points'             => true,
			'wordpoints_format_points'              => true,
			'wordpoints_get_array_option' => true,
			'wordpoints_get_excluded_users'         => true,
			'wordpoints_get_formatted_points'       => true,
			'wordpoints_get_points_logs_query'      => true,
			'wordpoints_get_points_logs_query_args' => true,
			'wordpoints_get_points_type_setting'    => true,
			'wordpoints_list_post_types'            => true,
			'wordpoints_prepare__in'      => true,
			'wordpoints_register_points_logs_query' => true,
			'wordpoints_show_points_logs_query'     => true,
			'wordpoints_subtract_points'  => true,
			// Static calls.
			'WordPoints_Points_Hooks::register'         => true,
			'WordPoints_Points_Hooks::points_type_form' => true,
			// Instance calls.
			'$this->_prepare__in'        => true,
			'$this->_prepare_posint__in' => true,
			'$this->get_field_id'        => true,
			'$this->get_field_name'      => true,
			'$this->the_field_id'        => true,
			'$this->the_field_name'      => true,
			// New instance calls.
			'new WordPoints_Points_Logs_Query' => true,
		)
	);

	$parser->add_ignored_args(
		array(
			'wordpoints_show_admin_message' => array(   2 ),
			'wordpoints_enqueue_datatables' => array( 1 ),
		)
	);
*/
	$parser->add_ignored_strings(
		array(
			'-',
			'_',
			'nav-tab-active',
			'adding-modules',
			'" href="?page=',
			'&amp;tab=',
			'hook-',
			'wordpoints_hook-',
			'][',
			'hook-content',
			'hook-control-noform',
			'id',
			'SELECT',
			'SELECT COUNT(*)',
			'DESC',
			'ASC',
			'SELECT COUNT',
			'WHERE',
			'LIMIT 1',
			'=',
			'>',
			'<>',
			'!=',
			'>=',
			'<=',
			'widefat',
			'%points%',
			'ALL',
		)
	);
}
