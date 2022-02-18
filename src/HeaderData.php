<?php

declare(strict_types = 1);

namespace Pantheon\EI;

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
   *
   * @param array $headerData (optional)
   *   The input header data. If not provided, will default to $_SERVER.
   *
   * @see https://www.php.net/manual/en/reserved.variables.server.php
   */
    public function __construct(array $headerData = null) {
        $this->headers = $this->getRequestHeaders($headerData);
    }

  /**
   * Retrieve header data and set in $headers array.
   *
   * @param array $headerData (optional)
   *   The input header data. If not provided, will default to $_SERVER.
   *
   * @see https://www.php.net/manual/en/reserved.variables.server.php
   */
    private function getRequestHeaders(array $headerData = null): array {
        if (is_null($headerData)) {
            $headerData = $_SERVER;
        }
        $headers = [];
        foreach ($headerData as $key => $value) {
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
   * @return array|string
   *   Returns important parts of header string.
   */
    public function parseHeader($key) {
      // Get specified header.
        $header = $this->getHeader($key);

        if (!empty($header)) {
            $parsed_header = [];
            switch ($key) {
                // Parse Audience header.
                case 'Audience':
                case 'Audience-Set':
                  // Separate different pairs in header string.
                    $header_parts = explode('|', $header);

                    foreach ($header_parts as $header_part) {
                        // Skip if empty.
                        if (empty($header_part)) {
                            continue;
                        }

                        // Separate the pair string into key and value.
                        $header_pair = explode(':', $header_part);
                        if (count($header_pair) >= 2) {
                            $parsed_header[$header_pair[0]] = $header_pair[1];
                        } else {
                            // If string isn't formatted as a pair, just set string.
                            $parsed_header[$key][] = $header_part;
                        }
                    }
                    break;

                case 'Interest':
                  // Decode special characters.
                    $header_decoded = urldecode($header);

                  // Split header value into an array.
                    $parsed_header = explode('|', $header_decoded);
                    break;

                // By default, just return header.
                default:
                    $parsed_header = $header;
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

        $header_keys = [
        'Audience',
        'Audience-Set',
        'Interest',
        'Role',
        ];

        foreach ($header_keys as $key) {
          // Get parsed header value.
            $header_parsed = $this->parseHeader($key);

          // Add header value to personalization object.
            if (!empty($header_parsed)) {
                $p_obj[$key] = $header_parsed;
            }
        }

        return $p_obj;
    }

  /**
   * Returns vary header array.
   *
   * @param string|array $key
   *   Key for the header, or array of keys.
   *
   * @return array
   *   Vary header array, based on header data.
   */
    public function returnVaryHeader($key): array {
      // Get current vary data if it exists, otherwise start with empty array.
        $vary_header = $this->getHeader('Vary');
        $vary_header_array = !empty($vary_header) ? explode(', ', $vary_header) : [];

      // If array, merge the arrays.
        if (is_array($key)) {
            $vary_header_array += $key;
        } else {
            // Otherrwise, add header $key to Vary header.
            $vary_header_array[] = $key;
        }

      // Return vary header array structure.
        return ['vary' => $vary_header_array];
    }

    /**
     * Gets the global header data based on the given key.
     *
     * @param string $key
     *   Key for the header.
     * @param array $data (optional)
     *   The header data to parse. Defaults to $_SERVER.
     *
     * Example:
     *     Pantheon\EI\HeaderData::header('Audience');
     *
     * @return string
     *   Returns header value.
     *
     * @see getHeader()
     */
    public static function header($key, array $data = null) {
        return (new HeaderData($data))->getHeader($key);
    }

    /**
     * Parses a global header by key using a specified regex.
     *
     * @param string $key
     *   Key for the header.
     * @param array $data (optional)
     *   The header data to parse. Defaults to $_SERVER.
     *
     * Example:
     *     Pantheon\EI\HeaderData::parse('Audience');
     *
     * @return array|string
     *   Returns important parts of header string.
     *
     * @see parseHeader()
     */
    public static function parse($key, array $data = null) {
        return (new HeaderData($data))->parseHeader($key);
    }

    /**
     * Gets the global personalizaition object.
     *
     * @return array
     *   Returns object with data used for personalization.
     * @param array $data (optional)
     *   The header data to parse. Defaults to $_SERVER.
     *
     * Example:
     *     Pantheon\EI\HeaderData::personalizationObject();
     *
     * @see returnPersonalizedObject()
     */
    public static function personalizationObject(array $data = null) {
        return (new HeaderData($data))->returnPersonalizationObject();
    }

    /**
     * Returns vary header array based on the global data.
     *
     * @param string|array $key
     *   Key for the header, or array of keys.
     * @param array $data (optional)
     *   The header data to parse. Defaults to $_SERVER.
     *
     * Example:
     *     Pantheon\EI\HeaderData::varyHeader('geo');
     *
     * @return array
     *   Vary header array, based on header data.
     *
     * @see returnVaryHeader()
     */
    public static function varyHeader($key, array $data = null): array {
        return (new HeaderData($data))->returnVaryHeader($key);
    }
}
