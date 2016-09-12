<?php

/**
 * Tests use periods with a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.1.1
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Use rate limits with a points reaction' );
$I->hadCreatedAPointsReaction(
	array( 'event' => 'user_visit', 'target' => array( 'current:user' ) )
);
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->waitForElement( '#points-user_visit .wordpoints-hook-reaction' );
$I->click( 'Edit', '#points-user_visit .wordpoints-hook-reaction' );
$I->see( 'Rate Limit', '#points-user_visit .wordpoints-hook-reaction' );
$I->canSeeOptionIsSelected(
	'#points-user_visit .wordpoints-hook-reaction [name="periods[fire][0][length]"]'
	, 'Minute'
);
$I->selectOption( '#points-user_visit .wordpoints-hook-reaction [name="periods[fire][0][length]"]', 'Day' );
$I->click( 'Save', '#points-user_visit .wordpoints-hook-reaction' );
$I->waitForJqueryAjax();
$I->see( 'Your changes have been saved.', '#points-user_visit .messages' );
$I->canSeeOptionIsSelected(
	'#points-user_visit .wordpoints-hook-reaction [name="periods[fire][0][length]"]'
	, 'Day'
);

// EOF
