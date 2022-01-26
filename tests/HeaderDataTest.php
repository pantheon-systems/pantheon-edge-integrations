<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pantheon\EI\HeaderData;

final class HeaderDataTest extends TestCase
{
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

  public function testParseHeader(): void {
    $input = [
      'HTTP_AUDIENCE' => 'Parents|Children||Age:47|Name:RobLoach|Name:StevePersch|Name:AnnaMykhailova',
      'HTTP_USER_AGENT' => 'Should just return the value',
      'IGNORED TEST' => 'Should return an empty array',
      'HTTP_INTEREST' => 'Carl Sagan|Richard Feynman||For Science!|With%20A Percent20',
    ];
    $headerData = new HeaderData($input);

    // When a header doesn't exist.
    $keyNotFound = $headerData->parseHeader('header key not found');
    $this->assertEmpty($keyNotFound, 'Expected to return an empty array');
    $this->assertIsArray($keyNotFound, 'Should be an array');

    // Audience
    $audience = $headerData->parseHeader('Audience');
    $this->assertIsArray($audience['Audience']); // TODO: The Audience is nested. Intended?
    $this->assertEquals($audience['Audience'][1], 'Children');
    $this->assertEquals($audience['Name'], 'AnnaMykhailova'); // Take the last entry.
    $this->assertEquals($audience['Age'], 47);

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

  public function testReturnPersonalizationObject(): void {
    $input = [
      'HTTP_AUDIENCE' => 'geo:US',
      'HTTP_ROLE' => 'Administrator',
      'HTTP_INTEREST' => 'Carl Sagan|Richard Feynman',
      'HTTP_IGNORED' => 'HTTP Ignored Entry',
      'IGNORED_ENTRY' => 'Completely ignored entry'
    ];
    $headerData = new HeaderData($input);
    $result = $headerData->returnPersonalizationObject();

    $this->assertEquals($result['Audience']['geo'], 'US');
    $this->assertEquals($result['Role'], 'Administrator');
    $this->assertNull($result['Ignored']);
  }

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
}

