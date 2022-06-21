<?php
/**
 * HeaderData Class
 *
 * Handles interactions with headers sent from Fastly to the application.
 *
 * @package Pantheon\EI\HeaderData
 */

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
        $parsed_header = in_array($key, [ 'Interest', 'P13n-Interest' ], true)? [] : '';

        // If the header is empty, bail early.
        if (empty($header)) {
            return $parsed_header;
        }

        // Decode the header.
        $header_decoded = urldecode($header);

        // Backwards compatibility with Audience and Audience-Set.
        if (in_array($key, ['Audience','Audience-Set'], true)) {
            $parsed_header = $this->__deprecatedAudienceHandling($key, $header_decoded);
            return $parsed_header;
        }

        // If the header is an interest, or if the value has multiple entries,
        // allow those entries to be split into an array.
        if (in_array($key, [ 'Interest', 'P13n-Interest' ], true) ||
          stripos($header_decoded, '|')
        ) {
            $parsed_header = explode('|', $header_decoded);
            // Trim white space out of values.
            $parsed_header = array_map('trim', $parsed_header);
        }

        // If the header is not an interest (e.g. Geo or custom), set the value to the decoded header.
        if (empty($parsed_header)) {
            $parsed_header = $header_decoded;
        }

        return $parsed_header;
    }

    /**
     * Handles deprecated Audience and Audience-Set headers.
     * @param string $key The header key. Either 'Audience' or 'Audience-Set'.
     * @param string $header The header value.
     * @return array
     *  Returns an array of audience data.
     * @deprecated
     *  This function is deprecated and will be removed in a future release.
     */
    public function __deprecatedAudienceHandling(string $key, string $header) : array {
        $parsed_header = [];

        // If we're dealing with an Audience header, we need to add it to an array.
        if ($key === 'Audience') {
            $_headers = [$header];
        }

        // Split the header data at the | character.
        $_headers = explode('|', $header);

        // Loop through each header.
        foreach ($_headers as $i => $header_part) {
            // If the header is empty, bail early.
            if (empty($header_part)) {
                continue;
            }

            // Split at the : character.
            $header_pair = explode(':', $header_part);
            // If we actually have a header pair, map the key and value.
            // Otherwise, just return the value for the passed key.
            if (count($header_pair) >= 2) {
                $parsed_header[$header_pair[0]] = $header_pair[1];
            } else {
                $parsed_header[$key] = $header_part;
            }
        }

        return $parsed_header;
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
        'P13n-Geo-Region',
        'P13n-Geo-Country-Code',
        'P13n-Geo-Country-Name',
        'P13n-Geo-Continent-Code',
        'P13n-Geo-City',
        'P13n-Geo-Conn-Type',
        'P13n-Geo-Conn-Speed',
        'P13n-Interest',
        'Audience', // Deprecated.
        'Audience-Set', // Deprecated.
        'Interest', // Deprecated.
        'Role', // Not implemented.
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
