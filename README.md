# Pantheon Edge Integrations

[![Unsupported](https://img.shields.io/badge/pantheon-unsupported-yellow?logo=pantheon&color=FFDC28&style=for-the-badge)](https://github.com/topics/unsupported?q=org%3Apantheon-systems "Unsupported, e.g. a tool we are actively using internally and are making available, but do not promise to support") ![Build Status](https://github.com/pantheon-systems/pantheon-edge-integrations/actions/workflows/main.yml/badge.svg)

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

## Development

[PHPUnit](https://phpunit.de/) is used to run the [tests](tests).

``` bash
composer install
composer test
```

## Default branch name

The default branch name is `main`. This has changed since the project was created. If your local environment is still using `master` as the default branch name, you may update by running the following commands:

```bash
git branch -m master <BRANCH>
git fetch origin
git branch -u origin/<BRANCH> <BRANCH>
git remote set-head origin -a
```
