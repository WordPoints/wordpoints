<?php

/**
 * WordPoints modules administration panel.
 *
 * @package WordPoints\Administration
 * @since 1.1.0
 */

global $status, $page;

$invalid = wordpoints_validate_active_modules();

if ( ! empty( $invalid ) ) {
	foreach ( $invalid as $module_file => $error ) {
		wordpoints_show_admin_error( sprintf( __( 'The module %s has been <strong>deactivated</strong> due to an error: %s', 'wordpoints' ), esc_html( $module_file ), '<code>' . esc_html( $error->get_error_message() ) . '</code>' ) );
	}
}

if ( isset( $_GET['error'] ) ) {

	if ( isset( $_GET['main'] ) ) {

		wordpoints_show_admin_error( esc_html__( 'You cannot delete a module while it is active on the main site.', 'wordpoints' ) );

	} elseif ( isset( $_GET['charsout'] ) ) {

		wordpoints_show_admin_error( sprintf( __( 'The module generated %d characters of <strong>unexpected output</strong> during activation. If you notice &#8220;headers already sent&#8221; messages, problems with syndication feeds or other issues, try deactivating or removing this module.', 'wordpoints' ), (int) $_GET['charsout'] ) );

	} else {

		$error_message = __( 'Module could not be activated because it triggered a <strong>fatal error</strong>.', 'wordpoints' );

		if (
			isset( $_GET['_error_nonce'], $_GET['module'] )
			&& wordpoints_verify_nonce( '_error_nonce', 'module-activation-error_%s', array( 'module' ) )
		) {

			?>

			<div id="message" class="error">
				<p>
					<?php echo wp_kses( $error_message, '' ); ?>
					<iframe style="border:0" width="100%" height="70px" src="admin.php?page=wordpoints_modules&action=error_scrape&amp;module=<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['module'] ) ) ); ?>&amp;_wpnonce=<?php echo esc_attr( sanitize_key( $_GET['_error_nonce'] ) ); ?>"></iframe>
				</p>
			</div>

			<?php

		} else {

			wordpoints_show_admin_error( $error_message );
		}
	}

} elseif ( isset( $_GET['deleted'] ) ) {

	$user_ID = get_current_user_id();
	$delete_result = get_transient( 'wordpoints_modules_delete_result_' . $user_ID );

	// Delete it once we're done.
	delete_transient( 'wordpoints_modules_delete_result_' . $user_ID );

	if ( is_wp_error( $delete_result ) ) {
		wordpoints_show_admin_error( sprintf( __( 'Module could not be deleted due to an error: %s', 'wordpoints' ), $delete_result->get_error_message() ) );
	} else {
		wordpoints_show_admin_message( __( 'The selected modules have been <strong>deleted</strong>.', 'wordpoints' ) );
	}

} elseif ( isset( $_GET['activate'] ) ) {

	wordpoints_show_admin_message( __( 'Module <strong>activated</strong>.', 'wordpoints' ) );

} elseif ( isset( $_GET['activate-multi'] ) ) {

	wordpoints_show_admin_message( __( 'Selected modules <strong>activated</strong>.', 'wordpoints' ) );

} elseif ( isset( $_GET['deactivate'] ) ) {

	wordpoints_show_admin_message( __( 'Module <strong>deactivated</strong>.', 'wordpoints' ) );

} elseif ( isset( $_GET['deactivate-multi'] ) ) {

	wordpoints_show_admin_message( __( 'Selected modules <strong>deactivated</strong>.', 'wordpoints' ) );

} elseif ( isset( $_REQUEST['action'] ) && 'update-selected' === sanitize_key( $_REQUEST['action'] ) ) {

	wordpoints_show_admin_message( esc_html__( 'No out of date modules were selected.', 'wordpoints' ) );
}

?>

<div class="wrap">
	<h2>
		<?php esc_html_e( 'WordPoints Modules', 'wordpoints' ); ?>

		<?php if ( ( ! is_multisite() || is_network_admin() ) && current_user_can( 'install_wordpoints_modules' ) ) : ?>
			<a href="<?php echo esc_attr( esc_url( self_admin_url( 'admin.php?page=wordpoints_install_modules' ) ) ); ?>" class="add-new-h2"><?php echo esc_html_x( 'Add New', 'module', 'wordpoints' ); ?></a>
		<?php endif; ?>

		<?php if ( ! empty( $_REQUEST['s'] ) ) : ?>
			<span class="subtitle"><?php echo esc_html( sprintf( __( 'Search results for &#8220;%s&#8221;', 'wordpoints' ), sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) ); ?></span>
		<?php endif; ?>
	</h2>

	<?php

	require_once WORDPOINTS_DIR . 'admin/includes/class-wordpoints-modules-list-table.php';

	$wp_list_table = new WordPoints_Modules_List_Table();
	$wp_list_table->prepare_items();

	?>

	<?php $wp_list_table->views(); ?>

	<form method="get" action="admin.php">
		<input type="hidden" name="page" value="wordpoints_modules" />
		<?php $wp_list_table->search_box( esc_html__( 'Search Installed Modules', 'wordpoints' ), 'module' ); ?>
	</form>

	<form method="post" action="admin.php?page=wordpoints_modules">
		<input type="hidden" name="module_status" value="<?php echo esc_attr( $status ) ?>" />
		<input type="hidden" name="paged" value="<?php echo esc_attr( $page ) ?>" />
		<?php $wp_list_table->display(); ?>
	</form>

	<?php
	/**
	 * Bottom of modules administration panel.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wordpoints_admin_modules' );
	?>

</div>
