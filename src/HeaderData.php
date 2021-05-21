<?php

declare(strict_types = 1);

namespace Kalamuna\SmartCDN;

/**
 * A class to handle smart content delivery network for users.
 */
class HeaderData {

  /**
   * Header data.
   *
   * @var headers
   */
  private $headers;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->headers = $this->getRequestHeaders();
  }

  /**
   * Get headers.
   */
  private function getRequestHeaders(): array {
    $headers = [];
    foreach ($_SERVER as $key => $value) {
      if (substr($key, 0, 5) <> 'HTTP_') {
        continue;
      }
      $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
      $headers[$header] = $value;
    }

    return $headers;
  }

  /**
   * Gets a header by key.
   *
   * @param string $key
   *   Key for the header.
   */
  public function getHeader($key): string {
    return !empty($this->headers[$key]) ? $this->headers[$key] : NULL;
  }

  /**
   * Parses a header by key using a specified regex.
   *
   * @param string $key
   *   Key for the header.
   * @param string $regex
   *   Regex match string.
   */
  public function parseHeader($key, $regex): array {
    // Get specified header.
    $header = $this->getHeader($key);

    if (!empty($header)) {
      // Parse header using regex.
      $output_array = [];
      preg_match($regex, $header, $output_array);

      // Return regex matches if found.
      return !empty($output_array) && count($output_array) > 1 ? array_slice($output_array, 1) : $header;
    }

    return NULL;
  }

  /**
   * Gets personalizaition object.
   */
  public function returnPersonalizationObject(): array {
    $p_obj = [];

    // Get parsed Interest header.
    $interest_header_parse = $this->parseHeader('Interest', '/geo:(.*)/i');

    // Add location to object.
    if (!empty($interest_header_parse)) {
      $p_obj['location'] = $interest_header_parse[0];
    }

    return $p_obj;
  }

}
