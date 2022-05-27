<?php

/**
 * @file PHPUnit tests for Pantheon Edge Integrations' HeaderData class.
 *
 * @see Pantheon\EI\HeaderData
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pantheon\EI\HeaderData;

/**
 * Tests all the public functions within the HeaderData class.
 *
 * @see Pantheon\EI\HeaderData
 */
final class HeaderDataTest extends TestCase
{

  private $p13n_input = [
    'HTTP_P13N_GEO_COUNTRY_CODE' => 'US',
    'HTTP_P13N_GEO_COUNTRY_NAME' => 'united states',
    'HTTP_P13N_GEO_CITY' => 'salt lake city',
    'HTTP_P13N_GEO_REGION' => 'UT',
    'HTTP_P13N_GEO_CONTINENT_CODE' => 'NA',
    'HTTP_P13N_GEO_CONN_TYPE' => 'wifi',
    'HTTP_P13N_GEO_CONN_SPEED' => 'broadband',
    'HTTP_P13N_Interest' => 'Marie Curie|Jane Goodall|Edith Clark||For Science!|With A Percent%20',
    'HTTP_USER_AGENT' => 'Should just return the value',
    'HTTP_ROLE' => 'Administrator',
    'HTTP_INTEREST' => 'Carl Sagan|Richard Feynman||For Science!|With A Percent%20',
    'HTTP_AUDIENCE' => 'geo:us',
    'HTTP_AUDIENCE_SET' => 'country:us|city:salt lake city|region:UT|continent:NA|conn_type:wifi|conn_speed:broadband',
    'HTTP_IGNORED' => 'HTTP Ignored Entry',
    'IGNORED_ENTRY' => 'Completely ignored entry'
  ];

  /**
   * Tests the HeaderData constructor.
   *
   * @see Pantheon\EI\HeaderData::HeaderData()
   *
   * @group headerdata
   */
  public function testConstructor(): void {
    // Empty Constructor.
    $headerData = new HeaderData();
    $this->assertIsObject($headerData);

    // Empty header data.
    $headerData = new HeaderData([]);
    $this->assertIsObject($headerData);

    // Constructor with a small amount of array data.
    $inputArray = [
        'testConstructor' => 'constructor'
    ];
    $headerData = new HeaderData($inputArray);
    $this->assertIsObject($headerData);
  }

  /**
   * Tests HeaderData's getHeader() method.
   *
   * @group headerdata
   */
  public function testGetHeader(): void {
    $input = [
      'HTTP_SHOULD_BE_FOUND' => 'Should be found',
      'SERVER_NAME' => 'Entries without http_ should be ignored',
      'HTTP_USER_AGENT' => 'Should become User_Agent'
    ];
    $headerData = new HeaderData($input);

    $this->assertEquals($headerData->getHeader('Should-Be-Found'), 'Should be found');
    $this->assertEmpty($headerData->getHeader('Server-Name'), 'SERVER_NAME should be ignored');
    $this->assertEquals($headerData->getHeader('User-Agent'), 'Should become User_Agent');
  }

  /**
   * Tests HeaderData's parseHeader() method, which has a focus on HTTP_AUDIENCE and HTTP_INTEREST.
   *
   * @group headerdata
   */
  public function testParseHeader(): void {
    $headerData = new HeaderData($this->p13n_input);

    // When a header doesn't exist.
    $keyNotFound = $headerData->parseHeader('header key not found');
    $this->assertEmpty($keyNotFound, 'Expected to return an empty string');
    $this->assertIsString($keyNotFound, 'Should be a string');

    // Geolocation.
    $country_code = $headerData->parseHeader('P13n-Geo-Country-Code');
    $this->assertIsString($country_code, 'Should be a string');
    $this->assertEquals($country_code, 'US');
    $country_name = $headerData->parseHeader('P13n-Geo-Country-Name');
    $this->assertIsString($country_name, 'Should be a string');
    $this->assertEquals($country_name, 'united states');
    $city = $headerData->parseHeader('P13n-Geo-City');
    $this->assertIsString($city, 'Should be a string');
    $this->assertEquals($city, 'salt lake city');
    $region = $headerData->parseHeader('P13n-Geo-Region');
    $this->assertIsString($region, 'Should be a string');
    $this->assertEquals($region, 'UT');
    $continent_code = $headerData->parseHeader('P13n-Geo-Continent-Code');
    $this->assertIsString($continent_code, 'Should be a string');
    $this->assertEquals($continent_code, 'NA');
    $conn_type = $headerData->parseHeader('P13n-Geo-Conn-Type');
    $this->assertIsString($conn_type, 'Should be a string');
    $this->assertEquals($conn_type, 'wifi');
    $conn_speed = $headerData->parseHeader('P13n-Geo-Conn-Speed');
    $this->assertIsString($conn_speed, 'Should be a string');
    $this->assertEquals($conn_speed, 'broadband');

    // Interest
    $interest = $headerData->parseHeader('Interest');
    $expected = [
      'Carl Sagan',
      'Richard Feynman',
      '',
      'For Science!',
      'With A Percent'
    ];
    $this->assertEquals($interest, $expected);
    $p13n_interest = $headerData->parseHeader('P13n-Interest');
    $this->assertEquals($p13n_interest, [
      'Marie Curie',
      'Jane Goodall',
      'Edith Clark',
      '',
      'For Science!',
      'With A Percent'
    ]);

    // User Agent
    $this->assertEquals($headerData->parseHeader('User-Agent'), 'Should just return the value');

    // Backcompat.
    $audience = $headerData->parseHeader('Audience');
    $this->assertEquals($audience['geo'], 'us');

    $audience_set = $headerData->parseHeader('Audience-Set');
    $this->assertEquals($audience_set['country'], 'us');
    $this->assertEquals($audience_set['city'], 'salt lake city');
    $this->assertEquals($audience_set['region'], 'UT');
    $this->assertEquals($audience_set['continent'], 'NA');
    $this->assertEquals($audience_set['conn_type'], 'wifi');
    $this->assertEquals($audience_set['conn_speed'], 'broadband');
  }

  /**
   * Tests HeaderData's returnPersonalizationObject() method.
   *
   * @group headerdata
   */
  public function testReturnPersonalizationObject(): void {
    $headerData = new HeaderData($this->p13n_input);
    $result = $headerData->returnPersonalizationObject();
    $this->assertEquals($result['P13n-Geo-Country-Code'], 'US');
    $this->assertEquals($result['P13n-Geo-Country-Name'], 'united states');
    $this->assertEquals($result['P13n-Geo-City'], 'salt lake city');
    $this->assertEquals($result['P13n-Geo-Region'], 'UT');
    $this->assertEquals($result['P13n-Geo-Continent-Code'], 'NA');
    $this->assertEquals($result['P13n-Geo-Conn-Type'], 'wifi');
    $this->assertEquals($result['P13n-Geo-Conn-Speed'], 'broadband');
    $this->assertIsArray($result['P13n-Interest']);
    $this->assertContains('Edith Clark',$result['P13n-Interest']);
    $this->assertEquals($result['Role'], 'Administrator');
    $this->assertArrayNotHasKey('Ignored', $result);
  }

  /**
   * Tests HeaderData's returnVaryHeader() method.
   *
   * @group headerdata
   */
  public function testReturnVaryHeader(): void {
    // Without a Vary Header
    $headerData = new HeaderData([
      'HTTP_IGNORED' => 'Nothing'
    ]);
    $result = $headerData->returnVaryHeader('Vary Header');
    $this->assertIsArray($result['vary']);
    $this->assertArrayHasKey(0, $result['vary']);
    $this->assertEquals($result['vary'][0], 'Vary Header');

    // Valid VARY input
    $headerData = new HeaderData([
      'HTTP_VARY' => 'Something, Wicked, This, Way'
    ]);

    $result = $headerData->returnVaryHeader('Comes');
    $this->assertEquals($result['vary'], ['Something', 'Wicked', 'This', 'Way', 'Comes']);

    $result = $headerData->returnVaryHeader(['Author' => 'Ray Bradbury']);
    $this->assertEquals($result['vary']['Author'], 'Ray Bradbury');
  }

  /**
   * Tests the global HeaderData::header() function.
   *
   * @see Pantheon\EI\HeaderData::header()
   *
   * @group headerdata
   */
  public function testGlobalHeader() {
    // Initialize both the global and an instance as the same input.
    $input = [
      'IGNORED_ENTRY' => 'Completely ignored entry',
      'HTTP_SHOULD_BE_FOUND' => 'Should be found',
    ];

    $result = HeaderData::header('Should-Be-Found', $input);
    $this->assertIsString($result, 'HeaderData::header() should return a string');
    $this->assertEquals('Should be found', $result);
  }

  /**
   * Tests the global HeaderData::parse() function.
   *
   * @see Pantheon\EI\HeaderData::parse()
   *
   * @group headerdata
   */
  public function testGlobalParse() {
    // Initialize both the global and an instance as the same input.
    $input = $this->p13n_input;

    $country_code = HeaderData::parse('P13n-Geo-Country-Code', $input);
    $city = HeaderData::parse('P13n-Geo-City', $input);
    $region = HeaderData::parse('P13n-Geo-Region', $input);
    $interest = HeaderData::parse('P13n-Interest', $input);
    $this->assertEquals('US', $country_code);
    $this->assertEquals('salt lake city', $city);
    $this->assertEquals( 'UT', $region );
    $this->assertContains( 'Jane Goodall', $interest );
  }

  /**
   * Tests the global HeaderData::personalizationObject() function.
   *
   * @see Pantheon\EI\HeaderData::personalizationObject()
   *
   * @group headerdata
   */
  public function testGlobalPersonalizationObject() {
    $input = [
      'HTTP_ROLE' => 'Administrator',
    ];

    $personalizationObject = HeaderData::personalizationObject($input);
    $this->assertArrayHasKey('Role', $personalizationObject);
    $this->assertEquals('Administrator', $personalizationObject['Role']);
  }

  /**
   * Tests the global HeaderData::varyHeader() function.
   *
   * @see Pantheon\EI\HeaderData::varyHeader()
   *
   * @group headerdata
   */
  public function testGlobalVaryHeader(): void {
    // Initialize both the global and an instance as the same input.
    $input = [
      'HTTP_IGNORED' => 'HTTP Ignored Entry',
      'IGNORED_ENTRY' => 'Completely ignored entry',
      'HTTP_SHOULD_BE_FOUND' => 'Should be found',
      'HTTP_VARY' => 'Something, Wicked, This, Way',
    ];

    $varyHeader = HeaderData::varyHeader('Comes', $input);
    $this->assertArrayHasKey('vary', $varyHeader);
    $this->assertEquals($varyHeader['vary'], ['Something', 'Wicked', 'This', 'Way', 'Comes']);
  }
}
