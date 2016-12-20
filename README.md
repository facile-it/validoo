# facile-it/validoo

[![Stable release][Last stable image]][Packagist link] [![Unstable release][Last unstable image]][Packagist link] [![Build status][Master build image]][Master build link] [![Coverage Status][Master coverage image]][Master coverage link] [![Scrutinizer][Master scrutinizer image]][Master scrutinizer link]

## Installation

```sh
composer require facile-it/validoo
```

## Configuration

```php
<?php 
namespace Foo;

use Validoo\Validator; // << Add this

...
```

## Usage

```php
$inputs = [
    "field"  => "alphanumeric string",
    "field2" => "report-2016-12-20.csv", 
];
$rules = [
    "field"  => "required|alpha",
    "field2" => "onlyifset|is_filename",
];

$validator = Validator::validate($inputs, $rules);

echo $validator->isSuccess();  // print true
```

[Last stable image]: https://poser.pugx.org/facile-it/validoo/version.svg
[Last unstable image]: https://poser.pugx.org/facile-it/validoo/v/unstable.svg
[Master build image]: https://travis-ci.org/facile-it/validoo.svg
[Master scrutinizer image]: https://scrutinizer-ci.com/g/facile-it/validoo/badges/quality-score.png?b=master
[Master coverage image]: https://scrutinizer-ci.com/g/facile-it/validoo/badges/coverage.png?b=master

[Packagist link]: https://packagist.org/packages/facile-it/validoo
[Master build link]: https://travis-ci.org/facile-it/validoo
[Master scrutinizer link]: https://scrutinizer-ci.com/g/facile-it/validoo/?branch=master
[Master coverage link]: https://scrutinizer-ci.com/g/facile-it/validoo/?branch=master
