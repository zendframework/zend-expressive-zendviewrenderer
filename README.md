# zend-view PhpRenderer Integration for Expressive

> ## Repository abandoned 2019-12-31
>
> This repository has moved to mezzio/mezzio-laminasviewrenderer.

[![Build Status](https://secure.travis-ci.org/zendframework/zend-expressive-zendviewrenderer.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-expressive-zendviewrenderer)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-expressive-zendviewrenderer/badge.svg?branch=master)](https://coveralls.io/github/zendframework/zend-expressive-zendviewrenderer?branch=master)

[zend-view PhpRenderer](https://github.com/zendframework/zend-view) integration
for [Expressive](https://github.com/zendframework/zend-expressive).

## Installation

Install this library using composer:

```bash
$ composer require zendframework/zend-expressive-zendviewrenderer
```

We recommend using [PSR-11](https://www.php-fig.org/psr/psr-11/) compatible
dependency injection container. We can recommend the following implementations:

- [zend-servicemanager](https://github.com/zendframework/zend-servicemanager):
  `composer require zendframework/zend-servicemanager`
- [Pimple](https://github.com/silexphp/Pimple):
  `composer require zendframework/zend-pimple-config`
- [Aura.Di](https://github.com/auraphp/Aura.Di):
  `composer require zendframework/zend-auradi-config`

## View Helpers

To use view helpers, the `ZendViewRendererFactory`:

- requires a `config` service; with
- a `view_helpers` sub-key; which
- follows standard zend-servicemanager configuration.

## Documentation

Browse online at https://docs.zendframework.com/zend-expressive/features/template/zend-view/.