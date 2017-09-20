<?php

/**
 * WordPoints administration screen: Settings.
 *
 * @package WordPoints\Administration
 * @since 1.0.0
 */

/**
 * Display the configure administration panels.
 *
 * @since 1.0.0
 *
 * @uses do_action() To call 'wordpoints_admin_screen_head'.
 * @uses wordpoints_admin_show_tabs() To display the tabs.
 * @uses wordpoints_admin_get_current_tab() To get the current tab.
 * @uses do_action() To call 'wordpoints_admin_screen_foot'.
 */
function wordpoints_admin_screen_configure() {

	?>

	<div class="wrap">

		<?php

		/**
		 * At the top of the configure screens.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wordpoints_admin_configure_head' );

		wordpoints_admin_show_tabs(
			array(
				'general'    => __( 'General Settings', 'wordpoints' ),
				'components' => __( 'Components', 'wordpoints' ),
			)
		);

		if ( 'components' === wordpoints_admin_get_current_tab() ) {
			/**
			 * The Components screen.
			 *
			 * @since 1.0.0
			 */
			require WORDPOINTS_DIR . 'admin/screens/configure-components.php';
		} else {
			/**
			 * The Settings screen.
			 *
			 * @since 1.0.0
			 */
			require WORDPOINTS_DIR . 'admin/screens/configure-settings.php';
		}

		/**
		 * At the bottom of the configure screens.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wordpoints_admin_configure_foot' );

		?>

	</div>

	<?php
}

// EOF
