<?php
namespace Drupal\mm_mobile_phone\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mm_mobile_phone\MmMobilePhoneNumber;

/**
 * Plugin implementation of the 'mm_mobile_phone_widget_default' widget.
 *
 * @FieldWidget(
 *   id = "mm_mobile_phone_widget_default",
 *   module = "mm_mobile_phone",
 *   label = @Translation("Mobile Phone"),
 *   field_types = {
 *     "mm_mobile_phone_field_type"
 *   }
 * )
 */
class MmMobilePhone extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '';

    $element += [
      '#type' => 'textfield',
      '#default_value' => $value,
      '#size' => 64,
      '#element_validate' => [
        [static::class, 'validate'],
      ],
    ];

    return ['value' => $element];
  }

  /**
   * Validate the color text field.
   */
  public static function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];

    if (!empty($value)) {
      $phone = new MmMobilePhoneNumber($value);

      if (!$phone->valid) {
        $form_state->setError($element, t("Must be a valid Myanmar mobile number."));
      }
      else {
        $form_state->setValueForElement($element, $phone->phonenumber);
      }
    }
  }

}
