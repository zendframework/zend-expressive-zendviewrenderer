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
use Zend\Expressive\Helper\ServerUrlHelper as BaseServerUrlHelper;

class ServerUrlHelperFactory
{
    public function __invoke(ContainerInterface $container)
    {
        // test if we are using Zend\ServiceManager v2 or v3
        if (! method_exists($container, 'configure')) {
            $container = $container->getServiceLocator();
        }

        if (!$container->has(BaseServerUrlHelper::class)) {
            throw new Exception\MissingHelperException(
                sprintf(
                    'An instance of %s is required in order to create the "url" view helper; not found',
                    BaseServerUrlHelper::class
                )
            );
        }
        return new ServerUrlHelper($container->get(BaseServerUrlHelper::class));
    }
}