<?php

use Drupal\Tests\permissions_by_term\Unit\Base;
use Drupal\permissions_by_term\NodeAccess;

/**
 * Class NodeAccess
 */
class NodeAccessTest extends PHPUnit_Framework_TestCase {

  use Base;

  /**
   * @dataProvider provideTableData
   */
  public function testCreateRealms($permissions_by_term_user, $permissions_by_term_role) {
    $accessStorage = $this->createMock('Drupal\permissions_by_term\AccessStorage', ['fetchUidsByRid' => 999]);
    $nodeAccess = new NodeAccess($accessStorage);
    $ret = $nodeAccess->createGrants($permissions_by_term_user, $permissions_by_term_role);

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
