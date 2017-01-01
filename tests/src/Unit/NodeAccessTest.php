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
   * @param $permissionsByTermUser
   * @param $permissionsByTermRole
   */
  public function testCreateRealms($permissionsByTermUser, $permissionsByTermRole) {
    $accessStorage = $this->createMock('Drupal\permissions_by_term\AccessStorage',
      [
        'fetchUidsByRid' => [999, 87, 44],
        'getNidsByTid' => [64, 826, 91, 21],
        'getAllNids' => [12, 55, 88, 3, 5],
        'getAllUids' => [6, 84, 2, 99, 2]
      ]
    );
    $nodeAccessStorageFactory = new NodeAccessRecordFactory();

    $entityManager = $this->createMock('Drupal\Core\Entity\EntityManager',
      [
        'getStorage' => FALSE,
      ]
    );

    $accessCheck = $this->createMock('Drupal\permissions_by_term\AccessCheck',
      [
        'canUserAccessByNodeId' => TRUE
      ]
    );

    $nodeAccess = new NodeAccess($accessStorage, $nodeAccessStorageFactory, $entityManager, $accessCheck);
    $objectStack = $nodeAccess->createGrants($permissionsByTermUser, $permissionsByTermRole);

    $this->assertTrue($this->propertiesHaveValues($objectStack));
    $this->assertTrue($this->realmContainsNumber($objectStack));
  }

  /**
   * @param array $objectStack
   * @return bool
   * @throws Exception
   */
  private function realmContainsNumber($objectStack) {
    foreach ($objectStack as $object) {
      foreach ($object as $propertyName => $propertyValue) {
        if ($propertyName == 'realm') {
          if ($this->stringContainsOneNumbers($propertyValue) === FALSE) {
            throw new \Exception('The realm does not contain two numbers. It must contain the UID and TID.');
          }
        }
      }
    }

    return TRUE;
  }

  private function stringContainsOneNumbers($string) {
    $numOfNumbers = 0;
    $elements = explode('_', $string);
    foreach ($elements as $element) {
      if (is_numeric($element)) {
        $numOfNumbers++;
      }
    }

    if ($numOfNumbers == 1) {
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
