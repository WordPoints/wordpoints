<?php

/**
 * Tests bulk updating active extensions.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Bulk update active extensions' );
$I->haveTestExtensionInstalled( 'module-7' );
$I->hadActivatedExtension( 'module-7/module-7.php' );
$I->haveTestExtensionInstalledNeedingUpdate();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_extensions' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'There is a new version of Module 7 available.' );
$I->checkOption( '#checkbox_module-7module-7php' );
$I->selectOption( 'action', 'Update' );
$I->click( 'Apply', '.actions' );
$I->see( 'Update WordPoints Extensions', '.wrap h1' );
$I->switchToIFrame( 'wordpoints_extension_updates' );
$I->see( 'Module 7 updated successfully.' );
$I->see( 'Reactivating extension' );
$I->switchToIFrame( 'wordpoints_extension_reactivation' );
$I->see( 'Extension reactivated successfully.' );

// EOF
