<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\ZendView;

use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\ProphecyInterface;
use Psr\Container\ContainerInterface;
use Zend\Expressive\ZendView\NamespacedPathStackResolver;
use Zend\Expressive\ZendView\PhpRendererFactory;
use PHPUnit\Framework\TestCase;
use Zend\View\HelperPluginManager;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;

/**
 * @covers \Zend\Expressive\ZendView\PhpRendererFactory
 */
class PhpRendererFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|ProphecyInterface
     */
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->has('config')->willReturn(false);
    }

    public function injectContainerService($name, $service)
    {
        $this->container->has($name)->willReturn(true);
        $this->container->get($name)->willReturn(
            $service instanceof ObjectProphecy ? $service->reveal() : $service
        );
    }

    public function testWillUseHelperPluginManagerFromContainer()
    {
        $this->injectContainerService(
            NamespacedPathStackResolver::class,
            new NamespacedPathStackResolver()
        );
        $helpers = new HelperPluginManager($this->container->reveal());
        $this->injectContainerService(HelperPluginManager::class, $helpers);
        $factory = new PhpRendererFactory();
        $renderer = $factory($this->container->reveal());
        $this->assertSame($helpers, $renderer->getHelperPluginManager());
    }

    public function testConfiguresAggregateResolver()
    {
        $this->injectContainerService(
            HelperPluginManager::class,
            new HelperPluginManager($this->container->reveal())
        );
        $nsResolver = new NamespacedPathStackResolver();
        $this->injectContainerService(NamespacedPathStackResolver::class, $nsResolver);
        $factory = new PhpRendererFactory();
        $renderer = $factory($this->container->reveal());
        $aggregate = $renderer->resolver();
        $this->assertInstanceOf(AggregateResolver::class, $aggregate);
        $this->assertContains($nsResolver, $aggregate, 'Expected NamespacedPathStackResolver not found!');
        $resolver = null;
        foreach ($aggregate as $resolver) {
            if ($resolver instanceof TemplateMapResolver) {
                break;
            }
        }
        $this->assertInstanceOf(TemplateMapResolver::class, $resolver, 'Expected TemplateMapResolver not found!');
    }

    public function testConfiguresTemplateMap()
    {
        $config = [
            'templates' => [
                'map' => [
                    'foo' => 'bar',
                    'bar' => 'baz',
                ],
            ],
        ];
        $this->injectContainerService('config', $config);
        $this->injectContainerService(
            HelperPluginManager::class,
            new HelperPluginManager($this->container->reveal())
        );
        $this->injectContainerService(
            NamespacedPathStackResolver::class,
            new NamespacedPathStackResolver()
        );
        $factory = new PhpRendererFactory();
        $renderer = $factory($this->container->reveal());
        $aggregate = $renderer->resolver();
        $this->assertInstanceOf(AggregateResolver::class, $aggregate);
        $resolver = false;
        foreach ($aggregate as $resolver) {
            if ($resolver instanceof TemplateMapResolver) {
                break;
            }
        }
        $this->assertInstanceOf(TemplateMapResolver::class, $resolver, 'Expected TemplateMapResolver not found!');
        $this->assertTrue($resolver->has('foo'));
        $this->assertEquals('bar', $resolver->get('foo'));
        $this->assertTrue($resolver->has('bar'));
        $this->assertEquals('baz', $resolver->get('bar'));
    }

    public function testWillUseHelperManagerFromContainer()
    {
        $helpers = new HelperPluginManager($this->container->reveal());
        $this->injectContainerService(HelperPluginManager::class, $helpers);
        $this->injectContainerService(
            NamespacedPathStackResolver::class,
            new NamespacedPathStackResolver()
        );
        $factory = new PhpRendererFactory();
        $renderer = $factory($this->container->reveal());

        $this->assertSame($helpers, $renderer->getHelperPluginManager());
        return $helpers;
    }
}
