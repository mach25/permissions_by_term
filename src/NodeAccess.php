<?php

namespace Drupal\permissions_by_term;

use \Drupal\node\NodeAccessControlHandler;
use \Drupal\permissions_by_term\Factory\NodeAccessRecordFactory;
use \Drupal\permissions_by_term\AccessStorage;
use \Drupal\user\Entity\User;
use \Drupal\node\Entity\Node;

class NodeAccess {

  /**
   * @var array $uniqueGid
   */
  private $uniqueGid = 0;

  /**
   * @var AccessStorage $accessStorage
   */
  private $accessStorage;

  /**
   * @var User $user
   */
  private $user;

  /**
   * @var Node $node
   */
  private $node;

  public function __construct(AccessStorage $accessStorage, NodeAccessRecordFactory $nodeAccessRecordFactory, User $user, Node $node)
  {
    $this->accessStorage = $accessStorage;
    $this->nodeAccessRecordFactory = $nodeAccessRecordFactory;
    $this->user = $user;
    $this->node = $node;
  }

  public function createRealm($uid, $tid) {
    return 'permissions_by_term__uid_' . $uid . '_tid_' . $tid;
  }

  public function createGrants($permissions_by_term_user, $permissions_by_term_role) {
    foreach ($permissions_by_term_user as $data) {
      $nids = $this->accessStorage->getNidsByTid($data['tid']);
      foreach ($nids as $nid) {
        $realm = $this->createRealm($data['uid'], $data['tid']);
        $grants[] = $this->nodeAccessRecordFactory->create($realm, $nid, $this->createUniqueGid());
      }
    }

    foreach ($permissions_by_term_role as $data) {
      $uids = $this->accessStorage->fetchUidsByRid($data['rid']);
      $nids = $this->accessStorage->getNidsByTid($data['tid']);
      foreach ($nids as $nid) {
        foreach ($uids as $uid) {
          $realm = $this->createRealm($uid, $data['tid']);
          $grants[] = $this->nodeAccessRecordFactory->create($realm, $nid, $this->createUniqueGid());
        }
      }
    }

    return $grants;
  }

  public function createUniqueGid() {
    $uniqueGid = $this->getUniqueGid();
    $uniqueGid++;
    $this->setUniqueGid($uniqueGid);
    return $this->getUniqueGid();
  }

  /**
   * @return array
   */
  public function getUniqueGid()
  {
    return $this->uniqueGid;
  }

  /**
   * @param array $uniqueGid
   */
  public function setUniqueGid($uniqueGid)
  {
    $this->uniqueGid = $uniqueGid;
  }

  public function canUserUpdateNode($uid, $nodeType)
  {
    $user = $this->user->load($uid);
    if ($user->hasPermission('edit any ' . $nodeType . ' content')) {
      return TRUE;
    }

    return FALSE;
  }

  public function canUserBypassNodeAccess($uid)
  {
    $user = $this->user->load($uid);
    if ($user->hasPermission('bypass node access')) {
      return TRUE;
    }

    return FALSE;
  }

  public function canUserDeleteNode($uid, $nodeType)
  {
    $user = $this->user->load($uid);
    if ($user->hasPermission('delete any ' . $nodeType . ' content')) {
      return TRUE;
    }

    return FALSE;
  }

  public function getLangcodeForNode($nid)
  {
    $node = $this->node::load($nid);
    xdebug_break();
  }

}