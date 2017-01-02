<?php

namespace Drupal\permissions_by_term;

use Drupal\Core\Entity\EntityManager;
use \Drupal\permissions_by_term\Factory\NodeAccessRecordFactory;
use Drupal\permissions_by_term\Model\NodeAccessRecordModel;
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
   * @var User $userEntityStorage
   */
  private $userEntityStorage;

  /**
   * @var Node $node
   */
  private $node;

  /**
   * @var EntityManager $entityManager
   */
  private $entityManager;

  /**
   * @var AccessCheck $accessCheck
   */
  private $accessCheck;

  /**
   * @var NodeAccessRecordModel[] $grants
   */
  private $grants;

  /**
   * @var int $loadedUid
   */
  private $loadedUid;

  /**
   * @var User $userInstance
   */
  private $userInstance;

  public function __construct(AccessStorage $accessStorage, NodeAccessRecordFactory $nodeAccessRecordFactory, EntityManager $entityManager, AccessCheck $accessCheck)
  {
    $this->accessStorage = $accessStorage;
    $this->nodeAccessRecordFactory = $nodeAccessRecordFactory;
    $this->entityManager = $entityManager;
    $this->userEntityStorage = $this->entityManager->getStorage('user');
    $this->node = $this->entityManager->getStorage('node');
    $this->accessCheck = $accessCheck;
  }

  public function createRealm($uid) {
    return 'permissions_by_term__uid_' . $uid;
  }

  public function createGrants() {
    $nids = $this->accessStorage->getAllNids();

    foreach ($nids as $nid) {
      $uids = $this->accessStorage->getAllUids();
      foreach ($uids as $uid) {
        if ($this->accessCheck->canUserAccessByNodeId($nid, $uid)) {
          $realm = $this->createRealm($uid);
          $nodeType = $this->accessStorage->getNodeType($nid);
          $langcode = $this->accessStorage->getLangCode($nid);
          $grants[] = $this->nodeAccessRecordFactory->create($realm, $this->createUniqueGid(), $nid, $langcode, $this->getGrantUpdate($uid, $nodeType, $nid), $this->getGrantDelete($uid, $nodeType, $nid));
        }
      }
    }

    $this->grants = $grants;
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

  public function canUserUpdateNode($uid, $nodeType, $nid)
  {
    $user = $this->getUserInstance($uid);

    $this->setLoadedUid($uid);

    if ($user->hasPermission('edit any ' . $nodeType . ' content')) {
      return TRUE;
    }

    if ($this->isNodeOwner($nid, $uid) && $this->canUpdateOwnNode($uid, $nodeType)) {
      return TRUE;
    }

    return FALSE;
  }

  public function canUserBypassNodeAccess($uid)
  {
    $user = $this->getUserInstance($uid);
    if ($user->hasPermission('bypass node access')) {
      return TRUE;
    }

    return FALSE;
  }

  public function canUserDeleteNode($uid, $nodeType, $nid)
  {
    $user = $this->getUserInstance($uid);
    if ($user->hasPermission('delete any ' . $nodeType . ' content')) {
      return TRUE;
    }

    if ($this->isNodeOwner($nid, $uid) && $this->canDeleteOwnNode($uid, $nodeType)) {
      return TRUE;
    }

    return FALSE;
  }

  private function getGrantDelete($uid, $nodeType, $nid)
  {
    if ($this->canUserBypassNodeAccess($uid)) {
      return 1;
    }

    if ($this->canUserDeleteNode($uid, $nodeType, $nid))
    {
      return 1;
    }

    return 0;
  }

  private function getGrantUpdate($uid, $nodeType, $nid)
  {
    if ($this->canUserBypassNodeAccess($uid)) {
      return 1;
    }

    if ($this->canUserUpdateNode($uid, $nodeType, $nid))
    {
      return 1;
    }

    return 0;
  }

  public function isNodeOwner($nid, $uid) {
    $node = $this->node->load($nid);
    if (intval($node->getOwnerId()) == intval($uid)){
      return TRUE;
    }

    return FALSE;
  }

  private function canUpdateOwnNode($uid, $nodeType) {
    $user = $this->getUserInstance($uid);
    if ($user->hasPermission('edit own ' . $nodeType . ' content')) {
      return 1;
    }

    return 0;
  }

  private function canDeleteOwnNode($uid, $nodeType) {
    $user = $this->getUserInstance($uid);
    if ($user->hasPermission('delete own ' . $nodeType . ' content')) {
      return 1;
    }

    return 0;
  }

  public function getGrantsByNid($nid) {
    $grants = [];
    foreach($this->grants as $grant) {
      if ($grant->nid == $nid) {
        $grants[] = $grant;
      }
    }

    return $grants;
  }

  /**
   * @return int
   */
  public function getLoadedUid()
  {
    return $this->loadedUid;
  }

  /**
   * @param int $loadedUid
   */
  public function setLoadedUid($loadedUid)
  {
    $this->loadedUid = $loadedUid;
  }

  /**
   * @return User
   */
  public function getUserInstance($uid)
  {
    if ($this->getLoadedUid() !== $uid) {
      $user = $this->userEntityStorage->load($uid);
      $this->setUserInstance($user);
      return $user;
    }

    return $this->userInstance;
  }

  /**
   * @param User $userInstance
   */
  public function setUserInstance($userInstance)
  {
    $this->userInstance = $userInstance;
  }

}