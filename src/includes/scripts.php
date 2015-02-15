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
function wordpoints_register_scripts() {}
add_action( 'wp_enqueue_scripts', 'wordpoints_register_scripts', 5 );
add_action( 'admin_enqueue_scripts', 'wordpoints_register_scripts', 5 );

// EOF
