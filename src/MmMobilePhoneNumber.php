<?php

namespace Drupal\mm_mobile_phone;

/**
 * Convertor class.
 */
class MmMobilePhoneNumber {
  public $originalNumber = NULL;

  private $operators = [];
  private $networks = [];
  private $expressions = [];

  /**
   * Assign rules privately.
   * Process the phone number info.
   */
  public function __construct($number) {
    $this->operators['ooredoo'] = "Ooredoo";
    $this->operators['telenor'] = "Telenor";
    $this->operators['mytel'] = "Mytel";
    $this->operators['mpt'] = "MPT";
    $this->operators['unknown'] = "Unknown";

    $this->networks['gsm'] = "GSM";
    $this->networks['wcdma'] = "WCDMA";
    $this->networks['cdma_450'] = "CDMA 450 MHz";
    $this->networks['cdma_800'] = "CDMA 800 MHz";
    $this->networks['unknown'] = "Unknown";

    $this->expressions['networks']['wcdma'] = "/^(09|\+?959)(55\d{5}|25[2-4]\d{6}|26\d{7}|4(4|5|6)\d{7})$/";
    $this->expressions['networks']['cdma_450'] = "/^(09|\+?959)(8\d{6}|6\d{6}|49\d{6})$/";
    $this->expressions['networks']['cdma_800'] = "/^(09|\+?959)(3\d{7}|73\d{6}|91\d{6})$/";

    $this->expressions['formats']['zero_before_areacode'] = "/^\+?9509\d{7,9}$/";
    $this->expressions['formats']['double_country_code'] = "/^\+?95950?9\d{7,9}$/";
    $this->expressions['formats']['country_code'] = "/^\+?950?9\d+$/";
    $this->expressions['formats']['mm_mobilephone'] = "/^(09|\+?950?9|\+?95950?9)\d{7,9}$/";

    $this->expressions['operators']['ooredoo'] = "/^(09|\+?959)9(5|7|6)\d{7}$/";
    $this->expressions['operators']['telenor'] = "/^(09|\+?959)7([5-9])\d{7}$/";
    $this->expressions['operators']['mytel'] = "/^(09|\+?959)6(8|9)\d{7}$/";
    $this->expressions['operators']['mpt'] = "/^(09|\+?959)(5\d{6}|4\d{7,8}|2\d{6,8}|3\d{7,8}|6\d{6}|8\d{6}|7\d{7}|9(0|1|9)\d{5,6}|2[0-4]\d{5}|5[0-6]\d{5}|8[13-7]\d{5}|3[0-369]\d{6}|34\d{7}|4[1379]\d{6}|73\d{6}|91\d{6}|25\d{7}|26[0-5]\d{6}|40[0-4]\d{6}|42\d{7}|45\d{7}|89[6789]\d{6}|)$/";

    $this->original_number = $number;

    // Add processed contents
    $this->process();
  }

  public function checkRegex($patterns=[], $input) {
    $result = TRUE;

    foreach ($patterns as $pattern) {
      $match = preg_match($pattern, $input);

      if (!$match) {
        $result = FALSE;

        break;
      }
    }

    return $result;
  }

  public function isValidMmPhonenumber() {
    if ($this->original_number) {
      $phone = $this->sanitizePhonenumber();
      $regex = [
        $this->expressions['formats']['mm_mobilephone']
      ];

      if ($this->checkRegex($regex, $phone)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  public function sanitizePhonenumber() {
    $phone = $this->original_number;

    $phone = trim($phone);
    $phone = str_replace(' ', '', $phone);
    $phone = str_replace('-', '', $phone);
    $phone = str_replace(',', '', $phone);

    // Process only when country code contains
    if ( $this->checkRegex([ $this->expressions['formats']['country_code'] ], $phone) ) {
      // Try to remove double country code
      if ( $this->checkRegex([ $this->expressions['formats']['double_country_code'] ], $phone) ) {
        $phone = $this->str_replace_once('9595', '95', $phone);
      }

      // Remove 0 before area code
      if ( $this->checkRegex([ $this->expressions['formats']['zero_before_areacode'] ], $phone) ) {
        $phone = $this->str_replace_once('9509', '959', $phone);
      }
    }

    return $phone;
  }

  public function getTelecomName() {
    $operator = $this->operators['unknown'];

    if ($this->isValidMmPhonenumber()) {
      $phone = $this->sanitizePhonenumber();

      foreach ($this->expressions['operators'] as $operator_name => $regex) {
        if ( $this->checkRegex([ $regex ], $phone) ) {
          $operator = $this->operators[$operator_name];

          break;
        }
      }
    }

    return $operator;
  }

  private function str_replace_once($find, $replacement, $string) {
    $occurrence = strpos($string, $find);
    if ($occurrence !== FALSE) {
      $string = substr_replace($string, $replacement, $occurrence, strlen($find));
    }

    return $string;
  }

  public function getPhoneNetworkType() {
    $network = $this->networks['unknown'];

    if ($this->isValidMmPhonenumber()) {
      $phone = $this->sanitizePhonenumber();

      foreach ($this->expressions['networks'] as $network_name => $regex) {
        if ( $this->checkRegex([ $regex ], $phone) ) {
          $network = $this->networks[$network_name];

          break;
        }
      }

      // 'gsm' network if above failed to detect network
      // Because 'gsm' doesn't have detection regex
      if ($network == $this->networks['unknown']) {
        $network = $this->networks['gsm'];
      }
    }

    return $network;
  }

  public function localPhoneNumber() {
    if ($this->isValidMmPhonenumber()) {
      $phone = $this->sanitizePhonenumber();
      $phone = str_replace('+', '', $phone);

      return $this->str_replace_once('959', '09', $phone);
    }
  }

  public function internationalPhoneNumber() {
    $phone = $this->localPhoneNumber();

    return $this->str_replace_once('09', '+959', $phone);
  }

  public function switchNumber($number) {
    $this->original_number = $number;

    // Re-process
    $re_process = $this->process();

    return $re_process;
  }

  /**
   * Construct outputs as public contents.
   */
  public function process() {
    $valid = $this->isValidMmPhonenumber();
    $sanitizePhonenumber = $this->sanitizePhonenumber();
    $network_type = $this->getPhoneNetworkType();
    $telecom_name = $this->getTelecomName();
    $localPhoneNumber = $this->localPhoneNumber();
    $internationalPhoneNumber = $this->internationalPhoneNumber();

    // Define contents
    $this->phonenumber = $valid ? $sanitizePhonenumber : NULL;
    $this->local_phonenumber = $localPhoneNumber;
    $this->international_phonenumber = $internationalPhoneNumber;
    $this->network_type = $network_type;
    $this->valid = $valid;
    $this->telecom_name = $telecom_name;

    return [
      'original_number' => $this->original_number,

      'phonenumber' => $this->phonenumber,
      'local_phonenumber' => $this->local_phonenumber,
      'international_phonenumber' => $this->international_phonenumber,
      'network_type' => $this->network_type,
      'valid' => $this->valid,
      'telecom_name' => $this->telecom_name,
    ];
  }
}
