<?php

/**
 * Points Hooks administration panel.
 *
 * @package WordPoints\Points\Admin
 * @since 1.0.0
 */

if ( current_user_can( 'manage_wordpoints_points_types' ) ) {

	if (
		isset( $_POST['save-points-type'], $_POST['points-name'], $_POST['points-prefix'], $_POST['points-suffix'] )
		&& wordpoints_verify_nonce( 'add_new', 'wordpoints_add_new_points_type', null, 'post' )
	) {

		// - We are creating a new points type.

		unset( $_GET['error'], $_GET['message'] );

		$settings = array();

		$settings['name']   = trim( sanitize_text_field( wp_unslash( $_POST['points-name'] ) ) );
		$settings['prefix'] = ltrim( sanitize_text_field( wp_unslash( $_POST['points-prefix'] ) ) );
		$settings['suffix'] = rtrim( sanitize_text_field( wp_unslash( $_POST['points-suffix'] ) ) );

		if ( ! wordpoints_add_points_type( $settings ) ) {

			// - Unable to create this, give an error.
			$_GET['error'] = 2;
		}

	} elseif (
		! empty( $_POST['delete-points-type'] )
		&& isset( $_POST['points-slug'] )
		&& wordpoints_verify_nonce( 'delete-points-type-nonce', 'wordpoints_delete_points_type-%s', array( 'points-slug' ), 'post' )
	) {

		// - We are deleting a points type.

		unset( $_GET['error'], $_GET['message'] );

		if ( wordpoints_delete_points_type( sanitize_key( $_POST['points-slug'] ) ) ) {

			$_GET['message'] = 1;

		} else {

			$_GET['error'] = 3;
		}
	}

} // if ( current_user_can( 'manage_wordpoints_points_types' ) )

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

if ( is_network_admin() ) {
	$title = _x( 'Network Points Hooks', 'page title', 'wordpoints' );
} else {
	$title = _x( 'Points Hooks', 'page title', 'wordpoints' );
}

?>

<div class="wrap">
	<h2><?php echo esc_html( $title ); ?></h2>

	<?php

	if ( empty( $points_types ) && ! current_user_can( 'manage_wordpoints_points_types' ) ) {

		wordpoints_show_admin_error( esc_html__( 'No points types have been created yet. Only network administrators can create points types.', 'wordpoints' ) );

		echo '</div>';
		return;
	}

	if ( isset( $_GET['message'] ) && isset( $messages[ (int) $_GET['message'] ] ) ) {

		wordpoints_show_admin_message( esc_html( $messages[ (int) $_GET['message'] ] ) );

	} elseif ( isset( $_GET['error'] ) && isset( $errors[ (int) $_GET['error'] ] ) ) {

		wordpoints_show_admin_error( esc_html( $errors[ (int) $_GET['error'] ] ) );
	}

	if ( is_network_admin() && current_user_can( 'manage_network_wordpoints_points_hooks' ) ) {

		// Display network wide hooks.
		WordPoints_Points_Hooks::set_network_mode( true );
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
					<h3><?php esc_html_e( 'Available Hooks', 'wordpoints' ); ?> <span id="removing-hook"><?php echo esc_html_x( 'Deactivate', 'removing-hook', 'wordpoints' ); ?> <span></span></span></h3>
				</div>
				<div class="hook-holder hook-holder">
					<p class="description"><?php esc_html_e( 'Drag hooks from here to a points type on the right to activate them. Drag hooks back here to deactivate them and delete their settings.', 'wordpoints' ); ?></p>
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
					<div id="_inactive_hooks" class="hooks-sortables">
						<div class="points-type-description">
							<p class="description">
								<?php esc_html_e( 'Drag hooks here to remove them from the points type but keep their settings.', 'wordpoints' ); ?>
							</p>
						</div>
						<?php WordPoints_Points_Hooks::list_by_points_type( '_inactive_hooks' ); ?>
					</div>
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
				if ( ! empty( $points_type['class'] ) ) {
					$wrap_class .= ' points-type-' . $points_type['class'];
				}

				if ( $i ) {
					$wrap_class .= ' closed';
				}

				?>

				<div class="<?php echo esc_attr( $wrap_class ); ?>">
					<div class="points-type-name">
						<div class="points-type-name-arrow"><br /></div>
						<h3><?php echo esc_html( $points_type['name'] ); ?><span class="spinner"></span></h3>
					</div>
					<div id="<?php echo esc_attr( $slug ); ?>" class="hooks-sortables">

						<?php

						if ( current_user_can( 'manage_wordpoints_points_types' ) ) {
							WordPoints_Points_Hooks::points_type_form( $slug );
						}

						WordPoints_Points_Hooks::list_by_points_type( $slug );

						?>

					</div>
				</div>

				<?php

				$i++;
			}

			if ( current_user_can( 'manage_wordpoints_points_types' ) ) {

				?>

				<div class="hooks-holder-wrap new-points-type <?php echo ( $i > 0 ) ? 'closed' : ''; ?>">
					<div class="points-type-name">
						<div class="points-type-name-arrow"><br /></div>
						<h3><?php esc_html_e( 'Add New Points Type', 'wordpoints' ); ?><span class="spinner"></span></h3>
					</div>
					<div class="wordpoints-points-add-new hooks-sortables hook">
						<?php WordPoints_Points_Hooks::points_type_form(); ?>
					</div>
				</div>

				<?php

			}

			?>

		</div>
	</div>
	<form method="post">
		<?php

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$field = 'save-network-wordpoints-points-hooks';
		} else {
			$field = 'save-wordpoints-points-hooks';
		}

		wp_nonce_field( $field, '_wpnonce_hooks', false );

		?>
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
		<h3><?php esc_html_e( 'Choose a points type:', 'wordpoints' ); ?></h3>
		<ul class="hooks-chooser-points-types"></ul>
		<div class="hooks-chooser-actions">
			<button class="button-secondary"><?php esc_html_e( 'Cancel', 'wordpoints' ); ?></button>
			<button class="button-primary"><?php esc_html_e( 'Add Hook', 'wordpoints' ); ?></button>
		</div>
	</div>
</div>
