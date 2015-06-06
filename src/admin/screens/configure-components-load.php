<?php

/**
 * Set up for the WordPoints > Components administration screen.
 *
 * @package WordPoints\Administration
 * @since 1.1.0
 */

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

if (
	'components' !== wordpoints_admin_get_current_tab()
	|| ! isset( $_POST['wordpoints_component'], $_POST['wordpoints_component_action'], $_POST['_wpnonce'] )
) {
	return;
}

$components = WordPoints_Components::instance();
$component  = sanitize_key( $_POST['wordpoints_component'] );

$action = sanitize_key( $_POST['wordpoints_component_action'] );

check_admin_referer( "wordpoints_{$action}_component-{$component}" );

switch ( $action ) {

	case 'activate':
		if ( $components->activate( $component ) ) {

			$message = array( 'message' => 1 );

		} else {

			$message = array( 'error' => 1 );
		}
	break;

	case 'deactivate':
		if ( $components->deactivate( $component ) ) {

			$message = array( 'message' => 2 );

		} else {

			$message = array( 'error' => 2 );
		}
	break;

	default: return;
}

wp_safe_redirect(
	add_query_arg(
		$message + array(
			'page'                 => 'wordpoints_configure',
			'tab'                  => 'components',
			'wordpoints_component' => $component,
			'_wpnonce'             => wp_create_nonce( 'wordpoints_component_' . key( $message ) . "-{$component}" ),
		)
		, self_admin_url( 'admin.php' )
	)
);

exit;

// EOF
