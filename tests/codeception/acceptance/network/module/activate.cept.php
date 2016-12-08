<?php

/**
 * Tests network-activating a module.
 *
 * @package WordPoints\Codeception
 * @since 2.2.0
 */

if ( ! is_wordpoints_network_active() ) {
	$scenario->skip( 'WordPoints must be network active.' );
}

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Network-activate a module' );
$I->haveTestModuleInstalled( 'module-7' );
$I->haveTestModuleInstalled( 'test-6' );
$I->hadActivatedModule( 'test-6/main-file.php', true );
$I->amLoggedInAsAdminOnPage( 'wp-admin/network/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->see( 'Network Deactivate', 'tr#test-6' );
$I->click( 'Network Activate', 'tr#module-7' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->see( 'Module activated.', '.notice-success' );
$I->see( 'Network Deactivate', 'tr#module-7' );
$I->see( 'Network Deactivate', 'tr#test-6' );

// EOF
