<?php

/**
 * Administration-side code of the ranks component.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

/**
 * Admin-side Ajax callbacks of the ranks component.
 *
 * @since 1.7.0
 */
include_once( WORDPOINTS_DIR . 'components/ranks/admin/includes/ajax.php' );

/**
 * Register ranks component admin scripts.
 *
 * @since 1.7.0
 */
function wordpoints_ranks_admin_register_scripts() {

	$assets_url = WORDPOINTS_URL . '/components/ranks/admin/assets';

	// CSS

	wp_register_style(
		'wordpoints-ranks-admin'
		, $assets_url . '/css/ranks-screen.css'
		, array( 'dashicons' )
		, WORDPOINTS_VERSION
	);

	// JS

	wp_register_script(
		'wordpoints-ranks-admin'
		, $assets_url . '/js/ranks-screen.js'
		, array( 'backbone' )
		, WORDPOINTS_VERSION
	);

	wp_localize_script(
		'wordpoints-ranks-admin'
		, 'WordPointsRanksAdminL10n'
		, array(
			'unexpectedError' => __( 'There was an unexpected error. Try reloading the page.', 'wordpoints' ),
			'changesSaved'    => __( 'Your changes have been saved.', 'wordpoints' ),
			'invalidFields'   => __( 'Some of the values you entered are invalid. Please correct them, and then try saving again.', 'wordpoints' ),
			'emptyName'       => __( 'A rank title cannot be empty.', 'wordpoints' ),
		)
	);

	wp_localize_script(
		'wordpoints-ranks-admin'
		, 'WordPointsRanksAdminData'
		, array( 'ranks' => WordPoints_Ranks_Admin_Screen_Ajax::prepare_all_ranks() )
	);
}
add_action( 'init', 'wordpoints_ranks_admin_register_scripts' );

/**
 * Add ranks admin screens to the administration menu.
 *
 * @since 1.7.0
 *
 * @action admin_menu
 */
function wordpoints_ranks_admin_menu() {

	$wordpoints_menu = wordpoints_get_main_admin_menu();

	// Ranks screen.
	add_submenu_page(
		$wordpoints_menu
		,__( 'WordPoints — Ranks', 'wordpoints' )
		,esc_html__( 'Ranks', 'wordpoints' )
		,'manage_options'
		,'wordpoints_ranks'
		,'wordpoints_ranks_admin_screen'
	);
}
add_action( 'admin_menu', 'wordpoints_ranks_admin_menu' );

/**
 * Set up for the ranks admin screen.
 *
 * @since 1.7.0
 *
 * @action load-wordpoints_page_wordpoints_ranks
 */
function wordpoints_ranks_admin_screen_load() {

	wp_enqueue_style( 'wordpoints-ranks-admin' );
	wp_enqueue_script( 'wordpoints-ranks-admin' );
}
add_action( 'load-wordpoints_page_wordpoints_ranks', 'wordpoints_ranks_admin_screen_load' );

/**
 * Display the points hooks admin page.
 *
 * @since 1.7.0
 */
function wordpoints_ranks_admin_screen() {

	/**
	 * The ranks admin screen.
	 *
	 * @since 1.7.0
	 */
	include WORDPOINTS_DIR . 'components/ranks/admin/screens/ranks.php';
}

// EOF
