<?php

namespace Drupal\simple_percentage_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'simple_percentage_default_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "simple_percentage_default_formatter",
 *   label = @Translation("Simple Percentage"),
 *   field_types = {
 *     "simple_percentage",
 *     "decimal",
 *     "float",
 *     "integer"
 *   }
 * )
 */
class SimplePercentageDefaultFormatter extends FormatterBase {

  /**
   * Constants for numeric value position marking.
   */
  const NUMERIC_VALUE_HIDDEN = 0;
  const NUMERIC_VALUE_ON_LEFT = 1;
  const NUMERIC_VALUE_ON_RIGHT = 2;
  const SIMPLE_PERCENTAGE_MIN = 0;
  const SIMPLE_PERCENTAGE_MAX = 100;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'numeric_percentage_position' => self::NUMERIC_VALUE_HIDDEN,
      'numeric_prefix' => '',
      'numeric_suffix' => '',
      'show_min' => FALSE,
      'show_max' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['numeric_percentage_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Show numeric value'),
      '#default_value' => self::NUMERIC_VALUE_HIDDEN,
      '#value' => $this->getSetting('numeric_percentage_position'),
      '#options' => [
        self::NUMERIC_VALUE_HIDDEN => $this->t('Hidden'),
        self::NUMERIC_VALUE_ON_LEFT => $this->t('Display on the left side'),
        self::NUMERIC_VALUE_ON_RIGHT => $this->t('Display on the right side'),
      ],
    ];
    $elements['numeric_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Numeric prefix'),
      '#description' => $this->t('Prefix for min, max and actual value'),
      '#size' => 6,
      '#maxlength' => 256,
      '#default_value' => '',
      '#value' => $this->getSetting('numeric_prefix'),
    ];
    $elements['numeric_suffix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Numeric suffix'),
      '#description' => $this->t('Suffix for min, max and actual value'),
      '#size' => 6,
      '#maxlength' => 256,
      '#default_value' => '',
      '#value' => $this->getSetting('numeric_suffix'),
    ];
    $elements['show_min'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show minimum value'),
      '#description' => $this->t('Show min value at the beginning of the percentage stripe'),
      '#default_value' => FALSE,
      '#value' => $this->getSetting('show_min'),
    ];
    $elements['show_max'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show maximum value'),
      '#description' => $this->t('Show max value at the beginning of the percentage stripe'),
      '#default_value' => FALSE,
      '#value' => $this->getSetting('show_max'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $numeric_position = $this->getSetting('numeric_percentage_position');
    $numeric_prefix = $this->getSetting('numeric_prefix');
    $numeric_suffix = $this->getSetting('numeric_suffix');
    $show_min = $this->getSetting('show_min');
    $show_max = $this->getSetting('show_max');

    // Display summary for the numeric position value.
    if (empty($numeric_position)) {
      $summary[] = $this->t('Numeric value is hidden');
    }
    else {
      if ($numeric_position == self::NUMERIC_VALUE_ON_LEFT) {
        $summary[] = $this->t('Numeric value position is on the left side');
      }
      else {
        $summary[] = $this->t('Numeric value position is on the right side');
      }

      // Display summary for the numeric prefix value.
      $summary[] = $this->t('Numeric prefix: @numeric_prefix', [
        '@numeric_prefix' => $numeric_prefix,
      ]);
      // Display summary for the numeric suffix value.
      $summary[] = $this->t('Numeric suffix: @numeric_suffix', [
        '@numeric_suffix' => $numeric_suffix,
      ]);
    }

    // Display summary for the min value display.
    if ($show_min) {
      $summary[] = $this->t('Min value is displayed');
    }
    else {
      $summary[] = $this->t('Min value is hidden');
    }

    // Display summary for the max value display.
    if ($show_max) {
      $summary[] = $this->t('Max value is displayed');
    }
    else {
      $summary[] = $this->t('Max value is hidden');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    // Get field settings.
    $field_settings = $this->getFieldSettings();
    $field_type = $items->getFieldDefinition()->getType();
    if ($field_type == 'simple_percentage') {
      $min_value = $field_settings['min_value'] ?: self::SIMPLE_PERCENTAGE_MIN;
      $max_value = $field_settings['max_value'] ?: self::SIMPLE_PERCENTAGE_MAX;
    }
    else {
      $min_value = $field_settings['min'] ?: self::SIMPLE_PERCENTAGE_MIN;
      $max_value = $field_settings['max'] ?: self::SIMPLE_PERCENTAGE_MAX;
    }
    // Get total number of scale items.
    $total_scale = abs($max_value - $min_value);
    // Get value of the scale's one percent.
    $one_percent = $total_scale / 100;

    // Get field formatter settings.
    $settings = $this->getSettings();
    $numeric_position = $settings['numeric_percentage_position'];
    $numeric_prefix = $settings['numeric_prefix'];
    $numeric_suffix = $settings['numeric_suffix'];
    $show_min = $settings['show_min'];
    $show_max = $settings['show_max'];

    foreach ($items as $delta => $item) {
      // Get absolute number in case of negative numbers.
      $percentage_value = abs(($item->value - $min_value) / $one_percent);
      $element[$delta] = [
        '#theme' => 'simple_percentage',
        '#numeric_position' => $numeric_position ?: self::NUMERIC_VALUE_HIDDEN,
        '#numeric_prefix' => $numeric_prefix ?: '',
        '#numeric_suffix' => $numeric_suffix ?: '',
        '#show_min' => $show_min ?: FALSE,
        '#show_max' => $show_max ?: FALSE,
        '#min_value' => !empty($min_value) || $min_value === 0 ? $min_value : NULL,
        '#max_value' => !empty($max_value) || $max_value === 0 ? $max_value : NULL,
        '#value' => $percentage_value,
        '#absolute_value' => $item->value,
        '#attached' => [
          'library' => [
            'simple_percentage_field/default',
          ],
        ],
      ];
    }

    return $element;
  }

}
