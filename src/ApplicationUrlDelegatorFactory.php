<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\ZendView;

use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApplicationUrlDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * Inject the UrlHelper in an Application instance, when available.
     *
     * @param ServiceLocatorInterface $container
     * @param string $name
     * @param string $requestedName
     * @param callable $callback Callback that returns the Application instance
     */
    public function createDelegatorWithName(ServiceLocatorInterface $container, $name, $requestedName, $callback)
    {
        $application = $callback();
        if ($container->has(UrlHelper::class)) {
            $application->attachRouteResultObserver($container->get(UrlHelper::class));
        }
        return $application;
    }
}
