<?php

/**
 * Tests viewing a module changelog.
 *
 * @package WordPoints\Codeception
 * @since 2.4.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'View a module changelog' );
$I->haveTestModuleInstalledNeedingUpdate();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_modules' );
$I->see( 'WordPoints Modules', '.wrap h1' );
$I->see( 'There is a new version of Module 7 available.' );
$I->click( 'View version 1.1.0 details', '.wordpoints-module-update-tr' );
$I->waitForJqueryAjax();
$I->see( 'Module 7', '#TB_title' );
$I->see( 'Test changelog for Module 7.' );

// EOF
