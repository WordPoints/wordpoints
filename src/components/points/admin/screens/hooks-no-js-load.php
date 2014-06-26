<?php

/**
 * Save points hooks for accessibility mode hooks screen.
 *
 * @package WordPoints\Points\Hooks
 * @since 1.2.0
 */

if ( ! isset( $_POST['hook-id'] ) ) {
	return;
}

$hook_id = sanitize_key( $_POST['hook-id'] );

check_admin_referer( "save-delete-hook-{$hook_id}" );

$redirect_path = 'admin.php?page=wordpoints_points_hooks';

if ( is_network_admin() ) {

	WordPoints_Points_Hooks::set_network_mode( true );
	$redirect_url = network_admin_url( $redirect_path );

} else {

	$redirect_url = admin_url( $redirect_path );
}

if ( ! isset( $_POST['points_type'], $_POST['id_base'] ) ) {
	return;
}

$points_type_id = sanitize_key( $_POST['points_type'] );
$id_base        = sanitize_key( $_POST['id_base'] );

// These are the hooks grouped by points type.
$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();

if ( empty( $points_types_hooks ) ) {
	$points_types_hooks = WordPoints_Points_Hooks::get_defaults();
}

if ( isset( $points_types_hooks[ $points_type_id ] ) ) {
	$points_type_hooks = $points_types_hooks[ $points_type_id ];
} else {
	$points_type_hooks = array();
}

$hook = WordPoints_Points_Hooks::get_handler_by_id_base( $id_base );

if ( isset( $_POST['removehook'] ) && $_POST['removehook'] ) {

	// - We are deleting an instance of a hook.

	if ( ! in_array( $hook_id, $points_type_hooks, true ) ) {

		// The hook isn't hooked to this points type, give an error.
		wp_redirect( $redirect_url . '&error=0' );
		exit;
	}

	// Remove the hook from this points type.
	$points_types_hooks[ $points_type_id ] = array_diff( $points_type_hooks, array( $hook_id ) );

	$hook->delete_callback( $hook_id );

} elseif ( isset( $_POST['savehook'] ) && $_POST['savehook'] ) {

	// - We are saving an instance of a hook.

	$number = isset( $_POST['multi_number'] ) ? (int) $_POST['multi_number'] : '';

	if ( $number ) {

		// Search the POST for the instance settings.
		foreach ( $_POST as $key => $val ) {

			if ( is_array( $val ) && preg_match( '/__i__|%i%/', key( $val ) ) ) {

				$new_instance = array_shift( $val );
				break;
			}
		}

	} else {

		if ( isset( $_POST[ 'hook-' . $id_base ] ) && is_array( $_POST[ 'hook-' . $id_base ] ) ) {
			$new_instance = wp_unslash( reset( $_POST[ 'hook-' . $id_base ] ) );
		}

		$number = $hook->get_number_by_id( $hook_id );
	}

	if ( ! isset( $new_instance ) || ! is_array( $new_instance ) ) {

		$new_instance = array();
	}

	// Update the hook.
	$hook->update_callback( $new_instance, $number );

	// Add hook it to this points type.
	if ( ! in_array( $hook_id, $points_type_hooks ) ) {

		$points_type_hooks[] = $hook_id;
		$points_types_hooks[ $points_type_id ] = $points_type_hooks;
	}

	// Remove from old points type if it has changed.
	$old_points_type = WordPoints_Points_Hooks::get_points_type( $hook_id );

	if ( $old_points_type && $old_points_type != $points_type_id && is_array( $points_types_hooks[ $old_points_type ] ) ) {

		$points_types_hooks[ $old_points_type ] = array_diff( $points_types_hooks[ $old_points_type ], array( $hook_id ) );
	}

} else {

	wp_redirect( $redirect_url . '&error=0' );
	exit;
}

WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );

wp_redirect( $redirect_url . '&message=0' );
exit;
