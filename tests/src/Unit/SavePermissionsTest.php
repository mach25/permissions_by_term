<?php

namespace Drupal\Tests\permissions_by_term\Unit;

use Drupal\permissions_by_term\AccessService;
use Drupal\Tests\UnitTestCase;

class SavePermissionsTests extends UnitTestCase {

  public function setUp() {
    $this->accessService = $this->getMock('Drupal\permissions_by_term\AccessService');
  }

  private function deleteItemFromArray(){

  }

  private function addItemToArray(){

  }

  public function testSaveTermPermissionsByUsers(){
    $this->accessService->saveTermPermissionsByUsers();
  }

}
