<?php

/**
 * Tests creating a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Create a points reaction' );
$I->hadCreatedAPointsType();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->click( 'Add New Reaction', '#points-user_register' );
$I->waitForNewReaction();
$I->seeElement( '#points-user_register .add-reaction[disabled]' );
$I->fillField( 'description', 'Registering.' );
$I->fillField( 'log_text', 'Registration.' );
$I->fillField( 'points', '10' );
$I->click( 'Save', '#points-user_register' );
$I->waitForJqueryAjax();
$I->see( 'Your changes have been saved.', '#points-user_register .messages' );
$I->canSeeElement( '#points-user_register .add-reaction:not([disabled])' );

// EOF
