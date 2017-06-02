<?php

/**
 * Tests updating a module.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Update a module' );
$I->haveTestModuleInstalledNeedingUpdate();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->see( 'There is a new version of Module 7 available.' );
$I->click( 'Update now', '.wordpoints-module-update-tr' );
$I->see( 'Update WordPoints Module', '.wrap h1' );
$I->see( 'Module updated successfully.' );
$I->see( 'Return to Modules page' );
$I->click( 'Activate Module' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->see( 'Module activated.', '.notice-success' );
$I->see( 'Deactivate', 'tr#module-7' );

// EOF
