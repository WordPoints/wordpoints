<?php

/**
 * WordPoints points hooks administration panel without cool JavaScript.
 *
 * @package WordPoints\Points\Administration
 * @since 1.0.0
 */

$hook_id = $_GET['edithook'];

$points_types = wordpoints_get_points_types();

if ( isset( $_GET['addnew'] ) ) {

	// - We are adding a new points hook.

	$points_type = wordpoints_get_default_points_type();

	// Default to the first points type.
	if ( ! $points_type ) {
		reset( $points_types );
		$points_type = key( $points_types );
	}

	if ( ! $points_type ) {

		wordpoints_show_admin_error( __( 'You need to add a points type before you can add any hooks.', 'wordpoints' ) );
		return;
	}

	if ( isset( $_GET['base'] ) && isset( $_GET['num'] ) ) {

		// Copy minimal info from an existing instance of this hook to a new instance.
		$hook = WordPoints_Points_Hooks::get_handler_by_id_base( $_GET['base'] );

		$multi_number = (int) $_GET['num'];
		$number       = 0;
		$hook_id      = $hook->get_id( $multi_number );
		$_hook        = $hook;
		$id_base      = $hook->get_id_base();
	}

} else {

	// We are editing an existing points hook.

	$points_type = isset( $_GET['points_type'] ) ? $_GET['points_type'] : '_inactive_hooks';

	$hook = WordPoints_Points_Hooks::get_handler( $hook_id );

	if ( ! $hook ) {

		wordpoints_show_admin_error( __( 'The hook you have asked to edit could not be found. Please go back and try again.', 'wordpoints' ) );
		return;
	}

	$id_base = $hook->get_id_base();
	$multi_number = 0;
	$number = $hook->get_number_by_id( $hook_id );
}

$name = esc_html( $hook->get_name() );

// Show the hook form.
$width = ' style="width:' . $hook->get_option( 'width' ) . 'px"';

?>

<div class="wrap">
	<h2><?php echo esc_html( __( 'Points Hooks', 'wordpoints' ) ); ?></h2>
	<div class="edithook"<?php echo $width; ?>>
		<h3><?php printf( _x( 'Hook %s', 'hook name', 'wordpoints' ), $name ); ?></h3>

		<form action="admin.php?page=wordpoints_points_hooks" method="post">
			<div class="hook-inside">
				<?php $hook->form_callback( $number ); ?>
			</div>

			<p class="describe"><?php _e( 'Select the points type to attach this hook to.', 'wordpoints' ); ?></p>
			<div class="hook-position">
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
				<?php if ( isset( $_GET['addnew'] ) ) : ?>
					<a href="admin.php?page=wordpoints_points_hooks" class="button alignleft"><?php _e( 'Cancel', 'wordpoints' ); ?></a>
				<?php else :
						submit_button( _x( 'Delete', 'points hook', 'wordpoints' ), 'button alignleft', 'removehook', false );
					endif;

					submit_button( __( 'Save Hook', 'wordpoints' ), 'button-primary alignright', 'savehook', false );
				?>

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

// end of file /components/points/admin/screens/points-hooks-no-js.php
