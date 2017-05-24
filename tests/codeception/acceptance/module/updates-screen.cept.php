<?php

/**
 * Tests updating modules via the Updates admin screen.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Update modules via the Updates admin screen' );
$I->haveTestModuleInstalledNeedingUpdate();
$I->amLoggedInAsAdminOnPage( 'wp-admin/update-core.php' );
$I->see( 'WordPoints Modules', 'h2' );
$I->checkOption( '#checkbox_module-7module-7php' );
$I->click( 'Update Modules' );
$I->see( 'Update WordPoints Modules', '.wrap h1' );
$I->switchToIFrame( 'wordpoints_module_updates' );
$I->see( 'Module 7 updated successfully.' );
$I->see( 'Return to Modules page' );
$I->see( 'Return to WordPress Updates' );
$I->click( 'Return to WordPress Updates' );
$I->see( 'WordPress Updates', '.wrap h1' );

// EOF
