<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\taxonomy\Tests\TaxonomyTestTrait;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Tests the ERR composite relationship upgrade path.
 *
 * @group permissions_by_term
 */
class SelectTermTest extends KernelTestBase {

  use TaxonomyTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'taxonomy',
    'permissions_by_term',
    'text'
  );

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('taxonomy_term');
  }

  /**
   * Tests if an editor has access to a term on node edit form.
   */
  public function testTermFormAccess() {
    $vocabulary = $this->createVocabulary();

    $values = array(
      'name' => $this->randomMachineName(),
    );

    
    Term::create([
      'name' => 'test',
      'vid' => '1',
      'description' => [
        'value' => $this->randomMachineName(),
        // Use the first available text format.
        'format' => 1,
      ],
    ])->save();

    $term = Term::load(1);

    $debug = true;

    /*
    // Add a paragraph field to the article.
    $field_storage = FieldStorageConfig::create(array(
      'field_name' => 'secured_areas',
      'entity_type' => 'term_relation',
      //'type' => 'entity_reference_revisions',
    ));
    $field_storage->save();
    $field = FieldConfig::create(array(
      'field_storage' => $field_storage,
      'bundle' => 'test_pbt',
    ));
    $field->save();

    // Add a paragraph field to the article.
    $field_storage = FieldStorageConfig::create(array(
      'field_name' => 'node_pbt_field',
      'entity_type' => 'node',
      'type' => 'entity_reference_revisions',
      'cardinality' => '-1',
    ));
    $field_storage->save();
    $field = FieldConfig::create(array(
      'field_storage' => $field_storage,
      'bundle' => 'article',
    ));
    $field->save();
    */
  }

}
