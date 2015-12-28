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
      ->setMethods(array('getUserTermPermissionsByTid', 'getUserIdsGrantedAccess',
        'deleteOneTermPermissionByUserId', 'addOneTermPermission'))
      ->getMock();
  }

  private function deleteItemFromArray(){

  }

  private function addItemToArray(){

  }

  public function testSaveTermPermissionsByUsers(){

    $aOne = array(1, 2, 3, 4, 5);



    $this->accessService->method('getUserTermPermissionsByTid')
      ->willReturn([1, 2, 3, 4, 5]);
    $this->accessService->method('getUserIdsGrantedAccess')
      ->willReturn([1, 2, 3, 4, 5]);
/*
    $this->accessService->method('saveTermPermissionsByUsers')
      ->willReturn([1, 2, 3, 4, 5]);
*/

    $aR = $this->accessService->saveTermPermissionsByUsers();
    var_dump($aR);
    $this->assertEquals($aOne, $aR);

    //$this->accessService->saveTermPermissionsByUsers();
  }

}
