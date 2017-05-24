<?php

/**
 * Tests bulk updating modules.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Bulk update modules' );
$I->haveTestModuleInstalledNeedingUpdate();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->see( 'There is a new version of Module 7 available.' );
$I->checkOption( '#checkbox_module-7module-7php' );
$I->selectOption( 'action', 'Update' );
$I->click( 'Apply', '.actions' );
$I->see( 'Update WordPoints Modules', '.wrap h1' );
$I->switchToIFrame( 'wordpoints_module_updates' );
$I->see( 'Module 7 updated successfully.' );
$I->see( 'Return to Modules page' );
$I->see( 'Return to WordPress Updates' );
$I->click( 'Return to Modules page' );
$I->see( 'WordPoints Modules', '.wrap h1' );

// EOF
