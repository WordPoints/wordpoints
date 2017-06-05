<?php

/**
 * Tests network-activating an extension.
 *
 * @package WordPoints\Codeception
 * @since 2.2.0
 */

if ( ! is_wordpoints_network_active() ) {
	$scenario->skip( 'WordPoints must be network active.' );
}

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Network-activate an extension' );
$I->haveTestExtensionInstalled( 'module-7' );
$I->haveTestExtensionInstalled( 'test-6' );
$I->hadActivatedExtension( 'test-6/main-file.php', true );
$I->amLoggedInAsAdminOnPage( 'wp-admin/network/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'Network Deactivate', 'tr#test-6' );
$I->click( 'Network Activate', 'tr#module-7' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'Extension activated.', '.notice-success' );
$I->see( 'Network Deactivate', 'tr#module-7' );
$I->see( 'Network Deactivate', 'tr#test-6' );

// EOF
