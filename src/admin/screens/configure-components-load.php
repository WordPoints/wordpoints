<?php

/**
 * Set up for the WordPoints > Components administration screen.
 *
 * @package WordPoints\Administration
 * @since 1.1.0
 */

if ( wordpoints_admin_get_current_tab() != 'components' || ! isset( $_POST['wordpoints_component'], $_POST['wordpoints_component_action'], $_POST['_wpnonce'] ) )
	return;

$components = WordPoints_Components::instance();

switch ( $_POST['wordpoints_component_action'] ) {

	case 'activate':
		if ( 1 == wp_verify_nonce( $_POST['_wpnonce'], "wordpoints_activate_component-{$_POST['wordpoints_component']}" ) && $components->activate( $_POST['wordpoints_component'] ) ) {

			$message = array( 'message' => 1 );

		} else {

			$message = array( 'error' => 1 );
		}
	break;

	case 'deactivate':
		if ( 1 == wp_verify_nonce( $_POST['_wpnonce'], "wordpoints_deactivate_component-{$_POST['wordpoints_component']}" ) && $components->deactivate( $_POST['wordpoints_component'] ) ) {

			$message = array( 'message' => 2 );

		} else {

			$message = array( 'error' => 2 );
		}
	break;

	default: return;
}

wp_redirect(
	add_query_arg(
		$message + array(
			'page'                 => 'wordpoints_configure',
			'tab'                  => 'components',
			'wordpoints_component' => $_POST['wordpoints_component'],
			'_wpnonce'             => wp_create_nonce( "wordpoints_component_" . key( $message ) . "-{$_POST['wordpoints_component']}" )
		)
		, admin_url( 'admin.php' )
	)
);

exit;
