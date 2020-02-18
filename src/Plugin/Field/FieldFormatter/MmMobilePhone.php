<?php

namespace Drupal\mm_mobile_phone\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\mm_mobile_phone\MmMobilePhoneNumber;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'mm_mobile_phone_formatter_text' formatter.
 *
 * @FieldFormatter(
 *   id = "mm_mobile_phone_formatter_text",
 *   label = @Translation("Mobile Phone"),
 *   field_types = {
 *     "mm_mobile_phone_field_type"
 *   }
 * )
 */
class MmMobilePhone extends FormatterBase {

  /**
   * Returns output styles.
   */
  public function getOutputStyles() {
    return [
      'phonenumber' => 'Normal',
      'network_type' => 'Network Type',
      'telecom_name' => 'Telecom Name',
      'local_phonenumber' => 'Local Phonenumber',
      'international_phonenumber' => 'International Phonenumber',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'tel_protocol' => FALSE,
      'style' => 'phonenumber',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $style = $this->getSetting('style');
    $tel_protocol = $this->getSetting('tel_protocol');

    $summary = [];

    if ($style) {
      $summary[] = $this->t('Style: @style', ['@style' => $style]);
    }

    if ($tel_protocol) {
      $summary[] = $this->t('Tel: Protocol: @tel_protocol', ['@tel_protocol' => $tel_protocol]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['style'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Style'),
      '#options' => $this->getOutputStyles(),
      '#default_value' => $this->getSetting('style'),
    ];

    $elements['tel_protocol'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Tel: Protocol'),
      '#default_value' => $this->getSetting('tel_protocol'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->viewValue($item, $langcode);
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewValue(FieldItemInterface $item, $langcode) {
    $output = [];

    $phone = new MmMobilePhoneNumber($item->value);

    // Get settings.
    $style = $this->getSetting('style');
    $tel_protocol = $this->getSetting('tel_protocol');

    // Format value.
    $formatted_value = $phone->{$style};

    // Set output as markup.
    $output['#markup'] = $formatted_value;

    // Make tel: link.
    if ($tel_protocol) {
      $url = Url::fromUri("tel:" . $phone->international_phonenumber);
      $link_text = $formatted_value;

      // Output with tel: protocol.
      $output = Link::fromTextAndUrl($link_text, $url)->toRenderable();
    }

    return $output;
  }

}
