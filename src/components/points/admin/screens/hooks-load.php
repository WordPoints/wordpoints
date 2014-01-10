<?php

/**
 * Points hooks screen load.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.2.0
 */

global $wp_version;

// Add help and screen options tabs.
$screen = get_current_screen();

$screen->add_help_tab(
	array(
		'id'      => 'overview',
		'title'   => __( 'Overview', 'wordpoints' ),
		'content' =>
			'<p>' . __( 'Points Hooks let you award users points by "hooking into" different actions. They can be hooked to any points type that you have created. To create a points type, fill out the Add New Points Type form, and click Save. You can edit the settings for your points type at any time by clicking on the Settings title bar within that points type section.', 'wordpoints' ) . '</p>
			<p>' . __( "To link a hook to a points type, click on the hook's title bar and select a points type, or drag and drop the hook title bars into the desired points type. By default, only the first points type area is expanded. To populate additional points types, click on their title bars to expand them.", 'wordpoints' ) . '</p>
			<p>' . __( 'The Available Hooks section contains all the hooks you can choose from. Once you add a hook into a points type, it will open to allow you to configure its settings. When you are happy with the hook settings, click the Save button and the hook will begin awarding points. If you click Delete, it will remove the hook.', 'wordpoints' ) . '</p>'
	)
);

$screen->add_help_tab(
	array(
		'id'      => 'removing-reusing',
		'title'   => __( 'Removing and Reusing', 'wordpoints' ),
		'content' =>
			'<p>' . __( 'If you want to remove the hook but save its setting for possible future use, just drag it into the Inactive Hooks area. You can add them back anytime from there.', 'wordpoints' ) . '</p>
			<p>' . __( 'Hooks may be used multiple times.', 'wordpoints' ) . '</p>
			<p>' . __( 'Enabling Accessibility Mode, via Screen Options, allows you to use Add and Edit buttons instead of using drag and drop.', 'wordpoints' ) . '</p>'
	)
);

$accessibility_mode = get_user_setting( 'wordpoints_points_hooks_access' );

if ( isset( $_GET['accessibility-mode'] ) ) {

	$accessibility_mode = ( 'on' == $_GET['accessibility-mode'] ) ? 'on' : 'off';
	set_user_setting( 'wordpoints_points_hooks_access', $accessibility_mode );
}

// Enqueue needed scripts and styles.
if ( 'on' == $accessibility_mode ) {

	add_filter( 'admin_body_class', 'wordpoints_points_hooks_access_body_class' );

} else {

	wp_enqueue_style( 'wp-jquery-ui-dialog' );

	wp_enqueue_script( 'jquery-ui-droppable' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'jquery-ui-dialog' );

	wp_enqueue_script(
		'wordpoints-admin-points-hooks'
		,plugins_url( 'assets/js/hooks.js', dirname( __FILE__ ) )
		,array( 'jquery', 'jquery-ui-droppable', 'jquery-ui-sortable', 'jquery-ui-dialog' )
		,WORDPOINTS_VERSION
	);

	wp_localize_script(
		'wordpoints-admin-points-hooks'
		,'WordPointsHooksL10n'
		,array(
			'confirmDelete' => __( 'Are you sure that you want to delete this points type? This will delete all related logs and hooks.', 'wordpoints' )
				. ' ' . __( 'Once a points type has been deleted, you cannot bring it back.', 'wordpoints' ),
			'confirmTitle'  => __( 'Are you sure?', 'wordpoints' ),
			'deleteText'    => __( 'Delete', 'wordpoints' ),
			'cancelText'    => __( 'Cancel', 'wordpoints' ),
		)
	);

	if ( wp_is_mobile() ) {
		wp_enqueue_script( 'jquery-touch-punch' );
	}
}

$deps = null;

if ( version_compare( $wp_version, '3.8', '>=' ) ) {

	$deps = array( 'dashicons' );

} else {

	wp_enqueue_style(
		'wordpoints-admin-points-hooks-legacy'
		, plugins_url( 'assets/css/hooks-legacy.css', dirname( __FILE__ ) )
		, array( 'wordpoints-admin-points-hooks' )
		, WORDPOINTS_VERSION
	);
}

wp_enqueue_style(
	'wordpoints-admin-points-hooks'
	, plugins_url( 'assets/css/hooks.css', dirname( __FILE__ ) )
	, $deps
	, WORDPOINTS_VERSION
);
