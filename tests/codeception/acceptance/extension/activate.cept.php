<?php

/**
 * Tests activating an extension.
 *
 * @package WordPoints\Codeception
 * @since 2.2.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Activate an extension' );
$I->haveTestExtensionInstalled( 'module-7' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->click( 'Activate', 'tr#module-7' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'Extension activated.', '.notice-success' );
$I->see( 'Deactivate', 'tr#module-7' );

// EOF
