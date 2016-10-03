<?php

/**
 * Tests cancelling deleting a condition for a points reaction.
 *
 * @package WordPoints\Codeception
 * @since 2.1.3
 */

use WordPoints\Tests\Codeception\Element\Reaction;
use WordPoints\Tests\Codeception\Element\ReactionCondition;

$I = new AcceptanceTester( $scenario );
$I->wantTo( 'Cancel deleting a condition for a points reaction' );
$the_reaction = $I->hadCreatedAPointsReaction(
	array(
		'event'      => 'post_publish\post',
		'target'     => array( 'post\post', 'author', 'user' ),
		'conditions' => array(
			'toggle_on' => array(
				'post\post' => array(
					'content' => array(
						'_conditions' => array(
							array(
								'type'     => 'contains',
								'settings' => array(
									'value' => 'Test',
								)
							)
						)
					)
				)
			)
		)
	)
);
$reaction  = new Reaction( $I, $the_reaction );
$condition = new ReactionCondition( $I, $reaction );
$I->amLoggedInAsAdminOnPage( 'wp-admin/admin.php?page=wordpoints_points_types' );
$I->waitForElement( (string) $reaction );
$reaction->edit();
$condition->delete();
$I->cantSeeElement( (string) $condition );
$reaction->cancel();
$I->canSeeElement( (string) $condition );

// EOF
