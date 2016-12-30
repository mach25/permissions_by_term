<?php

namespace Drupal\permissions_by_term;

use Drupal\permissions_by_term\Factory\NodeAccessRecordFactory;
use Drupal\permissions_by_term\AccessStorage;

class NodeAccess {

  /**
   * @var array $uniqueGids
   */
  private $uniqueGids;

  /**
   * @var AccessStorage $accessStorage
   */
  private $accessStorage;

  public function __construct(AccessStorage $accessStorage)
  {
    $this->accessStorage = $accessStorage;
  }

  public function createRealm($uid, $tid) {
    return 'permissions_by_term__uid_' . $uid . '_tid_' . $tid;
  }

  public function createGrants($permissions_by_term_user, $permissions_by_term_role) {
    $nodeAccessRecordFactory = new NodeAccessRecordFactory();

    foreach ($permissions_by_term_user as $data) {
      $realm = $this->createRealm($data['uid'], $data['tid']);

      $grants[] = $nodeAccessRecordFactory->create($data, $realm, $this->createUniqueGid());
    }

    foreach ($permissions_by_term_role as $data) {
      $uids = $this->accessStorage->fetchUidsByRid($data['rid']);
      foreach ($uids as $uid) {
        $realm = $this->createRealm($uid, $data['tid']);
        $grants[] = $nodeAccessRecordFactory->create($data, $realm);
      }
    }

    return $grants;
  }

  public function createUniqueGid() {

  }

}