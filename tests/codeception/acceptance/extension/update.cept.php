<?php

/**
 * Tests updating an extension.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Update an extension' );
$I->haveTestExtensionInstalledNeedingUpdate();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'There is a new version of Module 7 available.' );
$I->click( 'Update now', '.wordpoints-extension-update-tr' );
$I->see( 'Update WordPoints Extension', '.wrap h1' );
$I->see( 'Extension updated successfully.' );
$I->see( 'Return to Extensions page' );
$I->click( 'Activate Extension' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'Extension activated.', '.notice-success' );
$I->see( 'Deactivate', 'tr#module-7' );

// EOF
