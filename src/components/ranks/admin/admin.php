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
		, array( 'dashicons', 'wp-jquery-ui-dialog' )
		, WORDPOINTS_VERSION
	);

	// JS

	wp_register_script(
		'wordpoints-ranks-admin'
		, $assets_url . '/js/ranks-screen.js'
		, array( 'backbone', 'jquery-ui-dialog', 'wp-util' )
		, WORDPOINTS_VERSION
	);

	wp_localize_script(
		'wordpoints-ranks-admin'
		, 'WordPointsRanksAdminL10n'
		, array(
			'unexpectedError' => __( 'There was an unexpected error. Try reloading the page.', 'wordpoints' ),
			'changesSaved'    => __( 'Your changes have been saved.', 'wordpoints' ),
			'emptyName'       => __( 'A rank title cannot be empty.', 'wordpoints' ),
			'confirmDelete'   => __( 'Are you sure that you want to delete this rank? This action cannot be undone.', 'wordpoints' ),
			'confirmTitle'    => __( 'Are you sure?', 'wordpoints' ),
			'deleteText'      => __( 'Delete', 'wordpoints' ),
			'cancelText'      => __( 'Cancel', 'wordpoints' ),
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

	// Add help and screen options tabs.
	$screen = get_current_screen();

	$screen->add_help_tab(
		array(
			'id'      => 'overview',
			'title'   => esc_html__( 'Overview', 'wordpoints' ),
			'content' =>
				'<p>' . esc_html__( 'Ranks are titles assigned to users. A set of ranks are organized into a hierarchy. The user starts at the bottom, but can work his way up to higher ranks. Each rank has requirements that a user must meet before he can move up to that rank. For example, the user may need to have a certain number of points to reach a given rank.', 'wordpoints' ) . '</p>'
				. '<p>' . esc_html__( 'More than one rank hierarchy may be available. For example, if you have multiple types of points, you will have a group of ranks for each points type. Each rank group is managed separately, on a different tab on this screen.', 'wordpoints' ) . '</p>'
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'adding-editing',
			'title'   => esc_html__( 'Adding and Editing', 'wordpoints' ),
			'content' =>
				'<p>' . esc_html__( 'To add a new rank to the rank group, click the Add Rank button. A new rank will appear with its settings form open. Fill out its settings and click the save button. If you have changed your mind, you can remove the new rank without saving it by clicking the Cancel button.', 'wordpoints' ) . '</p>
				<p>' . esc_html__( 'To edit an existing rank&#8217;s settings, click on the Edit link in its title bar. The settings form for that rank will open. You can close the settings form again by clicking the Close link.', 'wordpoints' ) . '</p>
				<p>' . esc_html__( 'Once you have modified the rank&#8217;s settings as desired, you can save your changes by clicking the Save button. Keep in mind that your changes will take effect immediately, and it is wise to double check that you have all of the settings just as you want them. If you decide that you don&#8217;t want to save your changes, click the Cancel button instead, which will reset the form to the rank&#8217;s current settings.', 'wordpoints' ) . '</p>'
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'deleting',
			'title'   => esc_html__( 'Deleting', 'wordpoints' ),
			'content' =>
				'<p>' . esc_html__( 'If you would like to delete a rank, open its settings form, then click the Delete button. You will be asked to confirm that you want to delete the rank. If you are not sure, click the Cancel button in the confirmation dialog. Once you delete the rank, it is gone!', 'wordpoints' ) . '</p>'
		)
	);

	$screen->set_help_sidebar(
		'<p><strong>' . esc_html__( 'For more information:', 'wordpoints' ) . '</strong></p>' .
		'<p><a href="http://wordpoints.org/user-guide/ranks/" target="_blank">' . esc_html__( 'Documentation on Ranks', 'wordpoints' ) . '</a></p>' .
		'<p><a href="http://wordpress.org/support/plugin/wordpoints" target="_blank">' . esc_html__( 'Support Forums', 'wordpoints' ) . '</a></p>'
	);
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
