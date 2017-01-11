# zend-view PhpRenderer Integration for Expressive

[![Build Status](https://secure.travis-ci.org/zendframework/zend-expressive-zendviewrenderer.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-expressive-zendviewrenderer)

[zend-view PhpRenderer](https://github.com/zendframework/zend-view) integration
for [Expressive](https://github.com/zendframework/zend-expressive).

## Installation

Install this library using composer:

```bash
$ composer require zendframework/zend-expressive-zendviewrenderer
```

We recommend using a dependency injection container, and typehint against
[container-interop](https://github.com/container-interop/container-interop). We
can recommend the following implementations:

- [zend-servicemanager](https://github.com/zendframework/zend-servicemanager):
  `composer require zendframework/zend-servicemanager`
- [pimple-interop](https://github.com/moufmouf/pimple-interop):
  `composer require mouf/pimple-interop`
- [Aura.Di](https://github.com/auraphp/Aura.Di)

## View Helpers

To use view helpers, the `ZendViewRendererFactory`:

- requires a `config` service; with
- a `view_helpers` sub-key; which
- follows standard zend-servicemanager configuration.

To use the `UrlHelper` provided in this package, ensure that you register its
factory in that configuration:

```php
use Zend\Expressive\Helper\UrlHelperFactory;

return [
    'view_helpers' => [
        'factories' => [
            'url' => UrlHelperFactory::class,
        ],
    ],
];
```

## Documentation

See the [zend-expressive](https://github.com/zendframework/zend-expressive/blob/master/doc/book)
documentation tree, or browse online at http://zend-expressive.rtfd.org.
