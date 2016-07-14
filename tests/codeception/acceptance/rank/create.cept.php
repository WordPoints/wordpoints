<?php

/**
 * Tests creating a rank.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Create a rank' );
$I->hadCreatedAPointsType();
$I->hadActivatedComponent( 'ranks' );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_ranks' );
$I->see( 'Ranks' );
$I->see( 'Points', '.nav-tab-active' );
$I->click( 'Add Rank' );
$I->waitForNewRank();
$I->fillField( '.wordpoints-rank.new [name=name]', 'Beginner' );
$I->fillField( '.wordpoints-rank.new [name=points]', '100' );
$I->click( 'Save', '.wordpoints-rank.new' );
$I->waitForJqueryAjax();
$I->seeElement( '.wordpoints-rank.editing .success' );
$I->see( 'Beginner', '.wordpoints-rank.editing .view' );
$I->canSeeInFormFields(
	'.wordpoints-rank.editing'
	, array(
		'name' => 'Beginner',
		'points' => '100',
	)
);

// EOF
