<?php

namespace Drupal\Tests\permissions_by_term\Unit;

use Drupal\Tests\UnitTestCase;

class SavePermissionsTests extends UnitTestCase {

  /**
   * Setup the test data.
   *
   * @return null
   */
  public function setUp() {
    $this->accessService = $this->getMockBuilder('Drupal\permissions_by_term\AccessService')
      ->disableOriginalConstructor()
      // Set methods which can be overwritten.
      ->setMethods(array('getExistingUserTermPermissionsByTid', 'getSubmittedUserIdsGrantedAccess',
        'getExistingRoleTermPermissionsByTid', 'getSubmittedRolesGrantedAccess'))
      ->getMock();
  }

  /**
   * Test the function which will update data in database for the access by
   * user and role ids.
   *
   * @return null
   */
  public function testSaveTermPermissionsByUsers(){

    /**
     * Mock the methods which will retrieve data from database or form.
     */
    $this->accessService->method('getExistingUserTermPermissionsByTid')
      ->willReturn([1, 2, 3]);
    $this->accessService->method('getSubmittedUserIdsGrantedAccess')
      ->willReturn([1, 2, 4, 5]);

    $this->accessService->method('getExistingRoleTermPermissionsByTid')
      ->willReturn(['admin', 'anonymous']);
    $this->accessService->method('getSubmittedRolesGrantedAccess')
      ->willReturn(['some role']);

    /**
     * Compare the arrays which are returned by the save function.
     */
    $aRet = $this->accessService->saveTermPermissionsByUsers();

    $this->assertEquals([3], $aRet['UserIdPermissionsToRemove']);
    $this->assertEquals([4, 5], $aRet['UserIdPermissionsToAdd']);
    $this->assertEquals(['admin', 'anonymous'], $aRet['UserRolePermissionsToRemove']);
    $this->assertEquals(['some role'], $aRet['aRoleIdPermissionsToAdd']);

  }

}
