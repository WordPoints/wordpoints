<?php

/**
 * AJAX callbacks for the points component administration screens.
 *
 * @package WordPoints\Points\Administration
 * @since 1.2.0
 */

/**
 * Save points hooks order via AJAX.
 *
 * @since 1.0.0
 *
 * @action wp_ajax_wordpoints-points-hooks-order
 */
function wordpoints_ajax_points_hooks_order() {

	if ( check_ajax_referer( 'save-network-wordpoints-points-hooks', 'savehooks', false ) ) {

		if ( ! current_user_can( 'manage_network_wordpoints_points_hooks' ) ) {
			wp_die( -1 );
		}

		// Saving network hooks order, turn on network mode.
		WordPoints_Points_Hooks::set_network_mode( true );

	} elseif ( check_ajax_referer( 'save-wordpoints-points-hooks', 'savehooks', false ) ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

	} else {

		// CSRF attack (or, more probably, the user left the browser open too long).
		wp_die( -1 );
	}

	// Save hooks order for all points types.
	if ( is_array( $_POST['points_types'] ) ) {

		$points_types_hooks = array();

		foreach ( $_POST['points_types'] as $points_type => $hooks ) {

			$points_type_hooks = array();

			if ( ! empty( $hooks ) ) {

				$hooks = explode( ',', $hooks );

				foreach ( $hooks as $order => $hook_id ) {

					if ( false === strpos( $hook_id, 'hook-' ) ) {
						continue;
					}

					$points_type_hooks[ $order ] = substr( $hook_id, strpos( $hook_id, '_' ) + 1 );
				}
			}

			$points_types_hooks[ $points_type ] = $points_type_hooks;
		}

		WordPoints_Points_Hooks::save_points_types_hooks( wp_unslash( $points_types_hooks ) );

		wp_die( 1 );
	}

	wp_die( -1 );
}
add_action( 'wp_ajax_wordpoints-points-hooks-order', 'wordpoints_ajax_points_hooks_order' );

/**
 * Save points hook settings via AJAX.
 *
 * @since 1.0.0
 *
 * @action wp_ajax_save-wordpoints-points-hook
 */
function wordpoints_ajax_save_points_hook() {

	if ( check_ajax_referer( 'save-network-wordpoints-points-hooks', 'savehooks', false ) ) {

		if ( ! current_user_can( 'manage_network_wordpoints_points_hooks' ) ) {
			wp_die( -1 );
		}

		// Saving network hooks, turn on network mode.
		WordPoints_Points_Hooks::set_network_mode( true );

	} elseif ( check_ajax_referer( 'save-wordpoints-points-hooks', 'savehooks', false ) ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

	} else {

		// CSRF attack (or, more probably the user left the browser open too long).
		wp_die( -1 );
	}

	$error = '<p>' . esc_html__( 'An error has occurred. Please reload the page and try again.', 'wordpoints' ) . '</p>';

	if ( isset( $_POST['points-slug'] ) ) {

		// - We are saving the settings for a points type.

		if ( ! current_user_can( 'manage_wordpoints_points_types' ) ) {
			wp_die( -1 );
		}

		$settings = array();

		if ( isset( $_POST['points-name'] ) ) {
			$settings['name'] = wp_unslash( trim( esc_html( $_POST['points-name'] ) ) );
		}

		if ( isset( $_POST['points-prefix'] ) ) {
			$settings['prefix'] = wp_unslash( ltrim( esc_html( $_POST['points-prefix'] ) ) );
		}

		if ( isset( $_POST['points-suffix'] ) ) {
			$settings['suffix'] = wp_unslash( rtrim( esc_html( $_POST['points-suffix'] ) ) );
		}

		$points_type = sanitize_key( $_POST['points-slug'] );

		$old_settings = wordpoints_get_points_type( $points_type );

		if ( false === $old_settings ) {
			wp_die( -1 );
		}

		if ( is_array( $old_settings ) ) {
			$settings = array_merge( $old_settings, $settings );
		}

		if ( ! wordpoints_update_points_type( $points_type, $settings ) ) {

			// If this fails, show the user a message along with the form.
			echo '<p>' . esc_html__( 'An error has occurred. Please try again.', 'wordpoints' ) . '</p>';

			WordPoints_Points_Hooks::points_type_form( $points_type, 'none' );
		}

	} else {

		// - We are creating/updating/deleting an instance of a hook.

		if ( ! isset( $_POST['id_base'], $_POST['hook-id'], $_POST['points_type'], $_POST['hook_number'] ) ) {
			wp_die( -1 );
		}

		$id_base        = sanitize_key( $_POST['id_base'] );
		$hook_id        = sanitize_key( $_POST['hook-id'] );
		$points_type_id = sanitize_key( $_POST['points_type'] );
		$number         = (int) $_POST['hook_number'];

		/*
		 * Normally the hook ID will be in 'hook-id' when we are updating a hook.
		 * But when we are saving a brand new instance of a hook or updating a newly
		 * created hook, the ID won't have been set when the form was output, so
		 * 'hook-id' will be empty, and we'll get the ID from 'multi_number'.
		 */
		if ( ! $number ) {

			// This holds the ID number if the hook is brand new.
			if ( ! isset( $_POST['multi_number'] ) || ! wordpoints_posint( $_POST['multi_number'] ) ) {
				wp_die( $error );
			}

			$number  = $_POST['multi_number'];
			$hook_id = $id_base . '-' . $number;
		}

		$hook = WordPoints_Points_Hooks::get_handler( $hook_id );

		$settings = false;

		if ( isset( $_POST[ 'hook-' . $id_base ] ) && is_array( $_POST[ 'hook-' . $id_base ] ) ) {
			$settings = wp_unslash( $_POST[ 'hook-' . $id_base ] );
		}

		$points_types_hooks = WordPoints_Points_Hooks::get_points_types_hooks();

		// Get the hooks for this points type.
		$points_type_hooks = ( isset( $points_types_hooks[ $points_type_id ] ) ) ? $points_types_hooks[ $points_type_id ] : array();

		if ( ! empty( $_POST['delete_hook'] ) ) {

			// - We are deleting a hook instance.

			if ( false === $hook ) {
				wp_die( $error );
			}

			$hook->delete_callback( $number );

			// Remove this instance of the hook, and reset the positions (keys).
			$points_types_hooks[ $points_type_id ] = array_diff( $points_type_hooks, array( $hook_id ) );

			WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );

			echo esc_html( "deleted:{$hook_id}" );

			wp_die();

		} elseif ( $settings && false === $hook ) {

			// - We are creating a new a new instance of a hook.

			/*
			 * Get a hook object for this type of hook. We have to do this because
			 * since the hook is new, it hasn't been assigned an ID yet, so we can't
			 * just get it from the array of hooks by ID.
			 */
			$hook = WordPoints_Points_Hooks::get_handler_by_id_base( $id_base );

			$new_instance = reset( $settings );

			// Save the points types-hooks associations.
			$points_type_hooks[] = $hook->get_id( $number );
			$points_types_hooks[ $points_type_id ] = $points_type_hooks;
			WordPoints_Points_Hooks::save_points_types_hooks( $points_types_hooks );

		} else {

			// - We are updating the settings for an instance of a hook.

			if ( false === $hook ) {
				wp_die( $error );
			}

			$new_instance = ( ! empty( $settings ) ) ? reset( $settings ) : array();
		}

		$hook->update_callback( wp_unslash( $new_instance ), $number );

		if ( empty( $_POST['add_new'] ) ) {
			$hook->form_callback( $number );
		}

	} // if ( isset( $_POST['points-name'] ) ) {} else

	wp_die();
}
add_action( 'wp_ajax_save-wordpoints-points-hook', 'wordpoints_ajax_save_points_hook' );

// EOF
