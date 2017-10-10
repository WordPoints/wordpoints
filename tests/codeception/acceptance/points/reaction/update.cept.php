<?php

/**
 * Tests updating a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

use WordPoints\Tests\Codeception\Element\Reaction;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Update a points reaction' );
$the_reaction = $I->hadCreatedAPointsReaction();
$reaction     = new Reaction( $I, $the_reaction );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->see( 'Test description.', "{$reaction} .title" );
$reaction->edit();
$I->canSeeInFormFields(
	"{$reaction} form"
	, array(
		'description' => 'Test description.',
		'log_text'    => 'Test log text.',
		'points'      => '10',
	)
);
$I->fillField( "{$reaction} [name=description]", 'Registering.' );
$I->fillField( "{$reaction} [name=log_text]", 'Registration.' );
$I->fillField( "{$reaction} [name=points]", '50' );
$reaction->save();
$I->canSeeInFormFields(
	"{$reaction} form"
	, array(
		'description' => 'Registering.',
		'log_text'    => 'Registration.',
		'points'      => '50',
	)
);
$I->see( 'Registering.', "{$reaction} .title" );

// EOF
