<?php

/**
 * Tests cancelling updating a points reaction that uses periods.
 *
 * @package WordPoints\Codeception
 * @since 2.2.0
 */

use WordPoints\Tests\Codeception\Element\Reaction;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Cancel updating a points reaction with rate limits' );
$the_reaction = $I->hadCreatedAPointsReaction(
	array( 'event' => 'user_visit', 'target' => array( 'current:user' ) )
);

$reaction = new Reaction( $I, $the_reaction );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->waitForElement( (string) $reaction );
$reaction->edit();
$I->fillField( $reaction . '.wordpoints-hook-period-length-in-units', 1 );
$I->selectOption( $reaction . '.wordpoints-hook-period-units', 'Days' );
$reaction->save();
$I->fillField( $reaction . '.wordpoints-hook-period-length-in-units', 5 );
$I->selectOption( $reaction . '.wordpoints-hook-period-units', 'Hours' );
$reaction->cancel();
$I->seeInField( $reaction . '.wordpoints-hook-period-length-in-units', 1 );
$I->seeOptionIsSelected( $reaction . '.wordpoints-hook-period-units', 'Days' );

// EOF
