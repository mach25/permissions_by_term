<?php

use Drupal\Tests\permissions_by_term\Unit\Base;
use Drupal\permissions_by_term\NodeAccess;
use \Drupal\permissions_by_term\Factory\NodeAccessRecordFactory;

/**
 * Class NodeAccess
 */
class NodeAccessTest extends PHPUnit_Framework_TestCase {

  use Base;

  /**
   * @dataProvider provideTableData
   */
  public function testCreateRealms($permissions_by_term_user, $permissions_by_term_role) {
    $accessStorage = $this->createMock('Drupal\permissions_by_term\AccessStorage',
      [
        'fetchUidsByRid' => [999, 87, 44],
        'getNidsByTid' => [64, 826, 91, 21],
      ]
    );
    $nodeAccessStorageFactory = new NodeAccessRecordFactory();
    $nodeAccess = new NodeAccess($accessStorage, $nodeAccessStorageFactory);
    $objectStack = $nodeAccess->createGrants($permissions_by_term_user, $permissions_by_term_role);

    $this->assertTrue($this->propertiesHaveValues($objectStack));
    $this->assertTrue($this->realmContainsTwoNumbers($objectStack));
  }

  private function realmContainsTwoNumbers($objectStack) {
    foreach ($objectStack as $object) {
      foreach ($object as $propertyName => $propertyValue) {
        if ($propertyName == 'realm') {
          if ($this->stringContainsTwoNumbers($propertyValue) === FALSE) {
            throw new \Exception('The realm does not contain two numbers. It must contain the UID and TID.');
            return FALSE;
          }

        }
      }
    }

    return TRUE;
  }

  private function stringContainsTwoNumbers($string) {
    $numOfNumbers = 0;
    $elements = explode('_', $string);
    foreach ($elements as $element) {
      if (is_numeric($element)) {
        $numOfNumbers++;
      }
    }

    if ($numOfNumbers == 2) {
      return TRUE;
    }
    
    return FALSE;
  }

  private function propertiesHaveValues($objectStack) {
    foreach ($objectStack as $object) {
      foreach ($object as $propertyName => $propertyValue) {
        if ($propertyValue == '' && $propertyValue != 0) {
          throw new \Exception('Property with name ' . $propertyName . ' does not contain any value.');
          return FALSE;
        }
      }
    }

    return TRUE;
  }

  public function provideTableData() {
    return [
      [
        'permissions_by_term_user' => [
          [
            'tid' => 2,
            'uid' => 4,
          ],
          [
            'tid' => 1,
            'uid' => 99,
          ],
          [
            'tid' => 32,
            'uid' => 14,
          ],
        ],
        'permissions_by_term_role' => [
          [
            'tid' => 1,
            'rid' => 5,
          ],
          [
            'tid' => 4,
            'rid' => 8,
          ],
          [
            'tid' => 9,
            'rid' => 3,
          ],
        ],
      ],
      [
        'permissions_by_term_user' => [
          [
            'tid' => 7,
            'uid' => 9,
          ],
          [
            'tid' => 3,
            'uid' => 8,
          ],
          [
            'tid' => 12,
            'uid' => 4,
          ],
        ],
        'permissions_by_term_role' => [
          [
            'tid' => 8,
            'rid' => 45,
          ],
          [
            'tid' => 40,
            'rid' => 8,
          ],
          [
            'tid' => 19,
            'rid' => 30,
          ],
        ],
      ],
    ];
  }

}
