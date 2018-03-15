<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\ZendView;

use Zend\Expressive\Template\ArrayParametersTrait;
use Zend\Expressive\Template\DefaultParamsTrait;
use Zend\Expressive\Template\Exception;
use Zend\Expressive\Template\TemplatePath;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\AggregateResolver;

/**
 * Template implementation bridging zendframework/zend-view.
 *
 * This implementation provides additional capabilities.
 *
 * First, it always ensures the resolver is an AggregateResolver, pushing any
 * non-Aggregate into a new AggregateResolver instance. Additionally, it always
 * registers a NamespacedPathStackResolver at priority 0 (lower than
 * default) in the Aggregate to ensure we can add and resolve namespaced paths.
 */
class ZendViewRenderer implements TemplateRendererInterface
{
    use ArrayParametersTrait;
    use DefaultParamsTrait;

    /**
     * @var ViewModel
     */
    private $layout;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var NamespacedPathStackResolver
     */
    private $resolver;

    /**
     * Constructor
     *
     * Allows specifying the renderer to use (any zend-view renderer is
     * allowed), and optionally also the layout.
     *
     * The layout may be:
     *
     * - a string layout name
     * - a ModelInterface instance representing the layout
     *
     * If no renderer is provided, a default PhpRenderer instance is created;
     * omitting the layout indicates no layout should be used by default when
     * rendering.
     *
     * @param null|RendererInterface $renderer
     * @param null|string|ModelInterface $layout
     * @throws Exception\InvalidArgumentException for invalid $layout types
     */
    public function __construct(RendererInterface $renderer = null, $layout = null)
    {
        if (null === $renderer) {
            $renderer = $this->createRenderer();
            $resolver = $renderer->resolver();
        } else {
            $resolver = $renderer->resolver();
            if (! $resolver instanceof AggregateResolver) {
                $aggregate = $this->getDefaultResolver();
                $aggregate->attach($resolver);
                $resolver = $aggregate;
            } elseif (! $this->hasNamespacedResolver($resolver)) {
                $this->injectNamespacedResolver($resolver);
            }
        }

        if ($layout && is_string($layout)) {
            $model = new ViewModel();
            $model->setTemplate($layout);
            $layout = $model;
        }

        if ($layout !== null && ! $layout instanceof ModelInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Layout must be a string layout template name or a %s instance; received %s',
                ModelInterface::class,
                (is_object($layout) ? get_class($layout) : gettype($layout))
            ));
        }

        $this->renderer = $renderer;
        $this->resolver = $this->getNamespacedResolver($resolver);
        $this->layout   = $layout;
    }

    /**
     * Render a template with the given parameters.
     *
     * If a layout was specified during construction, it will be used;
     * alternately, you can specify a layout to use via the "layout"
     * parameter/variable, using either:
     *
     * - a string layout template name
     * - a Zend\View\Model\ModelInterface instance
     *
     * Layouts specified with $params take precedence over layouts passed to
     * the constructor.
     *
     * @param string $name
     * @param array|ModelInterface|object $params
     * @return string
     */
    public function render($name, $params = [])
    {
        $viewModel = $params instanceof ModelInterface
            ? $this->mergeViewModel($name, $params)
            : $this->createModel($name, $params);

        $useLayout = false !== $viewModel->getVariable('layout', null);
        if ($useLayout) {
            $viewModel = $this->prepareLayout($viewModel);
        }

        return $this->renderModel($viewModel, $this->renderer);
    }

    /**
     * Add a path for templates.
     *
     * @param string $path
     * @param string $namespace
     * @return void
     */
    public function addPath($path, $namespace = null)
    {
        $this->resolver->addPath($path, $namespace);
    }

    /**
     * Get the template directories
     *
     * @return TemplatePath[]
     */
    public function getPaths()
    {
        $paths = [];

        foreach ($this->resolver->getPaths() as $namespace => $namespacedPaths) {
            if ($namespace === NamespacedPathStackResolver::DEFAULT_NAMESPACE
                || empty($namespace)
                || is_int($namespace)
            ) {
                $namespace = null;
            }

            foreach ($namespacedPaths as $path) {
                $paths[] = new TemplatePath($path, $namespace);
            }
        }

        return $paths;
    }

    /**
     * Create a view model from the template and parameters.
     *
     * @param string $name
     * @param mixed $params
     * @return ModelInterface
     */
    private function createModel($name, $params)
    {
        $params = $this->mergeParams($name, $this->normalizeParams($params));
        $model  = new ViewModel($params);
        $model->setTemplate($name);
        return $model;
    }

    /**
     * Do a recursive, depth-first rendering of a view model.
     *
     * @param ModelInterface $model
     * @param RendererInterface $renderer
     * @return string
     * @throws Exception\RenderingException if it encounters a terminal child.
     */
    private function renderModel(ModelInterface $model, RendererInterface $renderer)
    {
        foreach ($model as $child) {
            if ($child->terminate()) {
                throw new Exception\RenderingException('Cannot render; encountered a child marked terminal');
            }

            $capture = $child->captureTo();
            if (empty($capture)) {
                continue;
            }

            $result = $this->renderModel($child, $renderer);

            if ($child->isAppend()) {
                $oldResult = $model->{$capture};
                $model->setVariable($capture, $oldResult . $result);
                continue;
            }

            $model->setVariable($capture, $result);
        }

        return $renderer->render($model);
    }

    /**
     * Returns a PhpRenderer object
     *
     * @return PhpRenderer
     */
    private function createRenderer()
    {
        $renderer = new PhpRenderer();
        $renderer->setResolver($this->getDefaultResolver());
        return $renderer;
    }

    /**
     * Get the default resolver
     *
     * @return AggregateResolver
     */
    private function getDefaultResolver()
    {
        $resolver = new AggregateResolver();
        $this->injectNamespacedResolver($resolver);
        return $resolver;
    }

    /**
     * Attaches a new NamespacedPathStackResolver to the AggregateResolver
     *
     * A priority of 0 is used, to ensure it is the last queried.
     *
     * @param AggregateResolver $aggregate
     * @return void
     */
    private function injectNamespacedResolver(AggregateResolver $aggregate)
    {
        $aggregate->attach(new NamespacedPathStackResolver(), 0);
    }

    /**
     * @param AggregateResolver $aggregate
     * @return bool
     */
    private function hasNamespacedResolver(AggregateResolver $aggregate)
    {
        foreach ($aggregate as $resolver) {
            if ($resolver instanceof NamespacedPathStackResolver) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AggregateResolver $aggregate
     * @return null|NamespacedPathStackResolver
     */
    private function getNamespacedResolver(AggregateResolver $aggregate)
    {
        foreach ($aggregate as $resolver) {
            if ($resolver instanceof NamespacedPathStackResolver) {
                return $resolver;
            }
        }

        return null;
    }

    /**
     * Merge global/template parameters with provided view model.
     *
     * @param string $name Template name.
     * @param ModelInterface $model
     * @return ModelInterface
     */
    private function mergeViewModel($name, ModelInterface $model)
    {
        $params = $this->mergeParams(
            $name,
            $this->normalizeParams($model->getVariables())
        );
        $model->setVariables($params);
        $model->setTemplate($name);
        return $model;
    }

    /**
     * Prepare the layout, if any.
     *
     * Injects the view model in the layout view model, if present.
     *
     * If the view model contains a non-empty 'layout' variable, that value
     * will be used to seed a layout view model, if:
     *
     * - it is a string layout template name
     * - it is a ModelInterface instance
     *
     * If a layout is discovered in this way, it will override the one set in
     * the constructor, if any.
     *
     * Returns the provided $viewModel unchanged if no layout is discovered;
     * otherwise, a view model representing the layout, with the provided
     * view model as a child, is returned.
     *
     * @param ModelInterface $viewModel
     * @return ModelInterface
     */
    private function prepareLayout(ModelInterface $viewModel)
    {
        $providedLayout = $viewModel->getVariable('layout', null);
        if (is_string($providedLayout) && ! empty($providedLayout)) {
            $layout = new ViewModel();
            $layout->setTemplate($providedLayout);
            $viewModel->setVariable('layout', null);
        } elseif ($providedLayout instanceof ModelInterface) {
            $layout = $providedLayout;
            $viewModel->setVariable('layout', null);
        } else {
            $layout = $this->layout ? clone $this->layout : null;
        }

        if ($layout) {
            $layout->addChild($viewModel);
            $viewModel = $layout;
            $viewModel->setVariables($this->mergeParams($layout->getTemplate(), (array) $layout->getVariables()));
        }

        return $viewModel;
    }
}
