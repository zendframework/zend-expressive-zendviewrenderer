<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\ZendView;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;

class UrlHelperFactory
{
    /**
     * Create a UrlHelper instance.
     *
     * @param ContainerInterface $container
     * @return UrlHelper
     */
    public function __invoke(ContainerInterface $container)
    {
        return new UrlHelper($container->get(RouterInterface::class));
    }
}
