<?php

namespace Drupal\mm_mobile_phone\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'mm_mobile_phone_formatter_text' formatter.
 *
 * @FieldFormatter(
 *   id = "mm_mobile_phone_formatter_text",
 *   label = @Translation("Random text"),
 *   field_types = {
 *     "mm_mobile_phone_field_type"
 *   }
 * )
 */
class MmMobilePhone extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = ['#markup' => $item->value];
    }

    return $element;
  }

}
