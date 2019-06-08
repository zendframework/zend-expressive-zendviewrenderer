<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\ZendView;

use Psr\Container\ContainerInterface;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;

final class PhpRendererFactory
{
    public function __invoke(ContainerInterface $container) : PhpRenderer
    {
        $config   = $container->has('config') ? $container->get('config') : [];

        $resolver = new AggregateResolver();
        $resolver->attach(
            new TemplateMapResolver($config['templates']['map'] ?? []),
            100
        );

        $nsPathResolver = $container->get(NamespacedPathStackResolver::class);
        $resolver->attach(
            $nsPathResolver,
            0
        );

        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);

        $helpers = $container->get(HelperPluginManager::class);
        $renderer->setHelperPluginManager($helpers);

        return $renderer;
    }
}
