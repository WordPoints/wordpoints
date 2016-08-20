<?php

/**
 * Tests use legacy periods with a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.1.1
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Use rate limits with a legacy points reaction' );
$I->hadCreatedAPointsReaction(
	array(
		'event' => 'user_visit',
		'target' => array( 'current:user' ),
		'points_legacy_periods' => array(
			'fire' => array(
				array(
					'arg' => array( 'current:user' ),
					'length' => HOUR_IN_SECONDS
				),
			),
		),
	)
);
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->waitForElementVisible( '#points-user_visit .wordpoints-hook-reaction' );
$I->click( 'Edit', '#points-user_visit .wordpoints-hook-reaction' );
$I->see( 'Rate Limit', '#points-user_visit .wordpoints-hook-reaction' );
$I->cantSeeElement( '#points-user_visit .wordpoints-hook-reaction [name="periods[fire][0][length]"]' );
$I->canSeeOptionIsSelected(
	'#points-user_visit .wordpoints-hook-reaction [name="points_legacy_periods[fire][0][length]"]'
	, 'Hour'
);
$I->selectOption( '#points-user_visit .wordpoints-hook-reaction [name="points_legacy_periods[fire][0][length]"]', 'Day' );
$I->click( 'Save', '#points-user_visit .wordpoints-hook-reaction' );
$I->waitForJqueryAjax();
$I->see( 'Your changes have been saved.', '#points-user_visit .messages' );
$I->canSeeOptionIsSelected(
	'#points-user_visit .wordpoints-hook-reaction [name="points_legacy_periods[fire][0][length]"]'
	, 'Day'
);

// EOF
