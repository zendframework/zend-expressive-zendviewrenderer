<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\ZendView;

use Prophecy\Prophecy\ProphecyInterface;
use Psr\Container\ContainerInterface;
use Zend\Expressive\ZendView\NamespacedPathStackResolver;
use Zend\Expressive\ZendView\NamespacedPathStackResolverFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Zend\Expressive\ZendView\NamespacedPathStackResolverFactory
 */
class NamespacedPathStackResolverFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|ProphecyInterface
     */
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function canCreateResolver()
    {
        $factory = new NamespacedPathStackResolverFactory();
        $resolver = $factory($this->container->reveal());
        $this->assertInstanceOf(NamespacedPathStackResolver::class, $resolver);
    }

    public function testConfiguresCustomDefaultSuffix()
    {
        $config = [
            'templates' => [
                'extension' => 'php',
            ],
        ];

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);

        $factory = new NamespacedPathStackResolverFactory();
        $resolver = $factory($this->container->reveal());
        $this->assertEquals('php', $resolver->getDefaultSuffix());
    }
}
