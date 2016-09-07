<?php

/**
 * Tests activating a module.
 *
 * @package WordPoints\Codeception
 * @since 2.2.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Activate a module' );
$I->haveTestModuleInstalled( 'module-7' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->click( 'Activate', 'tr#module-7' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->see( 'Module activated.', '.notice-success' );
$I->see( 'Deactivate', 'tr#module-7' );

// EOF
