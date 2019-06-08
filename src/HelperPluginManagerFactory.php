<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2015-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\ZendView;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper as BaseUrlHelper;
use Zend\ServiceManager\Config;
use Zend\View\HelperPluginManager;

final class HelperPluginManagerFactory
{
    public function __invoke(ContainerInterface $container) : HelperPluginManager
    {
        $manager = new HelperPluginManager($container);

        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['view_helpers'] ?? [];

        if (! empty($config)) {
            (new Config($config))->configureServiceManager($manager);
        }

        $this->injectHelpers($manager, $container);
        return $manager;
    }

    /**
     * Inject helpers into the HelperPhpRenderer instance.
     *
     * If a HelperPluginManager instance is present in the container, uses that;
     * otherwise, instantiates one.
     *
     * In each case, injects with the custom url/serverurl implementations.
     *
     * @throws Exception\MissingHelperException
     */
    private function injectHelpers(HelperPluginManager $helpers, ContainerInterface $container) : void
    {
        $helpers->setAlias('url', BaseUrlHelper::class);
        $helpers->setAlias('Url', BaseUrlHelper::class);
        $helpers->setFactory(BaseUrlHelper::class, static function () use ($container) {
            if (! $container->has(BaseUrlHelper::class)) {
                throw new Exception\MissingHelperException(sprintf(
                    'An instance of %s is required in order to create the "url" view helper; not found',
                    BaseUrlHelper::class
                ));
            }
            return new UrlHelper($container->get(BaseUrlHelper::class));
        });

        $helpers->setAlias('serverurl', BaseServerUrlHelper::class);
        $helpers->setAlias('serverUrl', BaseServerUrlHelper::class);
        $helpers->setAlias('ServerUrl', BaseServerUrlHelper::class);
        $helpers->setFactory(BaseServerUrlHelper::class, static function () use ($container) {
            if (! $container->has(BaseServerUrlHelper::class)) {
                throw new Exception\MissingHelperException(sprintf(
                    'An instance of %s is required in order to create the "url" view helper; not found',
                    BaseServerUrlHelper::class
                ));
            }
            return new ServerUrlHelper($container->get(BaseServerUrlHelper::class));
        });
    }
}
