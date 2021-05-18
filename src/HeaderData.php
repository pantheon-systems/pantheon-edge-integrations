<?php

declare(strict_types = 1);

namespace Kalamuna\SmartCDN;

/**
 * A class to handle smart content delivery network for users.
 */
class HeaderData {

  /**
   * Returns a greeting statement using the provided name.
   */
  public function data(): array {
    return [
      'greeting' => "Hello world!!",
    ];
  }

}
