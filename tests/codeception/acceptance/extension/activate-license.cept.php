<?php

/**
 * Tests activating an extension license.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Activate an extension license' );
$I->haveTestExtensionInstalled( 'extension-9' );
$I->hadActivatedExtension( 'extension-9/extension-9.php' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_extensions' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->fillField( 'license_key-test-9', 'test' );
$I->click( '[name=activate-license-9]' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'License activated.', '.notice-success' );
$I->seeInField( 'license_key-test-9', 'test' );
$I->click( '[name=deactivate-license-9]' );
$I->see( 'License deactivated.', '.notice-success' );
$I->seeInField( 'license_key-test-9', 'test' );

// EOF
