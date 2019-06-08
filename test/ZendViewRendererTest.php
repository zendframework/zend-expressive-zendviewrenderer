<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2015-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\ZendView;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Template\Exception\InvalidArgumentException;
use Zend\Expressive\Template\TemplatePath;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\ZendView\NamespacedPathStackResolver;
use Zend\Expressive\ZendView\ZendViewRenderer;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

use function file_get_contents;
use function sprintf;
use function str_replace;
use function trim;
use function uniqid;
use function var_export;

use const DIRECTORY_SEPARATOR;

/**
 * @covers \Zend\Expressive\ZendView\ZendViewRenderer
 */
class ZendViewRendererTest extends TestCase
{
    /**
     * @var NamespacedPathStackResolver
    */
    private $resolver;

    /**
     * @var PhpRenderer
     */
    private $renderer;

    public function setUp()
    {
        $this->resolver = new NamespacedPathStackResolver();
        $this->renderer = new PhpRenderer();
        $this->renderer->setResolver($this->resolver);
    }

    public function assertTemplatePath($path, TemplatePath $templatePath, $message = null)
    {
        $message = $message ?: sprintf('Failed to assert TemplatePath contained path %s', $path);
        $this->assertEquals($path, $templatePath->getPath(), $message);
    }

    public function assertTemplatePathString($path, TemplatePath $templatePath, $message = null)
    {
        $message = $message ?: sprintf('Failed to assert TemplatePath casts to string path %s', $path);
        $this->assertEquals($path, (string) $templatePath, $message);
    }

    public function assertTemplatePathNamespace($namespace, TemplatePath $templatePath, $message = null)
    {
        $message = $message
            ?: sprintf('Failed to assert TemplatePath namespace matched %s', var_export($namespace, true));
        $this->assertEquals($namespace, $templatePath->getNamespace(), $message);
    }

    public function assertEmptyTemplatePathNamespace(TemplatePath $templatePath, $message = null)
    {
        $message = $message ?: 'Failed to assert TemplatePath namespace was empty';
        $this->assertEmpty($templatePath->getNamespace(), $message);
    }

    public function assertEqualTemplatePath(TemplatePath $expected, TemplatePath $received, $message = null)
    {
        $message = $message ?: 'Failed to assert TemplatePaths are equal';
        if ($expected->getPath() !== $received->getPath()
            || $expected->getNamespace() !== $received->getNamespace()
        ) {
            $this->fail($message);
        }
    }

    public function testInstantiatingWithInvalidLayout()
    {
        $this->expectException(InvalidArgumentException::class);

        new ZendViewRenderer($this->renderer, $this->resolver, []);
    }

    public function testCanAddPathWithEmptyNamespace()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $paths = $renderer->getPaths();
        $this->assertIsArray($paths);
        $this->assertCount(1, $paths);
        $this->assertTemplatePath(__DIR__ . '/TestAsset' . DIRECTORY_SEPARATOR, $paths[0]);
        $this->assertTemplatePathString(__DIR__ . '/TestAsset' . DIRECTORY_SEPARATOR, $paths[0]);
        $this->assertEmptyTemplatePathNamespace($paths[0]);
    }

    public function testCanAddPathWithNamespace()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset', 'test');
        $paths = $renderer->getPaths();
        $this->assertIsArray($paths);
        $this->assertCount(1, $paths);
        $this->assertTemplatePath(__DIR__ . '/TestAsset' . DIRECTORY_SEPARATOR, $paths[0]);
        $this->assertTemplatePathString(__DIR__ . '/TestAsset' . DIRECTORY_SEPARATOR, $paths[0]);
        $this->assertTemplatePathNamespace('test', $paths[0]);
    }

    public function testDelegatesRenderingToUnderlyingImplementation()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $name = 'zendview';
        $result = $renderer->render('zendview', [ 'name' => $name ]);
        $this->assertContains($name, $result);
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $this->assertEquals($content, $result);
    }

    public function invalidParameterValues()
    {
        return [
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'string'     => ['value'],
        ];
    }

    /**
     * @dataProvider invalidParameterValues
     *
     * @param mixed $params
     */
    public function testRenderRaisesExceptionForInvalidParameterTypes($params)
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $this->expectException(InvalidArgumentException::class);

        $renderer->render('foo', $params);
    }

    public function testCanRenderWithNullParams()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $result = $renderer->render('zendview-null', null);
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview-null.phtml');
        $this->assertEquals($content, $result);
    }

    public function objectParameterValues()
    {
        $names = [
            'stdClass'    => uniqid(),
            'ArrayObject' => uniqid(),
        ];

        return [
            'stdClass'    => [(object) ['name' => $names['stdClass']], $names['stdClass']],
            'ArrayObject' => [new ArrayObject(['name' => $names['ArrayObject']]), $names['ArrayObject']],
        ];
    }

    /**
     * @dataProvider objectParameterValues
     *
     * @param object $params
     * @param string $search
     */
    public function testCanRenderWithParameterObjects($params, $search)
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $result = $renderer->render('zendview', $params);
        $this->assertContains($search, $result);
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $search, $content);
        $this->assertEquals($content, $result);
    }

    /**
     * @group layout
     */
    public function testWillRenderContentInLayoutPassedToConstructor()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver, 'zendview-layout');
        $renderer->addPath(__DIR__ . '/TestAsset');
        $name = 'zendview';
        $result = $renderer->render('zendview', [ 'name' => $name ]);
        $this->assertContains($name, $result);
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $this->assertContains($content, $result);
        $this->assertContains('<title>Layout Page</title>', $result, sprintf('Received %s', $result));
    }

    public function testSharedParameterIsAvailableInLayout()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver, 'zendview-layout-variable');
        $renderer->addPath(__DIR__ . '/TestAsset');
        $title = uniqid('ZendViewTitle', true);
        $renderer->addDefaultParam($renderer::TEMPLATE_ALL, 'title', $title);

        $name = uniqid('ZendViewName', true);
        $result = $renderer->render('zendview', ['name' => $name]);

        $this->assertContains($title, $result);
        $this->assertContains($name, $result);
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $this->assertContains($content, $result);
        $expected = sprintf('<title>Layout Page: %s</title>', $title);
        $this->assertContains($expected, $result, sprintf('Received %s', $result));
    }

    public function testTemplateDefaultParameterIsNotAvailableInLayout()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver, 'zendview-layout-variable');
        $renderer->addPath(__DIR__ . '/TestAsset');
        $title = uniqid('ZendViewTitle', true);
        $renderer->addDefaultParam('zendview', 'title', $title);

        $name = uniqid('ZendViewName', true);
        $result = $renderer->render('zendview', ['name' => $name]);

        $this->assertNotContains($title, $result);
        $this->assertContains($name, $result);
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $this->assertContains($content, $result);
        $expected = sprintf('<title>Layout Page: %s</title>', '');
        $this->assertContains($expected, $result, sprintf('Received %s', $result));
    }

    public function testLayoutTemplateDefaultParameterIsAvailableInLayout()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver, 'zendview-layout-variable');
        $renderer->addPath(__DIR__ . '/TestAsset');
        $title = uniqid('ZendViewTitle', true);
        $name = uniqid('ZendViewName', true);
        $renderer->addDefaultParam('zendview-layout-variable', 'title', $title);
        $result = $renderer->render('zendview', ['name' => $name]);
        $this->assertContains($title, $result);
        $this->assertContains($name, $result);

        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $layout = file_get_contents(__DIR__ . '/TestAsset/zendview-layout-variable.phtml');
        $layout = str_replace('<?= $this->title ?>', $title, $layout);
        $layout = str_replace('<?= $this->content ?>' . PHP_EOL, $content, $layout);
        $this->assertContains($layout, $result);

        $expected = sprintf('<title>Layout Page: %s</title>', $title);
        $this->assertContains($expected, $result, sprintf('Received %s', $result));
    }

    public function testVariableInProvidedLayoutViewModelOverridesTemplateDefaultParameter()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $titleToBeOverriden = uniqid('ZendViewTitleToBeOverriden', true);
        $title = uniqid('ZendViewTitle', true);
        $name = uniqid('ZendViewName', true);
        $renderer->addDefaultParam('zendview-layout-variable', 'title', $titleToBeOverriden);

        $layout = new ViewModel(['title' => $title]);
        $layout->setTemplate('zendview-layout-variable');
        $result = $renderer->render('zendview', ['name' => $name, 'layout' => $layout]);
        $this->assertContains($title, $result);
        $this->assertContains($name, $result);

        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $layout = file_get_contents(__DIR__ . '/TestAsset/zendview-layout-variable.phtml');
        $layout = str_replace('<?= $this->title ?>', $title, $layout);
        $layout = str_replace('<?= $this->content ?>' . PHP_EOL, $content, $layout);
        $this->assertContains($layout, $result);

        $expected = sprintf('<title>Layout Page: %s</title>', $title);
        $this->assertContains($expected, $result, sprintf('Received %s', $result));
    }

    public function testTemplateDefaultParameterIsAvailableInLayoutProvidedWithViewModel()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $title = uniqid('ZendViewTitle', true);
        $name = uniqid('ZendViewName', true);
        $renderer->addDefaultParam('zendview-layout-variable', 'title', $title);

        $layout = new ViewModel();
        $layout->setTemplate('zendview-layout-variable');
        $result = $renderer->render('zendview', ['name' => $name, 'layout' => $layout]);
        $this->assertContains($title, $result);
        $this->assertContains($name, $result);

        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $layout = file_get_contents(__DIR__ . '/TestAsset/zendview-layout-variable.phtml');
        $layout = str_replace('<?= $this->title ?>', $title, $layout);
        $layout = str_replace('<?= $this->content ?>' . PHP_EOL, $content, $layout);
        $this->assertContains($layout, $result);

        $expected = sprintf('<title>Layout Page: %s</title>', $title);
        $this->assertContains($expected, $result, sprintf('Received %s', $result));
    }

    /**
     * @group layout
     */
    public function testWillRenderContentInLayoutPassedDuringRendering()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $name = 'zendview';
        $result = $renderer->render('zendview', [ 'name' => $name, 'layout' => 'zendview-layout' ]);
        $this->assertContains($name, $result);
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $this->assertContains($content, $result);

        $this->assertContains('<title>Layout Page</title>', $result);
    }

    /**
     * @group layout
     */
    public function testLayoutPassedWhenRenderingOverridesLayoutPassedToConstructor()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver, 'zendview-layout');
        $renderer->addPath(__DIR__ . '/TestAsset');
        $name = 'zendview';
        $result = $renderer->render('zendview', [ 'name' => $name, 'layout' => 'zendview-layout2' ]);
        $this->assertContains($name, $result);
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $this->assertContains($content, $result);

        $this->assertContains('<title>ALTERNATE LAYOUT PAGE</title>', $result);
    }

    /**
     * @group layout
     */
    public function testCanPassViewModelForLayoutToConstructor()
    {
        $layout = new ViewModel();
        $layout->setTemplate('zendview-layout');

        $renderer = new ZendViewRenderer($this->renderer, $this->resolver, $layout);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $name = 'zendview';
        $result = $renderer->render('zendview', [ 'name' => $name ]);
        $this->assertContains($name, $result);
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $this->assertContains($content, $result);
        $this->assertContains('<title>Layout Page</title>', $result, sprintf('Received %s', $result));
    }

    /**
     * @group layout
     */
    public function testCanPassViewModelForLayoutParameterWhenRendering()
    {
        $layout = new ViewModel();
        $layout->setTemplate('zendview-layout2');

        $renderer = new ZendViewRenderer($this->renderer, $this->resolver, 'zendview-layout');
        $renderer->addPath(__DIR__ . '/TestAsset');
        $name = 'zendview';
        $result = $renderer->render('zendview', [ 'name' => $name, 'layout' => $layout ]);
        $this->assertContains($name, $result);
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $this->assertContains($content, $result);
        $this->assertContains('<title>ALTERNATE LAYOUT PAGE</title>', $result);
    }

    /**
     * @group layout
     */
    public function testDisableLayoutOnRender()
    {
        $layout = new ViewModel();
        $layout->setTemplate('zendview-layout');

        $renderer = new ZendViewRenderer($this->renderer, $this->resolver, $layout);
        $renderer->addPath(__DIR__ . '/TestAsset');

        $name = 'zendview';
        $rendered = $renderer->render('zendview', [
            'layout' => false,
            'name'   => $name,
        ]);

        $expected = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $expected = str_replace('<?php echo $name ?>', $name, $expected);

        $this->assertEquals($rendered, $expected);
    }

    /**
     * @group layout
     */
    public function testDisableLayoutViaDefaultParameter()
    {
        $layout = new ViewModel();
        $layout->setTemplate('zendview-layout');

        $renderer = new ZendViewRenderer($this->renderer, $this->resolver, $layout);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $renderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'layout', false);


        $name = 'zendview';
        $rendered = $renderer->render('zendview', [ 'name' => $name ]);

        $expected = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $expected = str_replace('<?php echo $name ?>', $name, $expected);

        $this->assertEquals($rendered, $expected);
    }

    /**
     * @group namespacing
     */
    public function testProperlyResolvesNamespacedTemplate()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset/test', 'test');

        $expected = file_get_contents(__DIR__ . '/TestAsset/test/test.phtml');
        $test     = $renderer->render('test::test');

        $this->assertSame($expected, $test);
    }

    public function testAddParameterToOneTemplate()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $name = 'ZendView';
        $renderer->addDefaultParam('zendview', 'name', $name);
        $result = $renderer->render('zendview');

        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $this->assertEquals($content, $result);
    }

    public function testAddSharedParameters()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $name = 'ZendView';
        $renderer->addDefaultParam($renderer::TEMPLATE_ALL, 'name', $name);
        $result = $renderer->render('zendview');
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $this->assertEquals($content, $result);

        $result = $renderer->render('zendview-2');
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview-2.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $this->assertEquals($content, $result);
    }

    public function testOverrideSharedParametersPerTemplate()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $name = 'Zend';
        $name2 = 'View';
        $renderer->addDefaultParam($renderer::TEMPLATE_ALL, 'name', $name);
        $renderer->addDefaultParam('zendview-2', 'name', $name2);
        $result = $renderer->render('zendview');
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name, $content);
        $this->assertEquals($content, $result);

        $result = $renderer->render('zendview-2');
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview-2.phtml');
        $content = str_replace('<?php echo $name ?>', $name2, $content);
        $this->assertEquals($content, $result);
    }

    public function useArrayOrViewModel()
    {
        return [
            'array'      => [false],
            'view-model' => [true],
        ];
    }

    /**
     * @dataProvider useArrayOrViewModel
     *
     * @param bool $viewAsModel
     */
    public function testOverrideSharedParametersAtRender($viewAsModel)
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $name = 'Zend';
        $name2 = 'View';
        $renderer->addDefaultParam($renderer::TEMPLATE_ALL, 'name', $name);

        $viewModel = ['name' => $name2];
        $viewModel = $viewAsModel ? new ViewModel($viewModel) : $viewModel;

        $result = $renderer->render('zendview', $viewModel);
        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $name2, $content);
        $this->assertEquals($content, $result);
    }

    public function testWillRenderAViewModel()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');

        $viewModel = new ViewModel(['name' => 'Zend']);
        $result = $renderer->render('zendview', $viewModel);

        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', 'Zend', $content);
        $this->assertEquals($content, $result);
    }

    public function testCanRenderWithChildViewModel()
    {
        $path = __DIR__ . '/TestAsset';
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath($path);

        $viewModelChild = new ViewModel();
        $viewModelChild->setTemplate('zendview-null');

        $viewModelParent = new ViewModel();
        $viewModelParent->setVariables([
            'layout' => 'zendview-layout',
        ]);
        $viewModelParent->addChild($viewModelChild, 'name');

        $result = $renderer->render('zendview', $viewModelParent);

        $content             = file_get_contents(sprintf('%s/zendview-null.phtml', $path));
        $contentParent       = file_get_contents(sprintf('%s/zendview.phtml', $path));
        $contentParentLayout = file_get_contents(sprintf('%s/zendview-layout.phtml', $path));

        // trim is used here, because rendering engine is trimming content too
        $content = trim(str_replace('<?php echo $name ?>', $content, $contentParent));
        $content = str_replace('<?= $this->content ?>', $content, $contentParentLayout);

        $this->assertEquals($content, $result);
    }

    public function testRenderChildWithDefaultParameter()
    {
        $name2 = 'Foo';

        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');
        $renderer->addDefaultParam('zendview-2', 'name', $name2);

        $viewModelChild = new ViewModel();
        $viewModelChild->setTemplate('zendview-2');

        $viewModelParent = new ViewModel();
        $viewModelParent->addChild($viewModelChild, 'name');

        $result = $renderer->render('zendview', $viewModelParent);

        $contentChild = file_get_contents(__DIR__ . '/TestAsset/zendview-2.phtml');
        $contentChild = str_replace('<?php echo $name ?>', $name2, $contentChild);

        $content = file_get_contents(__DIR__ . '/TestAsset/zendview.phtml');
        $content = str_replace('<?php echo $name ?>', $contentChild, $content);

        static::assertEquals($content, $result);
    }

    public function testChangeLayoutInTemplate()
    {
        $renderer = new ZendViewRenderer($this->renderer, $this->resolver);
        $renderer->addPath(__DIR__ . '/TestAsset');

        $result = $renderer->render('zendview-change-layout', ['layout' => 'zendview-layout']);

        $contentChild = file_get_contents(__DIR__ . '/TestAsset/zendview-change-layout.phtml');
        $contentChild = str_replace("<?php \$this->layout('zendview-layout2'); ?>\n", '', $contentChild);

        $content = file_get_contents(__DIR__ . '/TestAsset/zendview-layout2.phtml');
        $content = str_replace("<?= \$this->content ?>\n", $contentChild, $content);

        static::assertEquals($content, $result);
    }
}
