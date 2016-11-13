# PromiseFactory

This library tries to create a simple promise factory standard while waiting for a psr.
It Comes out of the box with factory for [ReactPhp/Promise](https://github.com/reactphp/promise) and [Guzzle/Promises](https://github.com/guzzle/promises).

[![Build Status](https://travis-ci.org/mcg-web/promise-factory.svg?branch=master)](https://travis-ci.org/mcg-web/promise-factory)
[![Coverage Status](https://coveralls.io/repos/github/mcg-web/promise-factory/badge.svg?branch=master)](https://coveralls.io/github/mcg-web/promise-factory?branch=master)
[![Latest Stable Version](https://poser.pugx.org/mcg-web/promise-factory/version)](https://packagist.org/packages/mcg-web/promise-factory)
[![License](https://poser.pugx.org/mcg-web/promise-factory/license)](https://packagist.org/packages/mcg-web/promise-factory)

## Getting Started

First, install PromiseFactory using composer.

```sh
composer require "mcg-web/promise-factory"
```

Optional to use Guzzle:

```sh
composer require "guzzlehttp/promises"
```

Optional to use ReactPhp:

```sh
composer require "react/promise"
```

## Supported Factory

*Guzzle*: `McGWeb\PromiseFactory\Factory\GuzzleHttpPromiseFactory`

*ReactPhp*: `McGWeb\PromiseFactory\Factory\ReactPromiseFactory`

To use a custom Promise lib you can implement `McGWeb\PromiseFactory\PromiseFactoryInterface`

## License

McGWeb/PromiseFactory is released under the [MIT](https://github.com/mcg-web/promise-factory/blob/master/LICENSE) license.
