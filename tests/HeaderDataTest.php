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
    $this->assertEmpty($keyNotFound, 'Expected to return an empty array');
    $this->assertIsArray($keyNotFound, 'Should be an array');

    // Audience
    $audience = $headerData->parseHeader('Audience');
    $this->assertIsArray($audience['Audience']);
    $this->assertEquals($audience['Audience'][1], 'Children');
    $this->assertEquals($audience['Name'], 'AnnaMykhailova'); // Take the last entry.
    $this->assertEquals($audience['Age'], 47);

    // Audience Set
    $audienceSet = $headerData->parseHeader('Audience-Set');
    $this->assertIsArray($audienceSet);
    $this->assertEquals($audienceSet['country'], 'US');
    $this->assertEquals($audienceSet['city'], 'Salt Lake City');
    $this->assertEquals($audienceSet['region'], 'UT');
    $this->assertEquals($audienceSet['continent'], 'NA');
    $this->assertEquals($audienceSet['conn-speed'], 'broadband');
    $this->assertEquals($audienceSet['conn-type'], 'wired');

    // Interest
    $interest = $headerData->parseHeader('Interest');
    $expected = [
      'Carl Sagan',
      'Richard Feynman',
      '',
      'For Science!',
      'With A Percent20'
    ];
    $this->assertEquals($interest, $expected);

    // User Agent
    $this->assertEquals($headerData->parseHeader('User-Agent'), 'Should just return the value');
  }

  /**
   * Tests HeaderData's returnPersonalizationObject() method.
   *
   * @group headerdata
   */
  public function testReturnPersonalizationObject(): void {
    $headerData = new HeaderData($this->p13n_input);
    $result = $headerData->returnPersonalizationObject();

    $this->assertEquals($result['Audience']['geo'], 'US');
    $this->assertEquals($result['Role'], 'Administrator');
    $this->assertArrayNotHasKey('Ignored', $result);
    // Test the first and last things in the Audience Set array. If we have both, we can assume everything in the middle matches as well.
    $this->assertEquals($result['Audience-Set']['country'], 'US');
    $this->assertEquals($result['Audience-Set']['conn-type'], 'wired');
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

    $audience = HeaderData::parse('Audience', $input);
    $audienceSet = HeaderData::parse('Audience-Set', $input);
    $this->assertArrayHasKey('Age', $audience);
    $this->assertEquals(47, $audience['Age']);
    $this->assertArrayHasKey( 'region', $audienceSet );
    $this->assertEquals( 'UT', $audienceSet['region'] );
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
