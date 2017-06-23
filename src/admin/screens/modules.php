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
		wordpoints_show_admin_error(
			sprintf(
				// translators: 1. Extension name; 2. Error message.
				__( 'The extension %1$s has been <strong>deactivated</strong> due to an error: %2$s', 'wordpoints' )
				, esc_html( $module_file )
				, '<code>' . esc_html( $error->get_error_message() ) . '</code>'
			)
			, array( 'dismissible' => true )
		);
	}
}

if ( isset( $_GET['error'] ) ) {

	if ( isset( $_GET['main'] ) ) {

		wordpoints_show_admin_error(
			esc_html__( 'You cannot delete an extension while it is active on the main site.', 'wordpoints' )
			, array( 'dismissible' => true )
		);

	} elseif ( isset( $_GET['charsout'] ) ) {

		wordpoints_show_admin_message(
			sprintf(
				// translators: Number of characters.
				__( 'The extension generated %d characters of <strong>unexpected output</strong> during activation. If you notice &#8220;headers already sent&#8221; messages, problems with syndication feeds or other issues, try deactivating or removing this extension.', 'wordpoints' )
				, (int) $_GET['charsout'] // WPCS: CSRF OK.
			)
			, 'warning'
			, array( 'dismissible' => true )
		);

	} else {

		$error_message = __( 'Extension could not be activated because it triggered a <strong>fatal error</strong>.', 'wordpoints' );

		if (
			isset( $_GET['_error_nonce'], $_GET['module'] )
			&& wordpoints_verify_nonce( '_error_nonce', 'module-activation-error_%s', array( 'module' ) )
		) {

			$url = self_admin_url(
				'admin.php?page=wordpoints_extensions&action=error_scrape&amp;module='
					. sanitize_text_field( wp_unslash( $_GET['module'] ) )
					. '&amp;_wpnonce=' . sanitize_key( $_GET['_error_nonce'] )
			);

			?>

			<div class="notice notice-error is-dismissible">
				<p>
					<?php echo wp_kses( $error_message, '' ); ?>
					<iframe style="border:0" width="100%" height="70px" src="<?php echo esc_url( $url ); ?>"></iframe>
				</p>
			</div>

			<?php

		} else {

			wordpoints_show_admin_error(
				$error_message
				, array( 'dismissible' => true )
			);
		}

	} // End if ( main error ) elseif ( unexpected output error ) else.

} elseif ( isset( $_GET['deleted'] ) ) {

	$user_id       = get_current_user_id();
	$delete_result = get_transient( 'wordpoints_modules_delete_result_' . $user_id );

	// Delete it once we're done.
	delete_transient( 'wordpoints_modules_delete_result_' . $user_id );

	if ( is_wp_error( $delete_result ) ) {

		wordpoints_show_admin_error(
			sprintf(
				// translators: Error message.
				__( 'Extension could not be deleted due to an error: %s', 'wordpoints' )
				, $delete_result->get_error_message()
			)
			, array( 'dismissible' => true )
		);

	} else {

		wordpoints_show_admin_message(
			__( 'The selected extensions have been <strong>deleted</strong>.', 'wordpoints' )
			, 'success'
			, array( 'dismissible' => true )
		);
	}

} elseif ( isset( $_GET['activate'] ) ) {

	wordpoints_show_admin_message(
		__( 'Extension <strong>activated</strong>.', 'wordpoints' )
		, 'success'
		, array( 'dismissible' => true )
	);

} elseif ( isset( $_GET['activate-multi'] ) ) {

	wordpoints_show_admin_message(
		__( 'Selected extensions <strong>activated</strong>.', 'wordpoints' )
		, 'success'
		, array( 'dismissible' => true )
	);

} elseif ( isset( $_GET['deactivate'] ) ) {

	wordpoints_show_admin_message(
		__( 'Extension <strong>deactivated</strong>.', 'wordpoints' )
		, 'success'
		, array( 'dismissible' => true )
	);

} elseif ( isset( $_GET['deactivate-multi'] ) ) {

	wordpoints_show_admin_message(
		__( 'Selected extensions <strong>deactivated</strong>.', 'wordpoints' )
		, 'success'
		, array( 'dismissible' => true )
	);

} elseif ( isset( $_REQUEST['action'] ) && 'update-selected' === sanitize_key( $_REQUEST['action'] ) ) {

	wordpoints_show_admin_message(
		esc_html__( 'No out of date extensions were selected.', 'wordpoints' )
		, 'warning'
		, array( 'dismissible' => true )
	);

} // End if ( error ) elseif ( other messages/errors ).

?>

<div class="wrap">
	<h1>
		<?php esc_html_e( 'WordPoints Extensions', 'wordpoints' ); ?>

		<?php if ( ( ! is_multisite() || is_network_admin() ) && current_user_can( 'install_wordpoints_extensions' ) ) : ?>
			<a href="<?php echo esc_url( self_admin_url( 'admin.php?page=wordpoints_install_extensions' ) ); ?>" class="page-title-action"><?php echo esc_html_x( 'Add New', 'extension', 'wordpoints' ); ?></a>
		<?php endif; ?>

		<?php if ( ! empty( $_REQUEST['s'] ) ) : ?>
			<span class="subtitle">
				<?php

				echo esc_html(
					sprintf(
						// translators: Search term.
						__( 'Search results for &#8220;%s&#8221;', 'wordpoints' )
						, sanitize_text_field( wp_unslash( $_REQUEST['s'] ) )
					)
				);

				?>
			</span>
		<?php endif; ?>
	</h1>

	<?php

	$wp_list_table = new WordPoints_Admin_List_Table_Extensions();
	$wp_list_table->prepare_items();

	?>

	<?php $wp_list_table->views(); ?>

	<form method="get" action="<?php echo esc_url( self_admin_url( 'admin.php' ) ); ?>">
		<input type="hidden" name="page" value="wordpoints_extensions" />
		<?php $wp_list_table->search_box( esc_html__( 'Search Installed Extensions', 'wordpoints' ), 'module' ); ?>
	</form>

	<form method="post" action="<?php echo esc_url( self_admin_url( 'admin.php?page=wordpoints_extensions' ) ); ?>">
		<input type="hidden" name="module_status" value="<?php echo esc_attr( $status ); ?>" />
		<input type="hidden" name="paged" value="<?php echo esc_attr( $page ); ?>" />
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
