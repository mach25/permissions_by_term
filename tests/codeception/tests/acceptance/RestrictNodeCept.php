<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Check that an node with restricted access is not accessible for guests.');
$I->amOnPage('node/1');
$I->see('Access denied');
$I->doNotSeeErrors();
