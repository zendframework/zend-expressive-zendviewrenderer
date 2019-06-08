<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2015-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\ZendView;

use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\ProphecyInterface;
use ReflectionProperty;
use Zend\Expressive\Template\TemplatePath;
use Zend\Expressive\ZendView\ZendViewRenderer;
use Zend\Expressive\ZendView\ZendViewRendererFactory;
use Zend\Expressive\ZendView\NamespacedPathStackResolver;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\PhpRenderer;

use function sprintf;

use const DIRECTORY_SEPARATOR;

class ZendViewRendererFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|ProphecyInterface
    */
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $nsResolver = new NamespacedPathStackResolver();
        $phpRenderer = new PhpRenderer();
        $phpRenderer->setResolver($nsResolver);
        $this->injectContainerService(PhpRenderer::class, $phpRenderer);
        $this->injectContainerService(NamespacedPathStackResolver::class, $nsResolver);
    }

    public function getConfigurationPaths()
    {
        return [
            'foo' => __DIR__ . '/TestAsset/bar',
            1 => __DIR__ . '/TestAsset/one',
            'bar' => [
                __DIR__ . '/TestAsset/baz',
                __DIR__ . '/TestAsset/bat',
            ],
            0 => [
                __DIR__ . '/TestAsset/two',
                __DIR__ . '/TestAsset/three',
            ],
        ];
    }

    public function assertPathsHasNamespace($namespace, array $paths, $message = null)
    {
        $message = $message ?: sprintf('Paths do not contain namespace %s', $namespace ?: 'null');

        $found = false;
        foreach ($paths as $path) {
            $this->assertInstanceOf(TemplatePath::class, $path, 'Non-TemplatePath found in paths list');
            if ($path->getNamespace() === $namespace) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, $message);
    }

    public function assertPathNamespaceCount($expected, $namespace, array $paths, $message = null)
    {
        $message = $message ?: sprintf('Did not find %d paths with namespace %s', $expected, $namespace ?: 'null');

        $count = 0;
        foreach ($paths as $path) {
            $this->assertInstanceOf(TemplatePath::class, $path, 'Non-TemplatePath found in paths list');
            if ($path->getNamespace() === $namespace) {
                $count += 1;
            }
        }
        $this->assertSame($expected, $count, $message);
    }

    public function assertPathNamespaceContains($expected, $namespace, array $paths, $message = null)
    {
        $message = $message ?: sprintf('Did not find path %s in namespace %s', $expected, $namespace ?: null);

        $found = [];
        foreach ($paths as $path) {
            $this->assertInstanceOf(TemplatePath::class, $path, 'Non-TemplatePath found in paths list');
            if ($path->getNamespace() === $namespace) {
                $found[] = $path->getPath();
            }
        }
        $this->assertContains($expected, $found, $message);
    }

    public function injectContainerService($name, $service)
    {
        $this->container->has($name)->willReturn(true);
        $this->container->get($name)->willReturn(
            $service instanceof ObjectProphecy ? $service->reveal() : $service
        );
    }

    public function testCallingFactoryWithNoConfigReturnsZendViewInstance()
    {
        $this->container->has('config')->willReturn(false);
        $factory = new ZendViewRendererFactory();
        $view    = $factory($this->container->reveal());
        $this->assertInstanceOf(ZendViewRenderer::class, $view);
        return $view;
    }

    /**
     * @depends testCallingFactoryWithNoConfigReturnsZendViewInstance
     *
     * @param ZendViewRenderer $view
     */
    public function testUnconfiguredZendViewInstanceContainsNoPaths(ZendViewRenderer $view)
    {
        $paths = $view->getPaths();
        $this->assertInternalType('array', $paths);
        $this->assertEmpty($paths);
    }

    public function testConfiguresLayout()
    {
        $config = [
            'templates' => [
                'layout' => 'layout/layout',
            ],
        ];
        $this->injectContainerService('config', $config);
        $factory = new ZendViewRendererFactory();
        $view = $factory($this->container->reveal());

        $r = new ReflectionProperty($view, 'layout');
        $r->setAccessible(true);
        $layout = $r->getValue($view);
        $this->assertInstanceOf(ModelInterface::class, $layout);
        $this->assertSame($config['templates']['layout'], $layout->getTemplate());
    }

    public function testConfiguresPaths()
    {
        $config = [
            'templates' => [
                'paths' => $this->getConfigurationPaths(),
            ],
        ];
        $this->injectContainerService('config', $config);
        $factory = new ZendViewRendererFactory();
        $view = $factory($this->container->reveal());

        $paths = $view->getPaths();
        $this->assertPathsHasNamespace('foo', $paths);
        $this->assertPathsHasNamespace('bar', $paths);
        $this->assertPathsHasNamespace(null, $paths);

        $this->assertPathNamespaceCount(1, 'foo', $paths);
        $this->assertPathNamespaceCount(2, 'bar', $paths);
        $this->assertPathNamespaceCount(3, null, $paths);

        $dirSlash = DIRECTORY_SEPARATOR;

        $this->assertPathNamespaceContains(
            __DIR__ . '/TestAsset/bar' . $dirSlash,
            'foo',
            $paths,
            var_export($paths, true)
        );
        $this->assertPathNamespaceContains(__DIR__ . '/TestAsset/baz' . $dirSlash, 'bar', $paths);
        $this->assertPathNamespaceContains(__DIR__ . '/TestAsset/bat' . $dirSlash, 'bar', $paths);
        $this->assertPathNamespaceContains(__DIR__ . '/TestAsset/one' . $dirSlash, null, $paths);
        $this->assertPathNamespaceContains(__DIR__ . '/TestAsset/two' . $dirSlash, null, $paths);
        $this->assertPathNamespaceContains(__DIR__ . '/TestAsset/three' . $dirSlash, null, $paths);
    }

    public function testWillUseRendererFromContainer()
    {
        $engine = new PhpRenderer;
        $this->container->has('config')->willReturn(false);
        $this->injectContainerService(PhpRenderer::class, $engine);

        $factory = new ZendViewRendererFactory();
        $view = $factory($this->container->reveal());

        $r = new ReflectionProperty($view, 'renderer');
        $r->setAccessible(true);
        $composed = $r->getValue($view);
        $this->assertSame($engine, $composed);
    }

    public function testWillUseNamespacedPathStackResolverFromContainer()
    {
        $this->container->has('config')->willReturn(false);
        $nsResolver = new NamespacedPathStackResolver();
        $this->injectContainerService(NamespacedPathStackResolver::class, $nsResolver);

        $factory = new ZendViewRendererFactory();
        $view = $factory($this->container->reveal());

        $r = new ReflectionProperty($view, 'resolver');
        $r->setAccessible(true);
        $composed = $r->getValue($view);
        $this->assertSame($nsResolver, $composed);
    }
}
