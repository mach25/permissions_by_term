<?php

namespace Drupal\Tests\permissions_by_term\Unit;

use Doctrine\Common\Reflection\StaticReflectionMethod;
use Drupal\permissions_by_term\AccessService;
use Drupal\Tests\UnitTestCase;

class SavePermissionsTests extends UnitTestCase {

  public function setUp() {
    //$this->accessService = $this->getMock('Drupal\permissions_by_term\AccessService');

    $this->accessService = $this->getMockBuilder('Drupal\permissions_by_term\AccessService')
      ->disableOriginalConstructor()
      ->setMethods(array('getExistingUserTermPermissionsByTid', 'getSubmittedUserIdsGrantedAccess',
        'getExistingRoleTermPermissionsByTid', 'getSubmittedRolesGrantedAccess'))
      ->getMock();
  }

  private function deleteItemFromArray(){

  }

  private function addItemToArray(){

  }

  public function testSaveTermPermissionsByUsers(){

    $this->accessService->method('getExistingUserTermPermissionsByTid')
      ->willReturn([1, 2, 3]);
    $this->accessService->method('getSubmittedUserIdsGrantedAccess')
      ->willReturn([1, 2, 4, 5]);

    $this->accessService->method('getExistingRoleTermPermissionsByTid')
      ->willReturn(['admin', 'anonymous']);
    $this->accessService->method('getSubmittedRolesGrantedAccess')
      ->willReturn(['some role']);

    /*
    $this->accessService->method('saveTermPermissionsByUsers')
      ->willReturn([1, 2, 3, 4, 5]);
*/

    $aRet = $this->accessService->saveTermPermissionsByUsers();

    $this->assertEquals([3], $aRet['UserIdPermissionsToRemove']);
    $this->assertEquals([4, 5], $aRet['UserIdPermissionsToAdd']);
    $this->assertEquals(['admin', 'anonymous'], $aRet['UserRolePermissionsToRemove']);
    $this->assertEquals(['some role'], $aRet['aRoleIdPermissionsToAdd']);

    //$this->accessService->saveTermPermissionsByUsers();
  }

}
