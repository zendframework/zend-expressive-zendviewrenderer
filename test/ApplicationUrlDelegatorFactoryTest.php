<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\ZendView;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Expressive\Application;
use Zend\Expressive\ZendView\ApplicationUrlDelegatorFactory;
use Zend\Expressive\ZendView\UrlHelper;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApplicationUrlDelegatorFactoryTest extends TestCase
{
    public function testDelegatorRegistersUrlHelperAsRouteResultObserverWithApplication()
    {
        $urlHelper = $this->prophesize(UrlHelper::class);
        $application = $this->prophesize(Application::class);
        $application->attachRouteResultObserver($urlHelper->reveal())->shouldBeCalled();
        $applicationCallback = function () use ($application) {
            return $application->reveal();
        };

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->has(UrlHelper::class)->willReturn(true);
        $container->get(UrlHelper::class)->willReturn($urlHelper->reveal());

        $delegator = new ApplicationUrlDelegatorFactory();
        $test = $delegator->createDelegatorWithName(
            $container->reveal(),
            Application::class,
            Application::class,
            $applicationCallback
        );
        $this->assertSame($application->reveal(), $test);
    }
}
