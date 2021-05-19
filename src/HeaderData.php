<?php

declare(strict_types = 1);

namespace Kalamuna\SmartCDN;

// use Symfony\Component\HttpFoundation\Request;

// use Fastly\Fastly;
// use Fastly\Adapter\Guzzle\GuzzleAdapter;

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
   * Returns a greeting statement using the provided name.
   */
  public function data(): array {
    return [
      'greeting' => "Hello world!!",
    ];
  }

  /**
   * Get headers.
   */
  private function getRequestHeaders(): array {
    // $headers = [];
    // foreach ($_SERVER as $key => $value) {
    //   if (substr($key, 0, 5) <> 'HTTP_') {
    //     continue;
    //   }
    //   $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
    //   $headers[$header] = $value;
    // }
    $headers = apache_request_headers();
    // $request = Request::createFromGlobals();

    return $headers;
  }

  /**
   * Gets the header.
   *
   * * @param string $key
   *   Key for the header.
   */
  public function getHeader($key) {
    // $adapter = new GuzzleAdapter($this->serviceID);
    // $fastly = new Fastly($adapter);

    // $request = Request::createFromGlobals();
    // return $request->headers->get($key);

    // return !empty($this->headers[$key]) ? $this->headers[$key] : NULL;
    return $this->headers;
  }

  /**
   * Parses the header.
   */
  public function parseHeader() {
  }

  /**
   * Gets personalizaition object.
   */
  public function returnPersonalizationObject() {
  }

}
