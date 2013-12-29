<?php

/**
 * WordPoints Administration Screen: Configure > Settings
 *
 * @package WordPoints\Administration
 * @since 1.0.0
 */

if ( isset( $_POST['wordpoints_settings_nonce'] ) && wp_verify_nonce( $_POST['wordpoints_settings_nonce'], 'wordpoints_settings_submit' ) ) {

	// - The form has been submitted.

	$excluded_users = preg_replace( '/\s/', '', $_POST['excluded_users'] );

	if ( ! empty( $excluded_users ) ) {

		$excluded_users = explode( ',', $excluded_users );

		$excluded_users = array_unique( array_filter( $excluded_users, 'wordpoints_posint' ) );
	}

	wordpoints_update_network_option( 'wordpoints_excluded_users', $excluded_users );

	/**
	 * WordPoints settings form submitted.
	 *
	 * You should hook to this to update any custom settings that you add.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wordpoints_admin_settings_update' );

	wordpoints_show_admin_message( __( 'Settings updated.', 'wordpoints' ) );
}

?>

<p><?php _e( 'Configure WordPoints to your liking.', 'wordpoints' ); ?></p>
<form id="wordpoints-settings" method="post" action="">

	<?php

	/**
	 * Top of WordPoints settings form.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wordpoints_admin_settings_top' );

	?>

	<h3><?php _e( 'Excluded Users', 'wordpoints' ); ?></h3>
	<p><?php _e( 'Enter the IDs of users to exclude from leader boards, logs, etc. This may be useful if you use certain accounts for testing.', 'wordpoints' ); ?></p>
	<table class="form-table">
		<tbody>
			<tr>
				<th>
					<label for="excluded_users"><?php _e( 'Excluded Users', 'wordpoints' ); ?></label>
				</th>
				<td>
					<input type="text" name="excluded_users" id="excluded_users" value="<?php echo esc_attr( implode( ', ', wordpoints_get_array_option( 'wordpoints_excluded_users', 'network' ) ) ); ?>" />
				</td>
			</tr>
		</tbody>
	</table>

	<?php

	/**
	 * Bottom of WordPoints settings form.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wordpoints_admin_settings_bottom' );

	?>

	<?php wp_nonce_field( 'wordpoints_settings_submit', 'wordpoints_settings_nonce' ); ?>
	<?php submit_button(); ?>
</form>
