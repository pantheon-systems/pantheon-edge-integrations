# Pantheon Edge Integrations

[![Unsupported](https://img.shields.io/badge/pantheon-unsupported-yellow?logo=pantheon&color=FFDC28&style=for-the-badge)](https://github.com/topics/unsupported?q=org%3Apantheon-systems "Unsupported, e.g. a tool we are actively using internally and are making available, but do not promise to support")

Pantheon Edge Integrations uses header data to provide a personalization object, to be used for personalizing content for each user.

## Methods
### getHeader(key)
Uses header key to return raw header data.

### parseHeader(key)
Uses header key to return parsed header data array.

### returnPersonalizationObject()
Returns an array with personalization data.

### returnVaryHeader($key)
Returns vary header array, based on header data.

## Global Methods

There are a set of global functions that mirror the object methods, allowing using the API through a singleton global instance rather than having to instantiate it locally. These functions are as follows...

``` php
HeaderData::header($key, array $data = null);
HeaderData::parse($key, array $data = null);
HeaderData::personalizationObject(array $data = null);
HeaderData::varyHeader($key, array $data = null);
```

## Development

[PHPUnit](https://phpunit.de/) is used to run the [tests](tests).

``` bash
composer install
composer test
```
