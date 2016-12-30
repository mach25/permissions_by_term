<?php

namespace Drupal\permissions_by_term\Factory;

use Drupal\permissions_by_term\Model\NodeAccessRecordModel;

class NodeAccessRecordFactory {

  public function create($dataset, $realm, $langcode) {
    $nodeAccessRecord = new NodeAccessRecordModel();
    $nodeAccessRecord->setFallback(1);
    $nodeAccessRecord->setGid($dataset['gid']);
    $nodeAccessRecord->setGrantDelete($dataset['grant_delete']);
    $nodeAccessRecord->setGrantUpdate($dataset['grant_update']);
    $nodeAccessRecord->setGrantView($dataset['grant_view']);
    $nodeAccessRecord->setLangcode('');
    $nodeAccessRecord->setRealm($realm);
  }

}