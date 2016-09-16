<?php

namespace Drupal\Tests\permissions_by_term\Kernel;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\simpletest\WebTestBase;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\field\Entity\FieldConfig;
use Drupal\taxonomy\Entity\Term;

/**
 * Tests the ERR composite relationship upgrade path.
 *
 * @group permissions_by_term
 */
class SelectTermTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('taxonomy', 'text');

  /**
   * List of taxonomy term names by language.
   *
   * @var array
   */
  public $termNames = [];

  /**
   * The vocabulary used for creating terms.
   *
   * @var \Drupal\taxonomy\VocabularyInterface
   */
  protected $vocabulary;

  /**
   * Sets up the test.
   */
  protected function setUp() {
    parent::setUp();

    // Create a vocabulary.
    $this->vocabulary = Vocabulary::create([
      'name' => 'Views testing tags',
      'vid' => 'views_testing_tags',
    ]);
    $this->vocabulary->save();

    // Add a translatable field to the vocabulary.
    $field = FieldStorageConfig::create(array(
      'field_name' => 'field_foo',
      'entity_type' => 'taxonomy_term',
      'type' => 'text',
    ));
    $field->save();
    FieldConfig::create([
      'field_name' => 'field_foo',
      'entity_type' => 'taxonomy_term',
      'label' => 'Foo',
      'bundle' => 'views_testing_tags',
    ])->save();

    $new_term = Term::create([
      'name' => 'blar',
      'vid' => 'views_testing_tags',
      'field_foo' => [
        'value' => 'blub',
        'format' => 'text',
      ],
    ]);
    $new_term->save();
  }

}
