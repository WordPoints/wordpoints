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
		wordpoints_show_admin_error( sprintf( __( 'The module <code>%s</code> has been <strong>deactivated</strong> due to an error: %s', 'wordpoints' ), esc_html( $module_file ), $error->get_error_message() ) );
	}
}

if ( isset( $_GET['error'] ) ) {

	if ( isset( $_GET['main'] ) ) {

		$error_message = __( 'You cannot delete a module while it is active on the main site.', 'wordpoints' );

	} elseif ( isset( $_GET['charsout'] ) ) {

		$error_message = sprintf( __( 'The module generated %d characters of <strong>unexpected output</strong> during activation. If you notice &#8220;headers already sent&#8221; messages, problems with syndication feeds or other issues, try deactivating or removing this module.', 'wordpoints' ), (int) $_GET['charsout'] );

	} else {

		$error_message = __( 'Module could not be activated because it triggered a <strong>fatal error</strong>.', 'wordpoints' );

		if ( isset( $_GET['module'], $_GET['_error_nonce'] ) && wp_verify_nonce( $_GET['_error_nonce'], "module-activation-error_{$_GET['module']}" ) ) {

			$error_message .= '<iframe style="border:0" width="100%" height="70px" src="admin.php?page=wordpoints_modules&action=error_scrape&amp;module=' . esc_attr( $_GET['module'] ) . '&amp;_wpnonce=' . esc_attr( $_GET['_error_nonce'] ) .'"></iframe>';
		}
	}

} elseif ( isset( $_GET['deleted'] ) ) {

	$user_ID = get_current_user_id();
	$delete_result = get_transient( 'wordpoints_modules_delete_result_' . $user_ID );

	// Delete it once we're done.
	delete_transient( 'wordpoints_modules_delete_result_' . $user_ID );

	if ( is_wp_error( $delete_result ) ) {
		$error_message = sprintf( __( 'Module could not be deleted due to an error: %s', 'wordpoints' ), $delete_result->get_error_message() );
	} else {
		$message = __( 'The selected modules have been <strong>deleted</strong>.', 'wordpoints' );
	}

} elseif ( isset( $_GET['activate'] ) ) {

	$message = __( 'Module <strong>activated</strong>.', 'wordpoints' );

} elseif ( isset( $_GET['activate-multi'] ) ) {

	$message = __( 'Selected modules <strong>activated</strong>.', 'wordpoints' );

} elseif ( isset( $_GET['deactivate'] ) ) {

	$message = __( 'Module <strong>deactivated</strong>.', 'wordpoints' );

} elseif ( isset( $_GET['deactivate-multi'] ) ) {

	$message = __( 'Selected modules <strong>deactivated</strong>.', 'wordpoints' );

} elseif ( isset( $_REQUEST['action'] ) && 'update-selected' == $_REQUEST['action'] ) {

	$error_message = __( 'No out of date modules were selected.', 'wordpoints' );
}

if ( isset( $error_message ) ) {

	wordpoints_show_admin_error( $error_message );

} elseif ( isset( $message ) ) {

	wordpoints_show_admin_message( $message );
}

$title = esc_html( __( 'WordPoints Modules', 'wordpoints' ) );

if ( ( ! is_multisite() || is_network_admin() ) && current_user_can( 'install_wordpoints_modules' ) ) {
	$title .= '<a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_install_modules' ) ) . '" class="add-new-h2">' . esc_html_x( 'Add New', 'module' ) . '</a>';
}

if ( ! empty( $_REQUEST['s'] ) ) {
	$title .= sprintf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;', 'wordpoints' ) . '</span>', esc_html( urlencode( $_REQUEST['s'] ) ) );
}

?>

<div class="wrap">
	<h2><?php echo $title; ?></h2>

	<?php

	require_once WORDPOINTS_DIR . 'admin/includes/class-wordpoints-modules-list-table.php';

	$wp_list_table = new WordPoints_Modules_List_Table();
	$wp_list_table->prepare_items();

	?>

	<?php $wp_list_table->views(); ?>

	<form method="get" action="admin.php">
		<input type="hidden" name="page" value="wordpoints_modules" />
		<?php $wp_list_table->search_box( __( 'Search Installed Modules', 'wordpoints' ), 'module' ); ?>
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