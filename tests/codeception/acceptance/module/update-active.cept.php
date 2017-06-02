<?php

/**
 * Tests updating an active module.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Update an active module' );
$I->haveTestModuleInstalled( 'module-7' );
$I->hadActivatedModule( 'module-7/module-7.php' );
$I->haveTestModuleInstalledNeedingUpdate();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->see( 'Deactivate', 'tr#module-7' );
$I->see( 'There is a new version of Module 7 available.' );
$I->click( 'Update now', '.wordpoints-module-update-tr' );
$I->see( 'Update WordPoints Module', '.wrap h1' );
$I->see( 'Module updated successfully.' );
$I->switchToIFrame( 'wordpoints_module_reactivation' );
$I->see( 'Module reactivated successfully.' );
$I->switchToIFrame();
$I->click( 'Return to Modules page' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->see( 'Deactivate', 'tr#module-7' );

// EOF
