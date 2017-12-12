# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.4.1 - 2017-12-12

### Added

- [#39](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/39)
  adds the ability for the `ZendViewRendererFactory` to use the
  `Zend\View\Renderer\PhpRenderer` service when present, defaulting to creating
  an unconfigured instance if none is available (previous behavior).

### Changed

- [#41](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/41)
  updates the renderer to also inject the layout with any default parameters (vs
  only the template requested).

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#43](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/43)
  ensures that if a view model provided to the renderer contains child view
  models, then it will properly merge variables pulled from the child model.
  Previously, an error would occur due to an attempt to merge either a null or
  an object where it expected an array.

## 1.4.0 - 2017-03-14

### Added

- [#36](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/36)
  adds support for zend-expressive-helpers 4.0.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.3.0 - 2017-03-02

### Added

- [#23](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/23)
  adds the ability to disable layouts either globally or when rendering. Disable
  globally by setting the default `layout` parameter to boolean `false`:

  ```php
  $renderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'layout', false);
  ```

  Or do so when rendering, by passing the template variable `layout` with a
  boolean `false` value:

  ```php
  $renderer->render($templateName, [
      'layout' => false,
      // other template variables
  ]);
  ```

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.1 - 2017-01-12

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#33](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/33)
  fixes the signature of the `UrlHelper` to make the default value of
  `$fragmentIdentifer` a `null` instead of `''`; this fixes an issue whereby
  missing fragments led to exceptions thrown by zend-expressive-helpers.

## 1.2.0 - 2017-01-11

### Added

- [#30](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/30)
  adds support for zend-expressive-router 2.0.

- [#30](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/30)
  adds support for zend-expressive-helpers 2.2 and 3.0.

- [#30](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/30)
  adds new arguments to the `url()` helper:

  ```php
  echo $this->url(
      $routeName,   // (optional) string route for which to generate URI; uses matched when absent
      $routeParams, // (optional) array route parameter substitutions; uses matched when absent
      $queryParams, // (optional) array query string arguments to include
      $fragment,    // (optional) string URI fragment to include
      $options,     // (optional) array of router options. The key `router` can
                    //     contain options to pass to the router; the key
                    //     `reuse_result_params` can be used to disable re-use of
                    //     matched routing parameters.
  );
  ```

  If using zend-expressive-router versions prior to 2.0 and/or
  zend-expressive-helpers versions prior to 3.0, arguments after `$routeParams`
  will be ignored.

### Changed

- [#26](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/26)
  updated the zend-view dependency to 2.8.1+.

### Deprecated

- Nothing.

### Removed

- [#26](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/26)
  removes the dependencies for the zend-i18n and zend-filter packages, as they
  are no longer required by the minimum version of zend-view supported.

  If you depended on features of these, you may need to re-add them to your
  application:

  ```bash
  $ composer require zendframework/zend-filter zendframework/zend-i18n
  ```

- This release removes support for PHP 5.5.

### Fixed

- Nothing.

## 1.1.0 - 2016-03-23

### Added

- [#22](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/22)
  adds support for the zend-eventmanager and zend-servicemanager v3 releases.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.1 - 2016-01-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#19](https://github.com/zendframework/zend-expressive-zendviewrenderer/pull/19)
  updates the zend-expressive-helpers dependency to `^1.1 || ^2.0`, allowing it
  to work with either version.

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
