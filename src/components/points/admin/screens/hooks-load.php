<?php

/**
 * Points hooks screen load.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.2.0
 */

// Add help and screen options tabs.
$screen = get_current_screen();

$screen->add_help_tab(
	array(
		'id'      => 'overview',
		'title'   => esc_html__( 'Overview', 'wordpoints' ),
		'content' =>
			'<p>' . esc_html__( 'Points Hooks let you award users points by &#8220;hooking into&#8221; different actions. They can be hooked to any points type that you have created. To create a points type, fill out the Add New Points Type form, and click Save. You can edit the settings for your points type at any time by clicking on the Settings title bar within that points type section.', 'wordpoints' ) . '</p>
			<p>' . esc_html__( 'To link a hook to a points type, click on the hook&#8217;s title bar and select a points type, or drag and drop the hook title bars into the desired points type. By default, only the first points type area is expanded. To populate additional points types, click on their title bars to expand them.', 'wordpoints' ) . '</p>
			<p>' . esc_html__( 'The Available Hooks section contains all the hooks you can choose from. Once you add a hook into a points type, it will open to allow you to configure its settings. When you are happy with the hook settings, click the Save button and the hook will begin awarding points. If you click Delete, it will remove the hook.', 'wordpoints' ) . '</p>'
	)
);

$screen->add_help_tab(
	array(
		'id'      => 'removing-reusing',
		'title'   => esc_html__( 'Removing and Reusing', 'wordpoints' ),
		'content' =>
			'<p>' . esc_html__( 'If you want to remove the hook but save its setting for possible future use, just drag it into the Inactive Hooks area. You can add them back anytime from there.', 'wordpoints' ) . '</p>
			<p>' . esc_html__( 'Hooks may be used multiple times.', 'wordpoints' ) . '</p>
			<p>' . esc_html__( 'Enabling Accessibility Mode, via Screen Options, allows you to use Add and Edit buttons instead of using drag and drop.', 'wordpoints' ) . '</p>'
	)
);

$screen->set_help_sidebar(
	'<p><strong>' . esc_html__( 'For more information:', 'wordpoints' ) . '</strong></p>' .
	'<p><a href="http://wordpoints.org/user-guide/points-hooks/" target="_blank">' . esc_html__( 'Documentation on Points Hooks', 'wordpoints' ) . '</a></p>' .
	'<p><a href="http://wordpress.org/support/plugin/wordpoints" target="_blank">' . esc_html__( 'Support Forums', 'wordpoints' ) . '</a></p>'
);

$accessibility_mode = get_user_setting( 'wordpoints_points_hooks_access' );

if (
	isset( $_GET['accessibility-mode'], $_GET['wordpoints-accessiblity-nonce'] )
	&& wp_verify_nonce( $_GET['wordpoints-accessiblity-nonce'], 'wordpoints_points_hooks_accessiblity' )
) {

	$accessibility_mode = ( 'on' === $_GET['accessibility-mode'] ) ? 'on' : 'off';
	set_user_setting( 'wordpoints_points_hooks_access', $accessibility_mode );
}

// Enqueue needed scripts and styles.
if ( 'on' === $accessibility_mode ) {

	add_filter( 'admin_body_class', 'wordpoints_points_hooks_access_body_class' );

} else {

	wp_enqueue_style( 'wp-jquery-ui-dialog' );

	wp_enqueue_script( 'wordpoints-admin-points-hooks' );

	if ( wp_is_mobile() ) {
		wp_enqueue_script( 'jquery-touch-punch' );
	}
}

wp_enqueue_style( 'wordpoints-admin-points-hooks' );

// EOF
