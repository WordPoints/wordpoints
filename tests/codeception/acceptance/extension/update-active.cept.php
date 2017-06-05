<?php

/**
 * Tests updating an active extension.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Update an active extension' );
$I->haveTestExtensionInstalled( 'module-7' );
$I->hadActivatedExtension( 'module-7/module-7.php' );
$I->haveTestExtensionInstalledNeedingUpdate();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'Deactivate', 'tr#module-7' );
$I->see( 'There is a new version of Module 7 available.' );
$I->click( 'Update now', '.wordpoints-extension-update-tr' );
$I->see( 'Update WordPoints Extension', '.wrap h1' );
$I->see( 'Extension updated successfully.' );
$I->switchToIFrame( 'wordpoints_extension_reactivation' );
$I->see( 'Extension reactivated successfully.' );
$I->switchToIFrame();
$I->click( 'Return to Extensions page' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'Deactivate', 'tr#module-7' );

// EOF
