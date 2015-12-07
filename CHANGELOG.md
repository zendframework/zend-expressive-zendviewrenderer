# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.1.0 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.1 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.0 - 2015-12-07

First stable release.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.4.1 - 2015-12-06

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#14](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/14)
  updates the zend-expressive-helpers dependency to `^1.1`, allowing removal of
  the zend-expressive development dependency.

## 0.4.0 - 2015-12-04

### Added

- [#11](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/11)
  adds a factory for providing the `HelperPluginManager`, and support in the
  `ZendViewRendererFactory` for injecting the `HelperPluginManager` service
  (using its FQCN) instead of instantiating one directly. 
- [#13](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/13)
  adds `zendframework/zend-expressive-helpers` as a dependency, in order to
  consume its `UrlHelper` and `ServerUrlHelper` implementations.

### Deprecated

- Nothing.

### Removed

- [#13](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/13)
  removes the `UrlHelperFactory`.
- [#13](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/13)
  removes the `Zend\Expressive\ZendView\ApplicationUrlDelegatorFactory`. This
  functionality is obsolete due to the changes made to the `UrlHelper` in this
  release.

### Fixed

- [#13](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/13)
  updates the `UrlHelper` to be a proxy to `Zend\Expressive\Helper\UrlHelper`.
- [#13](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/13)
  updates the `ServerUrlHelper` to be a proxy to `Zend\Expressive\Helper\ServerUrlHelper`.
- [#13](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/13)
  modifies the logic for injecting the `url` and `serverurl` helpers to pull the
  `Zend\Expressive\Helper\UrlHelper` and `Zend\Expressive\Helper\ServerUrlHelper`
  instances, respectively, to inject into the package's own `UrlHelper` and
  `ServerUrlHelper` instances.

## 0.3.1 - 2015-12-03

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#12](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/12)
  updates the `UrlHelper` to implement the `Zend\Expressive\RouteResultObserverInterface`
  from the zendframework/zend-expressive package, instead of
  `Zend\Expressive\Router\RouteResultObserverInterface` from the
  zendframework/zend-expressive-router package (as it is now
  [deprecated](https://github.com/zendframework/zend-expressive-router/pull/3).

## 0.3.0 - 2015-12-02

### Added

- [#4](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/4)
  Allow rendering view models via render
- [#9](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/9)
  updates `UrlHelper` to implement `Zend\Expressive\Template\RouteResultObserverInterface`,
  and the `update()` method it defines. This allows it to observer the
  application for the `RouteResult` and store it for later URI generation.
  To accomplish this, the following additional changes were made:
  - `Zend\Expressive\ZendView\UrlHelperFactory`  was added, for creating the
    `UrlHelper` instance. This should be registered with the application service
    container.
  - `Zend\Expressive\ZendView\ZendViewRendererFactory` was updated to look for
    the `Zend\Expressive\ZendView\UrlHelper` service in the application service
    container, and use it to seed the `HelperManager` when available.
  - `Zend\Expressive\ZendView\ApplicationUrlDelegatorFactory` was created; when
    registered as a delegator factory with the `Zend\Expressive\Application`
    service, it will pull the `UrlHelper` and attach it as a route result
    observer to the `Application` instance. Documentation was also provided for
    creating a Pimple extension for accomplishing this.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#6](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/6)
  Merge route result params with those provided
- [#10](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/10)
  updates the code to depend on [zendframework/zend-expressive-template](https://github.com/zendframework/zend-expressive-template)
  and [zendframework/zend-expressive-router](https://github.com/zendframework/zend-expressive-router)
  instead of zendframework/zend-expressive.

## 0.2.0 - 2015-10-20

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Update to zend-expressive RC1.
- Added branch alias of dev-master to 1.0-dev.

## 0.1.2 - 2015-10-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#1](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/1)
  adds a dependency on zendframework/zend-i18n, as it's required for use of the
  PhpRenderer.

## 0.1.1 - 2015-10-10

Initial release.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Updated to zend-expressive `^0.5`

## 0.1.0 - 2015-10-10

Initial release.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
