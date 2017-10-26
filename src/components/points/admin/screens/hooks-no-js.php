<?php

/**
 * WordPoints points hooks administration panel without cool JavaScript.
 *
 * @package WordPoints\Points\Administration
 * @since 1.0.0
 */

if ( ! isset( $_GET['edithook'] ) ) { // WPCS: CSRF OK.
	return;
}

$hook_id = sanitize_key( $_GET['edithook'] );

$points_types = wordpoints_get_points_types();

if ( isset( $_GET['addnew'] ) ) { // WPCS: CSRF OK.

	// - We are adding a new points hook.

	$points_type = wordpoints_get_default_points_type();

	// Default to the first points type.
	if ( ! $points_type ) {
		reset( $points_types );
		$points_type = key( $points_types );
	}

	if ( ! $points_type ) {

		wordpoints_show_admin_error( esc_html__( 'You need to add a points type before you can add any hooks.', 'wordpoints' ) );
		return;
	}

	if ( isset( $_GET['base'], $_GET['num'] ) ) { // WPCS: CSRF OK.

		// Copy minimal info from an existing instance of this hook to a new instance.
		$hook = WordPoints_Points_Hooks::get_handler_by_id_base(
			sanitize_key( $_GET['base'] ) // WPCS: CSRF OK.
		);

		if ( ! $hook ) {

			wordpoints_show_admin_error(
				esc_html__( 'Unable to add the points hook, please try again.', 'wordpoints' )
				, array( 'dismissible' => true )
			);

			return;
		}

		$multi_number = (int) $_GET['num'];
		$number       = 0;
		$hook_id      = $hook->get_id( $multi_number );
		$_hook        = $hook;
		$id_base      = $hook->get_id_base();

	} else {

		wordpoints_show_admin_error(
			esc_html__( 'Unable to add the points hook, please try again.', 'wordpoints' )
			, array( 'dismissible' => true )
		);

		return;
	}

} else {

	// We are editing an existing points hook.

	$points_type = isset( $_GET['points_type'] ) ? sanitize_key( $_GET['points_type'] ) : '_inactive_hooks'; // WPCS: CSRF OK.

	$hook = WordPoints_Points_Hooks::get_handler( $hook_id );

	if ( ! $hook ) {

		wordpoints_show_admin_error( esc_html__( 'The hook you have asked to edit could not be found. Please go back and try again.', 'wordpoints' ) );
		return;
	}

	$id_base      = $hook->get_id_base();
	$multi_number = 0;
	$number       = $hook->get_number_by_id( $hook_id );

} // End if ( adding hook ) else { updating hook }.

$name = esc_html( $hook->get_name() );

// Show the hook form.

?>

<div class="wrap">
	<h1><?php esc_html_e( 'Points Hooks', 'wordpoints' ); ?></h1>
	<div class="edithook" style="width:<?php echo absint( $hook->get_option( 'width' ) ); ?>px">
		<h2>
			<?php

			// translators: Hook name.
			echo esc_html( sprintf( __( 'Hook %s', 'wordpoints' ), $name ) );

			?>
		</h2>

		<form action="<?php echo esc_url( self_admin_url( 'admin.php?page=wordpoints_points_hooks' ) ); ?>" method="post">
			<div class="hook-inside">
				<?php $hook->form_callback( $number ); ?>
			</div>

			<p class="describe"><?php esc_html_e( 'Select the points type to attach this hook to.', 'wordpoints' ); ?></p>
			<div class="hook-position">
				<label for="points_type" class="screen-reader-text">
					<?php esc_html_e( 'Points Type:', 'wordpoints' ); ?>
				</label>
				<?php

					wordpoints_points_types_dropdown(
						array(
							'selected' => $points_type,
							'name'     => 'points_type',
							'id'       => 'points_type',
							'class'    => 'widefat',
							'options'  => array( '_inactive_hooks' => __( 'Inactive Hooks', 'wordpoints' ) ),
						)
					);

				?>
			</div>
			<br />
			<div class="hook-control-actions">
				<?php if ( isset( $_GET['addnew'] ) ) : /* WPCS: CSRF OK. */ ?>
					<a href="<?php echo esc_url( self_admin_url( 'admin.php?page=wordpoints_points_hooks' ) ); ?>" class="button alignleft"><?php esc_html_e( 'Cancel', 'wordpoints' ); ?></a>
				<?php else : ?>
					<?php submit_button( _x( 'Delete', 'points hook', 'wordpoints' ), 'button alignleft', 'removehook', false ); ?>
				<?php endif; ?>

				<?php submit_button( __( 'Save Hook', 'wordpoints' ), 'button-primary alignright', 'savehook', false ); ?>

				<input type="hidden" name="hook-id" class="hook-id" value="<?php echo esc_attr( $hook_id ); ?>" />
				<input type="hidden" name="id_base" class="id_base" value="<?php echo esc_attr( $id_base ); ?>" />
				<input type="hidden" name="multi_number" class="multi_number" value="<?php echo esc_attr( $multi_number ); ?>" />
				<?php wp_nonce_field( "save-delete-hook-{$hook_id}" ); ?>
				<br class="clear" />
			</div>
		</form>
	</div>
</div>

<?php

// EOF
