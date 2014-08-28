<?php

/**
 * Register scripts and styles.
 *
 * These are all registered here so they may be easily enqueued when needed.
 *
 * Component-specific styles/scripts are enqueued separately by their respective
 * components.
 *
 * @package WordPoints
 * @since 1.0.0
 */

/**
 * Register scripts and styles.
 *
 * It is run on both the front and back end with a priority of 5, so the scripts will
 * all be registered when we want to enqueue them, usually on the default priority of
 * 10.
 *
 * @since 1.0.0
 *
 * @action wp_enqueue_scripts    5 Front-end scripts enqueued.
 * @action admin_enqueue_scripts 5 Admin scripts enqueued.
 */
function wordpoints_register_scripts() {

	$assets_url = WORDPOINTS_URL . '/assets/';

	// - JS

	// Back-compat, will be removed.
	wp_register_script(
		'wordpoints-datatables'
		,$assets_url . 'js/jquery.datatables.min.js'
		,array( 'jquery' )
		,'1.9.4'
	);

	// Back-compat, will be removed.
	wp_register_script(
		'wordpoints-datatables-init'
		,$assets_url . 'js/datatables-init.js'
		,array( 'wordpoints-datatables' )
		,WORDPOINTS_VERSION
	);

	// - CSS

	// Back-compat, will be removed.
	wp_register_style(
		'wordpoints-datatables'
		,$assets_url . 'css/datatables.css'
		,null
		,WORDPOINTS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'wordpoints_register_scripts', 5 );
add_action( 'admin_enqueue_scripts', 'wordpoints_register_scripts', 5 );

// EOF
