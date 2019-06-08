<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2015-2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\ZendView;

use Psr\Container\ContainerInterface;
use Zend\View\Renderer\PhpRenderer;

use function is_array;
use function is_numeric;

/**
 * Create and return a ZendView template instance.
 *
 * Optionally uses the service 'config', which should return an array. This
 * factory consumes the following structure:
 *
 * <code>
 * 'templates' => [
 *     'extension' => 'default template file extension',
 *     'layout' => 'name of layout view to use, if any',
 *     'map'    => [
 *         // template => filename pairs
 *     ],
 *     'paths'  => [
 *         // namespace / path pairs
 *         //
 *         // Numeric namespaces imply the default/main namespace. Paths may be
 *         // strings or arrays of string paths to associate with the namespace.
 *     ],
 * ]
 * </code>
 */
final class ZendViewRendererFactory
{
    public function __invoke(ContainerInterface $container) : ZendViewRenderer
    {
        $config   = $container->has('config') ? $container->get('config') : [];
        $config   = $config['templates'] ?? [];

        $renderer = $container->get(PhpRenderer::class);
        $nsPathResolver = $container->get(NamespacedPathStackResolver::class);

        // Inject renderer
        $view = new ZendViewRenderer($renderer, $nsPathResolver, $config['layout'] ?? null);

        // Add template paths
        $allPaths = isset($config['paths']) && is_array($config['paths']) ? $config['paths'] : [];
        foreach ($allPaths as $namespace => $paths) {
            $namespace = is_numeric($namespace) ? null : $namespace;
            foreach ((array) $paths as $path) {
                $view->addPath($path, $namespace);
            }
        }

        return $view;
    }
}
