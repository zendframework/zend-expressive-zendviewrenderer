<?php

namespace Zend\Expressive\ZendView;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Config;
use Zend\View\HelperPluginManager;

class HelperPluginManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = isset($config['view_helpers']) ? $config['view_helpers'] : [];
        $manager = new HelperPluginManager(new Config($config));
        $manager->setServiceLocator($container);

        return $manager;
    }
}
