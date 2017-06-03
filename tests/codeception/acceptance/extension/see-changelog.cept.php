<?php

/**
 * Tests viewing an extension changelog.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'View an extension changelog' );
$I->haveTestExtensionInstalledNeedingUpdate();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Extensions', '.wrap h1' );
$I->see( 'There is a new version of Module 7 available.' );
$I->click( 'View version 1.1.0 details', '.wordpoints-extension-update-tr' );
$I->waitForJqueryAjax();
$I->see( 'Test changelog for Module 7.' );

// EOF
