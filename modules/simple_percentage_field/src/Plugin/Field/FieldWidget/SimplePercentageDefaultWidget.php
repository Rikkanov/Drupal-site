<?php

namespace Drupal\simple_percentage_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'simple_percentage_default_widget' widget.
 *
 * @FieldWidget(
 *   id = "simple_percentage_default_widget",
 *   label = @Translation("Number field"),
 *   field_types = {
 *     "simple_percentage"
 *   }
 * )
 */
class SimplePercentageDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Check if current value is empty.
    $value_is_empty = $this->isEmpty($items[$delta]->value);
    // Get field settings.
    $field_settings = $this->getFieldSettings();

    $element += [
      '#type' => 'number',
      '#default_value' => !$value_is_empty ? $items[$delta]->value : NULL,
      '#min' => $field_settings['min_value'] ?: 0,
      '#max' => $field_settings['max_value'] ?: 100,
    ];

    return ['value' => $element];
  }

  /**
   * Checks if the provided value is empty or not.
   *
   * @param string $value
   *   Value for checking.
   *
   * @return bool
   *   Returns TRUE if the value is empty, otherwise returns FALSE.
   */
  private function isEmpty($value) {
    return $value === NULL || $value === "";
  }

}
