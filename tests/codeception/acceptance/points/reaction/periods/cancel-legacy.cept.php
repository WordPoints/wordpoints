<?php

/**
 * Tests cancelling updating a points reaction that uses legacy periods.
 *
 * @package WordPoints\Codeception
 * @since 2.2.0
 */

use WordPoints\Tests\Codeception\Element\Reaction;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Cancel updating a legacy points reaction with rate limits' );
$the_reaction = $I->hadCreatedAPointsReaction(
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
$reaction = new Reaction( $I, $the_reaction );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->waitForElement( (string) $reaction );
$reaction->edit();
$I->canSeeInField( $reaction . '.wordpoints-hook-period-length-in-units', 2 );
$I->canSeeOptionIsSelected( $reaction . '.wordpoints-hook-period-units', 'Hours' );
$I->fillField( $reaction . '.wordpoints-hook-period-length-in-units', 1 );
$I->selectOption( $reaction . '.wordpoints-hook-period-units', 'Days' );
$reaction->cancel();
$I->canSeeInField( $reaction . '.wordpoints-hook-period-length-in-units', 2 );
$I->canSeeOptionIsSelected( $reaction . '.wordpoints-hook-period-units', 'Hours' );

// EOF
