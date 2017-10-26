<?php

/**
 * Install module administration panel.
 *
 * @package WordPoints\Administration\Modules
 * @since 1.1.0
 */

global $tab;

if ( empty( $tab ) ) {
	$tab = 'upload';
}

?>
<script type="text/javascript">
jQuery( document ).ready( function() {

	jQuery( '#toplevel_page_wordpoints_configure, #toplevel_page_wordpoints_configure > a' )
		.addClass( 'wp-has-current-submenu wp-menu-open' );

	jQuery( '#toplevel_page_wordpoints_configure' )
		.find( 'a[href="admin.php?page=wordpoints_extensions"]' )
			.parent()
			.addBack()
			.addClass( 'current' );
});
</script>
<div class="wrap">
	<h1><?php esc_html_e( 'Install Extensions', 'wordpoints' ); ?></h1>
	<?php

	/**
	 * At the top of the extension install screen.
	 *
	 * This allows more tabs to be added to the extension install screen.
	 *
	 * @since 2.4.0
	 */
	do_action( 'wordpoints_install_extensions_screen' );

	/**
	 * At the top of the extension install screen.
	 *
	 * This allows more tabs to be added to the extension install screen.
	 *
	 * @since 1.1.0
	 * @deprecated 2.4.0 Use 'wordpoints_install_extensions_screen' instead.
	 */
	do_action_deprecated( 'wordpoints_install_modules_screen', array(), '2.4.0', 'wordpoints_install_extensions_screen' );

	?>
	<br class="clear" />
	<?php

	/**
	 * Fires after the main content of the Install Extensions screen.
	 *
	 * The dynamic portion of the action hook, $tab, allows for targeting
	 * individual tabs, for instance 'wordpoints_install_extensions-upload'.
	 *
	 * @since 2.4.0
	 */
	do_action( "wordpoints_install_extensions-{$tab}" );

	/**
	 * Fires after the main content of the Install Extensions screen.
	 *
	 * The dynamic portion of the action hook, $tab, allows for targeting
	 * individual tabs, for instance 'wordpoints_install_modules-module-upload'.
	 *
	 * @since 1.1.0
	 * @deprecated 2.4.0 Use "wordpoints_install_extensions-{$tab}" instead.
	 */
	do_action_deprecated( "wordpoints_install_modules-{$tab}", array(), '2.4.0', "wordpoints_install_extensions-{$tab}" );

	?>
</div>
