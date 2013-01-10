# With

This function simply mocks Python's [with statement](http://docs.python.org/release/2.5.3/ref/with.html) for PHP. 
There is probably very little use-case for this but I thought I throw it out there.

[![Build Status](https://secure.travis-ci.org/tyler-king/with.png?branch=master)](http://travis-ci.org/tyler-king/with)

## Fetch

The recommended way to install is [through composer](http://packagist.org).

Just create a composer.json file for your project:

```JSON
{
    "require": {
        "tyler-king/with": "dev-master"
    }
}
```

And run these two commands to install it:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar install

## Requirements

- [PHP](http://php.net) 5.4.x

## Usage

See `examples/` for some basic usage code.