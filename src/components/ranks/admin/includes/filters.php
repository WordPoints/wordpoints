<?php

/**
 * Administration-side action and filter hooks of the Ranks component.
 *
 * @package WordPoints\Ranks
 * @since 2.1.0
 */

add_action( 'init', 'wordpoints_ranks_admin_register_scripts' );

add_action( 'admin_menu', 'wordpoints_ranks_admin_menu' );

add_action( 'load-wordpoints_page_wordpoints_ranks', 'wordpoints_ranks_admin_screen_load' );


// EOF
