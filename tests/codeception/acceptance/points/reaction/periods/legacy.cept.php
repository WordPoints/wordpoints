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
					'length' => 2 * HOUR_IN_SECONDS,
				),
			),
		),
	)
);
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->waitForElement( '#points-user_visit .wordpoints-hook-reaction' );
$I->click( 'Edit', '#points-user_visit .wordpoints-hook-reaction' );
$I->see( 'Rate Limit', '#points-user_visit .wordpoints-hook-reaction' );
$I->cantSeeElementInDOM( '#points-user_visit .wordpoints-hook-reaction [name="periods[fire][0][length]"]' );
$I->canSeeElementInDOM( '#points-user_visit .wordpoints-hook-reaction [name="points_legacy_periods[fire][0][length]"]' );
$I->canSeeInField( '#points-user_visit .wordpoints-hook-reaction .wordpoints-hook-period-length-in-units', 2 );
$I->canSeeOptionIsSelected(
	'#points-user_visit .wordpoints-hook-reaction .wordpoints-hook-period-units'
	, 'Hours'
);
$I->fillField( '#points-user_visit .wordpoints-hook-reaction .wordpoints-hook-period-length-in-units', 1 );
$I->selectOption( '#points-user_visit .wordpoints-hook-reaction .wordpoints-hook-period-units', 'Days' );
$I->click( 'Save', '#points-user_visit .wordpoints-hook-reaction' );
$I->waitForJqueryAjax();
$I->see( 'Your changes have been saved.', '#points-user_visit .messages' );

// EOF
