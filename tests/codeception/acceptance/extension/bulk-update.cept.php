<?php

/**
 * Tests bulk updating extensions.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Bulk update extensions' );
$I->haveTestExtensionInstalledNeedingUpdate();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'There is a new version of Module 7 available.' );
$I->checkOption( '#checkbox_module-7module-7php' );
$I->selectOption( 'action', 'Update' );
$I->click( 'Apply', '.actions' );
$I->see( 'Update WordPoints Extensions', '.wrap h1' );
$I->switchToIFrame( 'wordpoints_extension_updates' );
$I->see( 'Module 7 updated successfully.' );
$I->see( 'Return to Extensions page' );
$I->see( 'Return to WordPress Updates' );
$I->click( 'Return to Extensions page' );
$I->see( 'WordPoints Extensions', '.wrap h1' );

// EOF
