<?php

/**
 * Tests deleting a module.
 *
 * @package WordPoints\Codeception
 * @since 2.1.3
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Delete a module' );
$I->haveTestModuleInstalled( 'module-7' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->click( 'Delete', 'tr#module-7' );
$I->see( 'Delete module', '.wrap h1' );
$I->see( 'You are about to remove the following module:' );
$I->see( 'Module 7 by J.D. Grimes' );
$I->click( 'Yes, Delete these files' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->see( 'The selected modules have been deleted.', '.notice-success' );
$I->cantSeeElementInDOM( 'tr#module-7' );

// EOF
