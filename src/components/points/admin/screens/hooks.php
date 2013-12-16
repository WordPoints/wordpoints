<?php

/**
 * Points Hooks administration panel.
 *
 * @package WordPoints\Points\Admin
 * @since 1.0.0
 */

if ( isset( $_POST['add_new'], $_POST['save-points-type'] ) && 1 == (int) $_POST['add_new'] ) {

	// - We are creating a new points type.

	unset( $_GET['error'], $_GET['message'] );

	$settings = array();

	$settings['name']   = trim( $_POST['points-name'] );
	$settings['prefix'] = ltrim( $_POST['points-prefix'] );
	$settings['suffix'] = rtrim( $_POST['points-suffix'] );

	if ( ! wordpoints_add_points_type( wp_unslash( $settings ) ) ) {

		// - Unable to create this, give an error.
		$_GET['error'] = 2;
	}

} elseif ( ! empty( $_POST['delete-points-type'] ) ) {

	// - We are deleting a points type.

	unset( $_GET['error'], $_GET['message'] );

	if ( isset( $_POST['points-slug'] ) && wordpoints_delete_points_type( $_POST['points-slug'] ) ) {

		$_GET['message'] = 1;

	} else {

		$_GET['error'] = 3;
	}
}

// Get all points types.
$points_types = wordpoints_get_points_types();

// These messages/errors are used upon redirection from the non-JS version.
$messages = array(
	__( 'Changes saved.', 'wordpoints' ),
	__( 'Points type deleted.', 'wordpoints' ),
);

$errors = array(
	__( 'Error while saving.', 'wordpoints' ),
	__( 'Error in displaying the hooks settings form.', 'wordpoints' ),
	__( 'Please choose a unique name for this points type.', 'wordpoints' ),
	__( 'Error while deleting.', 'wordpoints' ),
);

?>

<div class="wrap">
	<h2><?php echo esc_html( _x( 'Points Hooks', 'page title', 'wordpoints' ) ); ?></h2>

	<?php

	if ( isset( $_GET['message'] ) && isset( $messages[ $_GET['message'] ] ) ) {

		wordpoints_show_admin_message( $messages[ $_GET['message'] ] );

	} elseif ( isset( $_GET['error'] ) && isset( $errors[ $_GET['error'] ] ) ) {

		wordpoints_show_admin_error( $errors[ $_GET['error'] ] );
	}

	/**
	 * Top of points hooks admin screen.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wordpoints_admin_points_hooks_head' );

	?>

	<div class="hook-liquid-left hook-liquid-left">
		<div id="hooks-left">
			<div id="available-hooks" class="hooks-holder-wrap hooks-holder-wrap">
				<div class="points-type-name">
					<div class="points-type-name-arrow"><br /></div>
					<h3><?php esc_html_e( 'Available Hooks', 'wordpoints' ); ?> <span id="removing-hook"><?php _ex( 'Deactivate', 'removing-hook', 'wordpoints' ); ?> <span></span></span></h3>
				</div>
				<div class="hook-holder hook-holder">
					<p class="description"><?php _e( 'Drag hooks from here to a points type on the right to activate them. Drag hooks back here to deactivate them and delete their settings.', 'wordpoints' ); ?></p>
					<div id="hook-list">
						<?php WordPoints_Points_Hooks::list_hooks(); ?>
					</div>
					<br class="clear" />
				</div>
				<br class="clear" />
			</div>

			<div class="hooks-holder-wrap inactive-points-type">
				<div class="points-type-name">
					<div class="points-type-name-arrow"><br /></div>
					<h3><?php esc_html_e( 'Inactive Hooks', 'wordpoints' ); ?>
						<span class="spinner"></span>
					</h3>
				</div>
				<div class="hook-holder inactive hook-holder">
					<?php WordPoints_Points_Hooks::list_inactive(); ?>
					<div class="clear"></div>
				</div>
			</div>

		</div>
	</div>

	<div class="hook-liquid-right">
		<div id="hooks-right">

			<?php

				$i = 0;

				foreach ( $points_types as $slug => $points_type ) {

					$wrap_class = 'hooks-holder-wrap';
					if ( ! empty( $points_type['class'] ) )
						$wrap_class .= ' points-type-' . $points_type['class'];

					if ( $i )
						$wrap_class .= ' closed';

					?>

					<div class="<?php echo esc_attr( $wrap_class ); ?>">
						<div class="points-type-name">
							<div class="points-type-name-arrow"><br /></div>
							<h3><?php echo esc_html( $points_type['name'] ); ?><span class="spinner"></span></h3>
						</div>
						<?php WordPoints_Points_Hooks::list_by_points_type( $slug ); // Show the control forms for each of the hooks in this points type. ?>
					</div>

					<?php

					$i++;
				}

				$closed = '';

				if ( $i > 0 )
					$closed .= 'closed';
			?>

			<div class="hooks-holder-wrap new-points-type <?php echo $closed; ?>">
				<div class="points-type-name">
					<div class="points-type-name-arrow"><br /></div>
					<h3><?php esc_html_e( 'Add New Points Type', 'wordpoints' ); ?><span class="spinner"></span></h3>
				</div>
				<div class="wordpoints-points-add-new hooks-sortables hook">
					<?php WordPoints_Points_Hooks::points_type_form(); ?>
				</div>
			</div>

		</div>
	</div>
	<form action="" method="post">
		<?php wp_nonce_field( 'save-wordpoints-points-hooks', '_wpnonce_hooks', false ); ?>
	</form>

	<?php

	/**
	 * Bottom of points hooks admin screen.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wordpoints_admin_points_hooks_foot' );

	?>

	<br class="clear" />

	<div class="hooks-chooser">
		<h3><?php _e( 'Choose a points type:', 'wordpoints' ); ?></h3>
		<ul class="hooks-chooser-points-types"></ul>
		<div class="hooks-chooser-actions">
			<button class="button-secondary"><?php _e( 'Cancel', 'wordpoints' ); ?></button>
			<button class="button-primary"><?php _e( 'Add Hook', 'wordpoints' ); ?></button>
		</div>
	</div>
</div>
