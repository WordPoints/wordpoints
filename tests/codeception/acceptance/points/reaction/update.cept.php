<?php

/**
 * Tests updating a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.1.0
 */

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Update a points reaction' );
$I->hadCreatedAPointsReaction();
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->see( 'Test description.', '#points-user_register .wordpoints-hook-reaction .title' );
$I->click( 'Edit', '#points-user_register .wordpoints-hook-reaction' );
$I->canSeeInFormFields(
	'#points-user_register .wordpoints-hook-reaction form'
	, array(
		'description' => 'Test description.',
		'log_text' => 'Test log text.',
		'points' => '10',
	)
);
$I->fillField( 'description', 'Registering.' );
$I->fillField( 'log_text', 'Registration.' );
$I->fillField( 'points', '50' );
$I->click( 'Save', '#points-user_register .wordpoints-hook-reaction' );
$I->waitForJqueryAjax();
$I->see( 'Your changes have been saved.', '#points-user_register .messages' );
$I->canSeeInFormFields(
	'#points-user_register .wordpoints-hook-reaction form'
	, array(
		'description' => 'Registering.',
		'log_text' => 'Registration.',
		'points' => '50',
	)
);
$I->see( 'Registering.', '#points-user_register .wordpoints-hook-reaction .title' );

// EOF
