<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\permissions_by_term\Service\AccessCheck;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\permissions_by_term\Service\AccessStorage;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Class AccessCheckTest
 *
 * @package Drupal\Tests\permissions_by_term\Kernel
 */
class AccessCheckTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['taxonomy', 'node', 'user', 'text', 'field', 'system', 'permissions_by_term'];

  /**
   * @var AccessStorage
   */
  protected $accessStorage;

  /**
   * @var AccessCheck
   */
  protected $accessCheck;

  /**
   * @var int
   */
  protected $nidOneGrantedTerm;

  /**
   * @var int
   */
  protected $nidAllGrantedTerms;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installSchema('system', ['key_value_expire', 'sequences']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('taxonomy_term');
    $this->installConfig(['permissions_by_term']);
    $this->installSchema('node', 'node_access');
    $this->installSchema('permissions_by_term', 'permissions_by_term_user');
    $this->installSchema('permissions_by_term', 'permissions_by_term_role');
    $this->accessStorage = $this->container->get('permissions_by_term.access_storage');
    $this->accessCheck = $this->container->get('permissions_by_term.access_check');
    \Drupal::configFactory()->getEditable('taxonomy.settings')->set('maintain_index_table', TRUE)->save();
    $this->createTestVocabulary();
    $this->createPageNodeType();
    $this->createRelationOneGrantedTerm();
    $this->createRelationAllGrantedTerms();
    $this->createCurrentUser();
  }

  /**
   * @return void
   */
  public function testDisabledSingleTermRestriction() {
    \Drupal::configFactory()->getEditable('permissions_by_term.settings.single_term_restriction')->set('value', FALSE)->save();
    $this->assertTrue($this->accessCheck->canUserAccessByNodeId($this->getNidOneGrantedTerm()));

    node_access_rebuild();

    $gids = $this->accessStorage->getGids(\Drupal::service('current_user'));

    $database = $this->container->get('database');
    $nodeAccess = $database->select('node_access', 'na')
      ->fields('na', ['nid'])
      ->condition('na.gid', $gids['permissions_by_term'], 'IN')
      ->condition('na.realm', AccessStorage::NODE_ACCESS_REALM);
    $permittedNids = $nodeAccess
      ->execute()
      ->fetchCol();

    $this->assertCount(1, $permittedNids);
  }

  /**
   * @return void
   */
  public function testEnabledSingleTermRestriction() {
    \Drupal::configFactory()->getEditable('permissions_by_term.settings.single_term_restriction')->set('value', TRUE)->save();
    $this->assertFalse($this->accessCheck->canUserAccessByNodeId($this->getNidOneGrantedTerm()));

    node_access_rebuild();

    $gids = $this->accessStorage->getGids(\Drupal::service('current_user'));

    $database = $this->container->get('database');
    $nodeAccess = $database->select('node_access', 'na')
      ->fields('na', ['nid'])
      ->condition('na.gid', $gids['permissions_by_term'], 'IN')
      ->condition('na.realm', AccessStorage::NODE_ACCESS_REALM);
    $permittedNids = $nodeAccess
      ->execute()
      ->fetchCol();

    $this->assertCount(0, $permittedNids);
  }

  protected function createTestVocabulary() {
    Vocabulary::create([
      'name' => 'test',
      'vid' => 'test',
    ])->save();
  }

  /**
   * @return int
   */
  protected function createRelationOneGrantedTerm() {
    $term = Term::create([
      'name' => 'term1',
      'vid' => 'test',
    ]);
    $term->save();
    $tids[] = $term->id();

    $this->accessStorage->addTermPermissionsByUserIds([\Drupal::service('current_user')->id()], $term->id());

    $term = Term::create([
      'name' => 'term2',
      'vid' => 'test',
    ]);
    $term->save();
    $tids[] = $term->id();

    $this->accessStorage->addTermPermissionsByUserIds([2], $term->id());

    $node = Node::create([
      'type' => 'page',
      'title' => 'test_title',
      'field_tags' => [
        [
          'target_id' => $tids[0]
        ],
        [
          'target_id' => $tids[1]
        ],
      ]
    ]);
    $node->save();
    $this->setNidOneGrantedTerm($node->id());
  }

  protected function createRelationAllGrantedTerms() {
    $term = Term::create([
      'name' => 'term1',
      'vid' => 'test',
    ]);
    $term->save();
    $tids[] = $term->id();

    $this->accessStorage->addTermPermissionsByUserIds([\Drupal::service('current_user')->id()], $term->id());

    $term = Term::create([
      'name' => 'term2',
      'vid' => 'test',
    ]);
    $term->save();
    $tids[] = $term->id();

    $this->accessStorage->addTermPermissionsByUserIds([\Drupal::service('current_user')->id()], $term->id());

    $node = Node::create([
      'type' => 'page',
      'title' => 'test_title',
      'field_tags' => [
        [
          'target_id' => $tids[0]
        ],
        [
          'target_id' => $tids[1]
        ],
      ]
    ]);
    $node->save();
    $this->setNidAllGrantedTerms($node->id());
  }


  protected function getTaxonomyIndex() {
    return \Drupal::database()->select('taxonomy_index', 'ti')
      ->fields('ti', ['tid'])
      ->execute()
      ->fetchCol();
  }

  /**
   * @return int
   */
  public function getNidOneGrantedTerm() {
    return $this->nidOneGrantedTerm;
  }

  /**
   * @param int $nidOneGrantedTerm
   */
  public function setNidOneGrantedTerm($nidOneGrantedTerm) {
    $this->nidOneGrantedTerm = $nidOneGrantedTerm;
  }

  /**
   * @return int
   */
  public function getNidAllGrantedTerms() {
    return $this->nidAllGrantedTerms;
  }

  /**
   * @param int $nidAllGrantedTerms
   */
  public function setNidAllGrantedTerms($nidAllGrantedTerms) {
    $this->nidAllGrantedTerms = $nidAllGrantedTerms;
  }

  protected function createPageNodeType() {
    NodeType::create([
      'type' => 'page',
    ])->save();
    FieldStorageConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_tags',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'taxonomy_term',
      ],
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_tags',
      'entity_type' => 'node',
      'bundle' => 'page',
    ])->save();
  }

  protected function createCurrentUser() {
    $testUser = User::create([
      'uid' => 2,
      'name' => 'foobar',
      'mail' => 'foobar@example.com',
    ]);

    $testUser->save();
    \Drupal::service('current_user')->setAccount($testUser);
  }

}