<?php

namespace Drupal\mm_mobile_phone\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'mm_mobile_phone' field type.
 *
 * @FieldType(
 *   id = "mm_mobile_phone_field_type",
 *   label = @Translation("MM Mobile Phone"),
 *   description = @Translation("Create and store Myanmar mobile phone numbers."),
 *   default_widget = "mm_mobile_phone_widget_default",
 *   default_formatter = "mm_mobile_phone_formatter_text"
 * )
 */
class MmMobilePhoneNumber extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'varchar',
          'not null' => FALSE,
          'length' => 64,
        ],
      ],
      'indexes' => [
        'value' => ['value'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Value'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

}
