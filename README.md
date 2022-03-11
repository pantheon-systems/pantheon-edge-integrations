# Pantheon Edge Integrations

[![Unsupported](https://img.shields.io/badge/pantheon-unsupported-yellow?logo=pantheon&color=FFDC28)](https://pantheon.io/docs/oss-support-levels#unsupported) ![Packagist Release Version](https://img.shields.io/packagist/v/pantheon-systems/pantheon-edge-integrations)![Build Status](https://github.com/pantheon-systems/pantheon-edge-integrations/actions/workflows/main.yml/badge.svg)

Pantheon Edge Integrations is a PHP library which uses header data to provide a personalization object, to be used for personalizing content for each user.

## Installation

Pantheon Edge Integrations can be installed via Composer from [Packagist](https://packagist.org/packages/pantheon-systems/pantheon-edge-integrations)...

``` sh
composer require pantheon-systems/pantheon-edge-integrations
```

## Usage

To make use of the PHP library, ensure PHP can use the class. After which, it's possible to etiher call the library through instanced methods, or through the global methods.

``` php
use Pantheon\EI\HeaderData;
```

### Instanced Methods

Once the class is available, `headerData` objects can be instantiated to make use of the API and methods can be called on it.

``` php
$headerData = new HeaderData();
```

#### getHeader($key)

Uses header key to return raw header data.

``` php
$headerData->getHeader('Audience');
// => "geo:US"

$headerData->getHeader('Interest');
// => "27"

$headerData->getHeader('Role');
// => "subscriber"
```

#### parseHeader($key)

Uses header key to return parsed header data array.

``` php
$headerData->getHeader('Audience');
// => [geo => US]

$headerData->getHeader('Interest');
// => [0 => 27]

$headerData->getHeader('Role');
// => "subscriber"
```

#### returnPersonalizationObject()

Returns an array with personalization data.

``` php
$headerData->returnPersonalizedObject();
// => [
//        Audience => [ geo => US ]
//        Interest => [ 0 => 27 ]
//        Role => subscriber
//    ]
```

#### returnVaryHeader($key)

Returns vary header array, based on header data.

### Global Methods

There are also static methods defined within the class to help assist in retrieving data without having to instantiate the object yourself.

#### HeaderData::personalizationObject()

Gets the global personalizaition object.

``` php
Pantheon\EI\HeaderData::personalizationObject();
// => [
//        Audience => [ geo => US ]
//        Interest => [ 0 => 27 ]
//        Role => subscriber
//    ]
```

#### HeaderData::parse($key)

Parses a global header by key using a specified regex.

``` php
Pantheon\EI\HeaderData::parse('Audience');
// => [geo => US]
```

#### HeaderData::header($key)

Gets the global header data based on the given key.

``` php
Pantheon\EI\HeaderData::header('Audience');
// => geo:US
```

#### HeaderData::varyHeader()

Returns vary header array based on the global data.

``` php
Pantheon\EI\HeaderData::varyHeader('geo');
```

## Development

[PHPUnit](https://phpunit.de/) is used to run the [tests](tests).

``` bash
composer install
composer test
```
