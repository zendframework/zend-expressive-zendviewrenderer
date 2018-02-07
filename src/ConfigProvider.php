<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Expressive\ZendView;

use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\View\HelperPluginManager;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
        ];
    }

    public function getDependencies() : array
    {
        return [
            'aliases' => [
                TemplateRendererInterface::class => ZendViewRenderer::class,
            ],
            'factories' => [
                HelperPluginManager::class => HelperPluginManagerFactory::class,
                ZendViewRenderer::class => ZendViewRendererFactory::class,
            ],
        ];
    }

    public function getTemplates() : array
    {
        return [
            'layout' => 'layout::default',
            'paths' => [],
        ];
    }
}
