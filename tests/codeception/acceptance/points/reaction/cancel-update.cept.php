<?php

/**
 * Tests canceling updating a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

use WordPoints\Tests\Codeception\Element\Reaction;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Update a points reaction, but change my mind' );
$the_reaction = $I->hadCreatedAPointsReaction();
$reaction     = new Reaction( $I, $the_reaction );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->see( 'Test description.', "{$reaction} .title" );
$reaction->edit();
$I->canSeeInFormFields(
	"{$reaction} form"
	, array(
		'description' => 'Test description.',
		'log_text' => 'Test log text.',
		'points' => '10',
	)
);
$I->cantSee( 'Edit', "{$reaction} .view" );
$I->canSee( 'Close', "{$reaction} .view" );
$I->canSee( 'Close', "{$reaction} .actions" );
$I->canSee( 'Save', "{$reaction} .actions :disabled" );
$I->cantSee( 'Cancel', "{$reaction} .actions" );
$I->fillField( "{$reaction} [name=description]", 'Registering.' );
$I->fillField( "{$reaction} [name=log_text]", 'Registration.' );
$I->fillField( "{$reaction} [name=points]", '50' );
$I->cantSee( 'Edit', "{$reaction} .view" );
$I->cantSee( 'Close', "{$reaction} .view" );
$I->cantSee( 'Close', "{$reaction} .actions" );
$I->canSee( 'Save', "{$reaction} .actions :not(:disabled)" );
$reaction->cancel();
$I->canSeeInFormFields(
	"{$reaction} form"
	, array(
		'description' => 'Test description.',
		'log_text' => 'Test log text.',
		'points' => '10',
	)
);
$I->canSee( 'Close', "{$reaction} .view" );
$I->canSee( 'Close', "{$reaction} .actions" );
$I->canSee( 'Save', "{$reaction} .actions :disabled" );
$I->cantSee( 'Cancel', "{$reaction} .actions" );

// EOF
