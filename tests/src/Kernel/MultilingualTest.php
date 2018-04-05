<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Tests\TaxonomyTestTrait;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\permissions_by_term\Service\NodeEntityBundleInfo;


class MultilingualTest extends PBTKernelTestBase {

  use TaxonomyTestTrait;

  /**
   * @var NodeEntityBundleInfo
   */
  private $nodeEntityBundleInfo;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->nodeEntityBundleInfo = $this->container->get('permissions_by_term.node_entity_bundle_info');
  }

  public function testCanAccess() {
    Vocabulary::create([
      'name'     => 'Test Multilingual',
      'vid'      => 'test_multilingual',
      'langcode' => 'de',
    ])->save();

    $term = Term::create([
      'name'     => 'term1',
      'vid'      => 'test',
      'langcode' => 'de',
    ]);
    $term->save();

    $node = Node::create([
      'type' => 'page',
      'title' => 'test_title',
      'field_tags' => [
        [
          'target_id' => $term->id()
        ],
      ]
    ]);
    $node->save();

    $this->accessStorage->addTermPermissionsByUserIds([\Drupal::service('current_user')->id()], $term->id(), 'de');
    $this->assertTrue($this->accessCheck->canUserAccessByNodeId($node->id()));
  }

  public function testCanNotAccess() {
    Vocabulary::create([
      'name'     => 'Test Multilingual',
      'vid'      => 'test_multilingual',
      'langcode' => 'de',
    ])->save();

    $term = Term::create([
      'name'     => 'term1',
      'vid'      => 'test',
      'langcode' => 'de',
    ]);
    $term->save();

    $node = Node::create([
      'type' => 'page',
      'title' => 'test_title',
      'field_tags' => [
        [
          'target_id' => $term->id()
        ],
      ]
    ]);
    $node->save();

    $this->accessStorage->addTermPermissionsByUserIds([\Drupal::service('current_user')->id()], $term->id(), 'en');
    $this->assertFalse($this->accessCheck->canUserAccessByNodeId($node->id()));
  }

}