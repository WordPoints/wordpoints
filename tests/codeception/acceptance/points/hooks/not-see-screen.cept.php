<?php

/**
 * Tests that the Points Hooks screen is disabled on a fresh install.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Not see the Points Hooks screen on a fresh install' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_hooks' );
$I->seeElement( '#error-page' );

// EOF
