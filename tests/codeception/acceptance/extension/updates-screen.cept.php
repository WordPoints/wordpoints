<?php

/**
 * Tests updating extensions via the Updates admin screen.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Update extensions via the Updates admin screen' );
$I->haveTestExtensionInstalledNeedingUpdate();
$I->amLoggedInAsAdminOnPage( 'wp-admin/update-core.php' );
$I->see( 'WordPoints Extensions', 'h2' );
$I->checkOption( '#checkbox_module-7module-7php' );
$I->click( 'Update Extensions' );
$I->see( 'Update WordPoints Extensions', '.wrap h1' );
$I->switchToIFrame( 'wordpoints_extension_updates' );
$I->see( 'Module 7 updated successfully.' );
$I->see( 'Return to Extensions page' );
$I->see( 'Return to WordPress Updates' );
$I->click( 'Return to WordPress Updates' );
$I->see( 'WordPress Updates', '.wrap h1' );

// EOF
