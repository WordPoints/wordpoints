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
			'<p>' . esc_html__( 'WordPoints doesn&#8217;t have a lot of configuration options, which makes it simple and easy to use.', 'wordpoints' ) . '</p>
			<p>' . esc_html__( 'The settings provided below are optional, and are mainly for convenience', 'wordpoints' ) . '</p>
			<p>' . esc_html__( 'If you need more help getting started with WordPoints, see the links in the help sidebar.', 'wordpoints' ) . '</p>'
	)
);

$screen->set_help_sidebar(
	'<p><strong>' . esc_html__( 'For more information:', 'wordpoints' ) . '</strong></p>' .
	'<p><a href="http://wordpoints.org/user-guide/" target="_blank">' . esc_html__( 'User Guide', 'wordpoints' ) . '</a></p>' .
	'<p><a href="http://wordpress.org/support/plugin/wordpoints" target="_blank">' . esc_html__( 'Support Forums', 'wordpoints' ) . '</a></p>'
);

// EOF
