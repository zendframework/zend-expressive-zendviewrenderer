<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2015-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\ZendView;

use Interop\Container\ContainerInterface as InteropContainerInterface;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper as BaseUrlHelper;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;

use function get_class;
use function is_array;
use function is_numeric;
use function sprintf;

/**
 * Create and return a ZendView template instance.
 *
 * Requires the Zend\Expressive\Router\RouterInterface service (for creating
 * the UrlHelper instance).
 *
 * Optionally requires the Zend\View\HelperPluginManager service; if present,
 * will use the service to inject the PhpRenderer instance.
 *
 * Optionally uses the service 'config', which should return an array. This
 * factory consumes the following structure:
 *
 * <code>
 * 'templates' => [
 *     'extension' => 'default template file extension',
 *     'layout' => 'name of layout view to use, if any',
 *     'map'    => [
 *         // template => filename pairs
 *     ],
 *     'paths'  => [
 *         // namespace / path pairs
 *         //
 *         // Numeric namespaces imply the default/main namespace. Paths may be
 *         // strings or arrays of string paths to associate with the namespace.
 *     ],
 * ]
 * </code>
 *
 * Injects the HelperPluginManager used by the PhpRenderer with zend-expressive
 * overrides of the url and serverurl helpers.
 */
class ZendViewRendererFactory
{
    public function __invoke(ContainerInterface $container) : ZendViewRenderer
    {
        $config   = $container->has('config') ? $container->get('config') : [];
        $config   = $config['templates'] ?? [];


        // Configuration
        $resolver = new Resolver\AggregateResolver();
        $resolver->attach(
            new Resolver\TemplateMapResolver($config['map'] ?? []),
            100
        );

        // Create or retrieve the renderer from the container
        $renderer = $container->has(PhpRenderer::class)
            ? $container->get(PhpRenderer::class)
            : new PhpRenderer();
        $renderer->setResolver($resolver);

        // Inject helpers
        $this->injectHelpers($renderer, $container);

        $defaultSuffix = $config['extension'] ?? $config['default_suffix'] ?? null;
        // Inject renderer
        $view = new ZendViewRenderer($renderer, $config['layout'] ?? null, $defaultSuffix);

        // Add template paths
        $allPaths = isset($config['paths']) && is_array($config['paths']) ? $config['paths'] : [];
        foreach ($allPaths as $namespace => $paths) {
            $namespace = is_numeric($namespace) ? null : $namespace;
            foreach ((array) $paths as $path) {
                $view->addPath($path, $namespace);
            }
        }

        return $view;
    }

    /**
     * Inject helpers into the PhpRenderer instance.
     *
     * If a HelperPluginManager instance is present in the container, uses that;
     * otherwise, instantiates one.
     *
     * In each case, injects with the custom url/serverurl implementations.
     *
     * @throws Exception\InvalidContainerException if the $container argument
     *     does not implement InteropContainerInterface.
     * @throws Exception\MissingHelperException
     */
    private function injectHelpers(PhpRenderer $renderer, ContainerInterface $container) : void
    {
        $helpers = $this->retrieveHelperManager($container);
        $helpers->setAlias('url', BaseUrlHelper::class);
        $helpers->setAlias('Url', BaseUrlHelper::class);
        $helpers->setFactory(BaseUrlHelper::class, function () use ($container) {
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
        $helpers->setFactory(BaseServerUrlHelper::class, function () use ($container) {
            if (! $container->has(BaseServerUrlHelper::class)) {
                throw new Exception\MissingHelperException(sprintf(
                    'An instance of %s is required in order to create the "url" view helper; not found',
                    BaseServerUrlHelper::class
                ));
            }
            return new ServerUrlHelper($container->get(BaseServerUrlHelper::class));
        });

        $renderer->setHelperPluginManager($helpers);
    }

    /**
     * @throws Exception\InvalidContainerException if the $container argument
     *     does not implement InteropContainerInterface.
     */
    private function retrieveHelperManager(ContainerInterface $container) : HelperPluginManager
    {
        if ($container->has(HelperPluginManager::class)) {
            return $container->get(HelperPluginManager::class);
        }

        if (! $container instanceof InteropContainerInterface) {
            throw new Exception\InvalidContainerException(sprintf(
                '%s expects a %s instance to its constructor; however, your service'
                . ' container is an instance of %s, which does not implement that'
                . ' interface. Consider switching to zend-servicemanager for your'
                . ' container implementation if you wish to use the zend-view renderer.',
                HelperPluginManager::class,
                InteropContainerInterface::class,
                get_class($container)
            ));
        }

        return new HelperPluginManager($container);
    }
}
