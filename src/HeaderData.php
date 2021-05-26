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
   * Retrieve header data and set in $headers array.
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
   *
   * @return string
   *   Returns header value.
   */
  public function getHeader($key): string {
    return !empty($this->headers[$key]) ? $this->headers[$key] : '';
  }

  /**
   * Parses a header by key using a specified regex.
   *
   * @param string $key
   *   Key for the header.
   *
   * @return array
   *   Returns important parts of header string.
   */
  public function parseHeader($key): array {
    // Get specified header.
    $header = $this->getHeader($key);

    if (!empty($header)) {
      switch ($key) {
        // Parse interest header.
        case 'Interest':
          // Regex match.
          $regex = '/geo:(.*)/i';

          // Parse header using regex.
          $output_array = [];
          preg_match($regex, $header, $output_array);

          // Return regex matches if found.
          $parsed_header = !empty($output_array) && count($output_array) > 1 ? array_slice($output_array, 1) : $header;
          break;

        // By default, just return header.
        default:
          $parsed_header = [$header];
          break;
      }

      return $parsed_header;
    }

    return [];
  }

  /**
   * Gets personalizaition object.
   *
   * @return array
   *   Returns object with data used for personalization.
   */
  public function returnPersonalizationObject(): array {
    $p_obj = [];

    // Get parsed Interest header.
    $interest_header_parsed = $this->parseHeader('Interest');

    // Add location to object.
    if (!empty($interest_header_parsed)) {
      $p_obj['location'] = $interest_header_parsed[0];
    }

    return $p_obj;
  }

}
