<?php

/**
 * Tests activating a module license.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Activate a module license' );
$I->haveTestModuleInstalled( 'module-9' );
$I->hadActivatedModule( 'module-9/module-9.php' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->fillField( 'license_key-test-9', 'test' );
$I->click( '[name=activate-license-9]' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->see( 'License activated.', '.notice-success' );
$I->seeInField( 'license_key-test-9', 'test' );
$I->click( '[name=deactivate-license-9]' );
$I->see( 'License deactivated.', '.notice-success' );
$I->seeInField( 'license_key-test-9', 'test' );

// EOF
