<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2015-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types = 1);

namespace ZendTest\Expressive\ZendView;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase as TestCase;
use Zend\Expressive\ZendView\HelperPluginManagerFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager;
use ZendTest\Expressive\ZendView\TestAsset\TestHelper;

class HelperPluginManagerFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface
    */
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ServiceManager::class);
    }

    public function testCallingFactoryWithNoConfigReturnsHelperPluginManagerInstance()
    {
        $this->container->has('config')->willReturn(false);
        $factory = new HelperPluginManagerFactory();
        $manager = $factory($this->container->reveal());
        $this->assertInstanceOf(HelperPluginManager::class, $manager);
        return $manager;
    }

    public function testCallingFactoryWithNoViewHelperConfigReturnsHelperPluginManagerInstance()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $factory = new HelperPluginManagerFactory();
        $manager = $factory($this->container->reveal());
        $this->assertInstanceOf(HelperPluginManager::class, $manager);
        return $manager;
    }

    public function testCallingFactoryWithConfigAllowsAddingHelpers()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(
            [
                'view_helpers' => [
                    'invokables' => [
                        'testHelper' => TestHelper::class,
                    ],
                ],
            ]
        );
        $factory = new HelperPluginManagerFactory();
        $manager = $factory($this->container->reveal());
        $this->assertInstanceOf(HelperPluginManager::class, $manager);
        $this->assertTrue($manager->has('testHelper'));
        $this->assertInstanceOf(TestHelper::class, $manager->get('testHelper'));
        return $manager;
    }
}
