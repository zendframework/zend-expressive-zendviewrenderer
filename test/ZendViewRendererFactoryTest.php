<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2015-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\ZendView;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\ProphecyInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use ReflectionProperty;
use Zend\Expressive\Helper;
use Zend\Expressive\Template\TemplatePath;
use Zend\Expressive\ZendView\Exception\InvalidContainerException;
use Zend\Expressive\ZendView\ServerUrlHelper;
use Zend\Expressive\ZendView\UrlHelper;
use Zend\Expressive\ZendView\ZendViewRenderer;
use Zend\Expressive\ZendView\ZendViewRendererFactory;
use Zend\Expressive\ZendView\NamespacedPathStackResolver;
use Zend\View\HelperPluginManager;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;

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

    public function fetchPhpRenderer(ZendViewRenderer $view)
    {
        $r = new ReflectionProperty($view, 'renderer');
        $r->setAccessible(true);
        return $r->getValue($view);
    }

    public function injectContainerService($name, $service)
    {
        $this->container->has($name)->willReturn(true);
        $this->container->get($name)->willReturn(
            $service instanceof ObjectProphecy ? $service->reveal() : $service
        );
    }

    public function injectBaseHelpers()
    {
        $this->injectContainerService(
            Helper\UrlHelper::class,
            $this->prophesize(Helper\UrlHelper::class)
        );
        $this->injectContainerService(
            Helper\ServerUrlHelper::class,
            $this->prophesize(Helper\ServerUrlHelper::class)
        );
    }

    public function testCallingFactoryWithNoConfigReturnsZendViewInstance()
    {
        $this->container->has('config')->willReturn(false);
        $this->container->has(HelperPluginManager::class)->willReturn(false);
        $this->container->has(PhpRenderer::class)->willReturn(false);
        $this->injectBaseHelpers();
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
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);
        $this->container->has(HelperPluginManager::class)->willReturn(false);
        $this->container->has(PhpRenderer::class)->willReturn(false);
        $this->injectBaseHelpers();
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
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);
        $this->container->has(HelperPluginManager::class)->willReturn(false);
        $this->container->has(PhpRenderer::class)->willReturn(false);
        $this->injectBaseHelpers();
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
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);
        $this->container->has(HelperPluginManager::class)->willReturn(false);
        $this->container->has(PhpRenderer::class)->willReturn(false);
        $this->injectBaseHelpers();
        $factory = new ZendViewRendererFactory();
        $view = $factory($this->container->reveal());

        $r = new ReflectionProperty($view, 'renderer');
        $r->setAccessible(true);
        $renderer  = $r->getValue($view);
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

    public function testConfiguresCustomDefaultSuffix()
    {
        $config = [
            'templates' => [
                'extension' => 'php',
            ],
        ];

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);
        $this->container->has(HelperPluginManager::class)->willReturn(false);
        $this->container->has(PhpRenderer::class)->willReturn(false);

        $factory = new ZendViewRendererFactory();
        $view = $factory($this->container->reveal());

        $r = new ReflectionProperty($view, 'resolver');
        $r->setAccessible(true);
        $resolver  = $r->getValue($view);

        $this->assertInstanceOf(
            NamespacedPathStackResolver::class,
            $resolver,
            'Expected NamespacedPathStackResolver not found!'
        );
        $this->assertEquals('php', $resolver->getDefaultSuffix());
    }

    public function testConfiguresDeprecatedDefaultSuffix()
    {
        $config = [
            'templates' => [
                'default_suffix' => 'php',
            ],
        ];

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);
        $this->container->has(HelperPluginManager::class)->willReturn(false);
        $this->container->has(PhpRenderer::class)->willReturn(false);

        $factory = new ZendViewRendererFactory();
        $view = $factory($this->container->reveal());

        $r = new ReflectionProperty($view, 'resolver');
        $r->setAccessible(true);
        $resolver  = $r->getValue($view);

        $this->assertInstanceOf(
            NamespacedPathStackResolver::class,
            $resolver,
            'Expected NamespacedPathStackResolver not found!'
        );
        $this->assertEquals('php', $resolver->getDefaultSuffix());
    }

    public function testInjectsCustomHelpersIntoHelperManager()
    {
        $this->container->has('config')->willReturn(false);
        $this->container->has(HelperPluginManager::class)->willReturn(false);
        $this->container->has(PhpRenderer::class)->willReturn(false);
        $this->injectBaseHelpers();
        $factory = new ZendViewRendererFactory();
        $view    = $factory($this->container->reveal());
        $this->assertInstanceOf(ZendViewRenderer::class, $view);

        $renderer = $this->fetchPhpRenderer($view);
        $helpers  = $renderer->getHelperPluginManager();
        $this->assertInstanceOf(HelperPluginManager::class, $helpers);
        $this->assertTrue($helpers->has('url'));
        $this->assertTrue($helpers->has('serverurl'));
        $this->assertInstanceOf(UrlHelper::class, $helpers->get('url'));
        $this->assertInstanceOf(ServerUrlHelper::class, $helpers->get('serverurl'));
    }

    public function testWillUseHelperManagerFromContainer()
    {
        $this->container->has('config')->willReturn(false);
        $this->container->has(PhpRenderer::class)->willReturn(false);
        $this->injectBaseHelpers();

        $helpers = new HelperPluginManager($this->container->reveal());
        $this->container->has(HelperPluginManager::class)->willReturn(true);
        $this->container->get(HelperPluginManager::class)->willReturn($helpers);
        $factory = new ZendViewRendererFactory();
        $view    = $factory($this->container->reveal());
        $this->assertInstanceOf(ZendViewRenderer::class, $view);

        $renderer = $this->fetchPhpRenderer($view);
        $this->assertSame($helpers, $renderer->getHelperPluginManager());
        return $helpers;
    }

    /**
     * @depends testWillUseHelperManagerFromContainer
     *
     * @param HelperPluginManager $helpers
     */
    public function testInjectsCustomHelpersIntoHelperManagerFromContainer(HelperPluginManager $helpers)
    {
        $this->assertTrue($helpers->has('url'));
        $this->assertTrue($helpers->has('serverurl'));
        $this->assertInstanceOf(UrlHelper::class, $helpers->get('url'));
        $this->assertInstanceOf(ServerUrlHelper::class, $helpers->get('serverurl'));
    }

    public function testWillUseRendererFromContainer()
    {
        $engine = new PhpRenderer;
        $this->container->has('config')->willReturn(false);
        $this->container->has(HelperPluginManager::class)->willReturn(false);
        $this->injectContainerService(PhpRenderer::class, $engine);

        $factory = new ZendViewRendererFactory();
        $view = $factory($this->container->reveal());

        $composed = $this->fetchPhpRenderer($view);
        $this->assertSame($engine, $composed);
    }

    public function testWillRaiseExceptionIfContainerDoesNotImplementInteropContainerInterface()
    {
        $container = $this->prophesize(PsrContainerInterface::class);
        $container->has('config')->willReturn(false);
        $container->get('config')->shouldNotBeCalled();
        $container->has(PhpRenderer::class)->willReturn(false);
        $container->get(PhpRenderer::class)->shouldNotBeCalled();
        $container->has(HelperPluginManager::class)->willReturn(false);

        $factory = new ZendViewRendererFactory();

        $this->expectException(InvalidContainerException::class);
        $factory($container->reveal());
    }
}
