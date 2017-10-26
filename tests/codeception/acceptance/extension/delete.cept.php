<?php

/**
 * Tests deleting an extension.
 *
 * @package WordPoints\Codeception
 * @since 2.1.3
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Delete an extension' );
$I->haveTestExtensionInstalled( 'module-7' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_extensions' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->click( 'Delete', 'tr#module-7' );
$I->see( 'Delete extension', '.wrap h1' );
$I->see( 'You are about to remove the following extension:' );
$I->see( 'Module 7 by J.D. Grimes' );
$I->click( 'Yes, Delete these files' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'The selected extensions have been deleted.', '.notice-success' );
$I->cantSeeElementInDOM( 'tr#module-7' );

// EOF
