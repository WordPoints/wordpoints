<?php

/**
 * Tests deactivating an extension.
 *
 * @package WordPoints\Codeception
 * @since 2.2.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Deactivate an extension' );
$I->haveTestExtensionInstalled( 'module-7' );
$I->hadActivatedExtension( 'module-7/module-7.php' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->click( 'Deactivate', 'tr#module-7' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'Extension deactivated.', '.notice-success' );
$I->see( 'Activate', 'tr#module-7' );

// EOF
