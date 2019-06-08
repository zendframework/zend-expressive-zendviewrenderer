<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\ZendView;

use Psr\Container\ContainerInterface;

final class NamespacedPathStackResolverFactory
{
    public function __invoke(ContainerInterface $container) : NamespacedPathStackResolver
    {
        $config   = $container->has('config') ? $container->get('config') : [];
        $config   = $config['templates'] ?? [];

        $resolver = new NamespacedPathStackResolver();
        if (! empty($config['extension'])) {
            $resolver->setDefaultSuffix($config['extension']);
        }

        return $resolver;
    }
}
