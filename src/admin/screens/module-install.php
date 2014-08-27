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
		.addClass( 'wp-has-current-submenu wp-menu-open' )

	jQuery( '#toplevel_page_wordpoints_configure a[href="admin.php?page=wordpoints_modules"]' )
		.parent().addBack().addClass( 'current' );
});
</script>
<div class="wrap">
	<h2><?php esc_html_e( 'Install Modules', 'wordpoints' ); ?></h2>
	<?php

	/**
	 * At the top of the module install screen.
	 *
	 * This allows more tabs to be added to the module install screen.
	 *
	 * @since 1.1.0
	 */
	do_action( 'wordpoints_install_modules_screen' );

	?>
	<br class="clear" />
	<?php

	/**
	 * Fires after the main content of the Install Modules screen.
	 *
	 * The dynamic portion of the action hook, $tab, allows for targeting
	 * individual tabs, for instance 'wordpoints_install_modules-module-upload'.
	 *
	 * @since 1.1.0
	 */
	do_action( "wordpoints_install_modules-{$tab}" );

	?>
</div>
