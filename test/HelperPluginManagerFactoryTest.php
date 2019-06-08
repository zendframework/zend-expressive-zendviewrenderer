<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2015-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\ZendView;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ProphecyInterface;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Helper as ExpressiveHelper;
use Zend\Expressive\ZendView\HelperPluginManagerFactory;
use Zend\Expressive\ZendView\ServerUrlHelper;
use Zend\Expressive\ZendView\UrlHelper;
use Zend\View\HelperPluginManager;
use ZendTest\Expressive\ZendView\TestAsset\TestHelper;

class HelperPluginManagerFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|ProphecyInterface
     */
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
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

    public function testInjectsCustomHelpersIntoHelperManager()
    {
        $this->container->has(ExpressiveHelper\UrlHelper::class)->willReturn(true);
        $this->container->get(ExpressiveHelper\UrlHelper::class)->willReturn(
            $this->prophesize(ExpressiveHelper\UrlHelper::class)->reveal()
        );
        $this->container->has(ExpressiveHelper\ServerUrlHelper::class)->willReturn(true);
        $this->container->get(ExpressiveHelper\ServerUrlHelper::class)->willReturn(
            $this->prophesize(ExpressiveHelper\ServerUrlHelper::class)->reveal()
        );

        $this->container->has('config')->willReturn(false);
        $factory = new HelperPluginManagerFactory();
        $helpers = $factory($this->container->reveal());

        $this->assertTrue($helpers->has('url'));
        $this->assertTrue($helpers->has('serverurl'));
        $this->assertInstanceOf(UrlHelper::class, $helpers->get('url'));
        $this->assertInstanceOf(ServerUrlHelper::class, $helpers->get('serverurl'));
    }
}
