<?php

namespace Drupal\simple_percentage_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the Simple Percentage field type.
 *
 * @FieldType(
 *   id = "simple_percentage",
 *   label = @Translation("Simple percentage"),
 *   description = @Translation("This field stores a percentage value in the database."),
 *   category = @Translation("Number"),
 *   default_widget = "simple_percentage_default_widget",
 *   default_formatter = "simple_percentage_default_formatter"
 * )
 */
class SimplePercentageItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'varchar',
          'length' => 256,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Simple percentage field'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === "";
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['value'] = random_int(0, 100);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'min_value' => 0,
      'max_value' => 100,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['min_value'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum value'),
      '#default_value' => $this->getSetting('min_value') ?: 0,
      '#required' => TRUE,
    ];

    $element['max_value'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum value'),
      '#default_value' => $this->getSetting('max_value') ?: 100,
      '#required' => TRUE,
    ];

    return $element;
  }

}
