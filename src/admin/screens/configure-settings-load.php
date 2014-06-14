<?php

/**
 * Set up for the WordPoints » Configure administration screen.
 *
 * @package WordPoints\Administration
 * @since 1.5.0
 */

$screen = get_current_screen();

$screen->add_help_tab(
	array(
		'id'      => 'overview',
		'title'   => __( 'Overview', 'wordpoints' ),
		'content' =>
			'<p>' . __( 'WordPoints doesn&#8217;t have a lot of configuration options, which makes it simple and easy to use.', 'wordpoints' ) . '</p>
			<p>' . __( 'The settings provided below are optional, and are mainly for convenience', 'wordpoints' ) . '</p>
			<p>' . __( 'If you need more help getting started with WordPoints, see the links in the help sidebar.', 'wordpoints' ) . '</p>'
	)
);

$screen->set_help_sidebar(
	'<p><strong>' . __( 'For more information:', 'wordpoints' ) . '</strong></p>' .
	'<p>' . sprintf( __( '<a href="%s" target="_blank">User Guide</a>', 'wordpoints' ), 'http://wordpoints.org/user-guide/' ) . '</p>' .
	'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'wordpoints' ), 'http://wordpress.org/support/plugin/wordpoints' ) . '</p>'
);

// EOF
