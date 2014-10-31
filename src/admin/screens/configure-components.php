<?php

/**
 * WordPoints Administration Screen: Configure > Components.
 *
 * This template displays the Components tab on the Configure panel.
 *
 * @package WordPoints\Administration
 * @since 1.0.0
 */

$wordpoints_components = WordPoints_Components::instance();

$components = $wordpoints_components->get();

//
// Show messages and errors.
//

if ( isset( $_GET['wordpoints_component'], $_GET['_wpnonce'] ) && $wordpoints_components->is_registered( $_GET['wordpoints_component'] ) ) {

	$component = sanitize_key( $_GET['wordpoints_component'] );

	if ( isset( $_GET['message'] ) && wp_verify_nonce( $_GET['_wpnonce'], "wordpoints_component_message-{$component}" ) ) {

		switch ( $_GET['message'] ) {

			case '1':
				if ( $wordpoints_components->is_active( $component ) ) {
					$message = __( 'Component &#8220;%s&#8221; activated!', 'wordpoints' );
				}
			break;

			case '2':
				if ( ! $wordpoints_components->is_active( $component ) ) {
					$message = __( 'Component &#8220;%s&#8221; deactivated!', 'wordpoints' );
				}
			break;
		}

		if ( isset( $message ) ) {

			wordpoints_show_admin_message( esc_html( sprintf( $message, $components[ $component ]['name'] ) ) );
		}

	} elseif ( isset( $_GET['error'] ) && wp_verify_nonce( $_GET['_wpnonce'], "wordpoints_component_error-{$component}" ) ) {

		switch ( $_GET['error'] ) {

			case '1':
				if ( ! $wordpoints_components->is_active( $component ) ) {
					$error = __( 'The component &#8220;%s&#8221; could not be activated. Please try again.', 'wordpoints' );
				}
			break;

			case '2':
				if ( $wordpoints_components->is_active( $component ) ) {
					$error = __( 'The component &#8220;%s&#8221; could not be deactivated. Please try again.', 'wordpoints' );
				}
			break;
		}

		if ( isset( $error ) ) {

			wordpoints_show_admin_error( esc_html( sprintf( $error, $components[ $component ]['name'] ) ) );
		}
	}
}

//
// Display the page.
//

?>

<p><?php esc_html_e( 'View installed WordPoints components.', 'wordpoints' ); ?></p>

<?php

/**
 * Top of the components administration page.
 *
 * @since 1.0.0
 */
do_action( 'wordpoints_admin_components_top' );

?>

<table id="wordpoints_components_table" class="widefat">
	<thead>
		<tr>
			<th scope="col" width="150"><?php echo esc_html_x( 'Component', 'components table heading', 'wordpoints' ); ?></th>
			<th scope="col"><?php echo esc_html_x( 'Description', 'components table heading', 'wordpoints' ); ?></th>
			<th scope="col" width="80"><?php echo esc_html_x( 'Version', 'components table heading', 'wordpoints' ); ?></th>
			<th scope="col" width="70"><?php echo esc_html_x( 'Action', 'components table heading', 'wordpoints' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="col"><?php echo esc_html_x( 'Component', 'components table heading', 'wordpoints' ); ?></th>
			<th scope="col"><?php echo esc_html_x( 'Description', 'components table heading', 'wordpoints' ); ?></th>
			<th scope="col"><?php echo esc_html_x( 'Version', 'components table heading', 'wordpoints' ); ?></th>
			<th scope="col"><?php echo esc_html_x( 'Action', 'components table heading', 'wordpoints' ); ?></th>
		</tr>
	</tfoot>

	<?php

	foreach ( $components as $component ) {

		if ( $component['component_uri'] !== '' ) {
			$component_name = '<a href="' . esc_attr( esc_url( $component['component_uri'] ) ) . '">' . esc_html( $component['name'] ) . '</a>';
		} else {
			$component_name = esc_html( $component['name'] );
		}

		$author = '';

		if ( $component['author'] !== '' ) {

			if ( $component['author_uri'] !== '' ) {
				$author_name = '<a href="' . esc_attr( esc_url( $component['author_uri'] ) ) . '">' . esc_html( $component['author'] ) . '</a>';
			} else {
				$author_name = esc_html( $component['author'] );
			}

			/* translators: %s is the component author's name. */
			$author = ' | ' . sprintf( __( 'By %s', 'wordpoints' ), $author_name );
		}

		if ( $wordpoints_components->is_active( $component['slug'] ) ) {

			$action = 'deactivate';
			$button = __( 'Deactivate', 'wordpoints' );

		} else {

			$action = 'activate';
			$button = __( 'Activate', 'wordpoints' );
		}

		?>

		<tr>
			<td><?php echo $component_name; ?></td>
			<td><?php echo $component['description'] . $author; ?></td>
			<td><?php echo $component['version']; ?></td>
			<td>
				<form method="post" name="wordpoints_components_form_<?php echo esc_attr( $component['slug'] ); ?>" action="<?php esc_attr( esc_url( self_admin_url( 'page=wordpoints_configure&tab=components' ) ) ); ?>">
					<input type="hidden" name="wordpoints_component_action" value="<?php echo esc_attr( $action ); ?>" />
					<input type="hidden" name="wordpoints_component" value="<?php echo esc_attr( $component['slug'] ); ?>" />
					<?php wp_nonce_field( "wordpoints_{$action}_component-{$component['slug']}" ); ?>
					<?php submit_button( $button, "secondary wordpoints-component-{$action}", "wordpoints-component-{$action}_{$component['slug']}", false ); ?>
				</form>
			</td>
		</tr>

	<?php

	} // foreach ( $components as $component )

	?>

</table>

<?php

/**
 * Bottom of components administration panel.
 *
 * @since 1.0.0
 */
do_action( 'wordpoints_admin_components_bottom' );

// EOF
