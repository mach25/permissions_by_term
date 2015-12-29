<?php

namespace Drupal\Tests\permissions_by_term\Unit;

use Drupal\Tests\UnitTestCase;

class SavePermissionsTests extends UnitTestCase {

  /**
   * Setup the test data. Mock class, disable constructor and set methods from
   * the class, which have to be available during the test.
   *
   * @return null
   */
  public function setUp() {
    $this->accessService = $this->getMockBuilder('Drupal\permissions_by_term\AccessService')
      ->disableOriginalConstructor()
      ->setMethods(
        array(
          'getArrayItemsToRemove',
          'getArrayItemsToAdd'
        )
      )
      ->getMock();
  }

  /**
   * Data provider method for the test. Specifies the data which will be passed
   * by the parameters.
   *
   * @return array
   */
  public static function getTestData(){
    $aRet[] = [
      'aExistingUserPermissions' => [1, 2, 3],
      'aSubmittedUserIdsGrantedAccess' => [1, 2, 4, 5],
      'aExistingRoleIdsGrantedAccess' => ['admin', 'anonymous'],
      'aSubmittedRolesGrantedAccess' => ['some role']
    ];
    return $aRet;
  }

  /**
   * Test the method which prepares data from form submit and database to be
   * applied in the database.
   *
   * @return null
   * @dataProvider getTestData()
   */
  public function testSaveTermPermissionsByUsers($aExistingUserPermissions,
                                                 $aSubmittedUserIdsGrantedAccess,
                                                 $aExistingRoleIdsGrantedAccess,
                                                 $aSubmittedRolesGrantedAccess){

    $aRet = $this->accessService->getPreparedDataForDatabaseQueries(
      $aExistingUserPermissions, $aSubmittedUserIdsGrantedAccess,
      $aExistingRoleIdsGrantedAccess, $aSubmittedRolesGrantedAccess);

    $this->assertEquals([3], $aRet['UserIdPermissionsToRemove']);
    $this->assertEquals([4, 5], $aRet['UserIdPermissionsToAdd']);
    $this->assertEquals(['admin', 'anonymous'], $aRet['UserRolePermissionsToRemove']);
    $this->assertEquals(['some role'], $aRet['aRoleIdPermissionsToAdd']);

  }

}
