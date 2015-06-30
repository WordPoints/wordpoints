<?php

/**
 * Loaded by .maintenance when WordPoints.
 *
 * Only loaded with WordPoints is performing a breaking update.
 *
 * @package WordPoints
 * @since 2.0.0
 */

/** @var int $upgrading */
global $upgrading;

$time = time();

// Don't lock the user out of their site if the file fails to delete for some reason.
if ( $time - $upgrading >= 10 * MINUTE_IN_SECONDS ) {
	return;
}

// If we're not running a module check, let the maintenance message show.
if ( ! isset( $_GET['wordpoints_module_check'], $_GET['check_module'] ) ) {
	return;
}

// Normally we might try to verify the nonce here, however, the nonce functions are
// pluggable and so won't be loaded until much later.

// Trick wp_maintenance() into not showing the maintenance message.
$upgrading = $time - 10 * MINUTE_IN_SECONDS;

// EOF
