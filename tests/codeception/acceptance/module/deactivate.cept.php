<?php

/**
 * Tests deactivating a module.
 *
 * @package WordPoints\Codeception
 * @since 2.2.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Deactivate a module' );
$I->haveTestModuleInstalled( 'module-7' );
$I->hadActivatedModule( 'module-7/module-7.php' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->click( 'Deactivate', 'tr#module-7' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->see( 'Module deactivated.', '.notice-success' );
$I->see( 'Activate', 'tr#module-7' );

// EOF
