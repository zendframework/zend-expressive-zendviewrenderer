<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\ZendView;

use PHPUnit_Framework_TestCase;
use Zend\Expressive\Helper\UrlHelper as BaseUrlHelper;
use Zend\Expressive\ZendView\Exception\MissingHelperException;
use Zend\Expressive\ZendView\UrlHelper;
use Zend\Expressive\ZendView\UrlHelperFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager;

class UrlHelperFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    private $container;

    public function setUp()
    {
        $this->container = new ServiceManager();
    }

    public function testCreatesUrlViewHelper()
    {
        $baseHelper = $this->prophesize(BaseUrlHelper::class);
        $this->container->setService(BaseUrlHelper::class, $baseHelper->reveal());
        $helpers = new HelperPluginManager($this->container);
        $factory = new UrlHelperFactory();

        // test if we are using Zend\ServiceManager v2 or v3
        if (! method_exists($helpers, 'configure')) {
            $container = $helpers;
        } else {
            $container = $this->container;
        }

        $helper = $factory($container);
        $this->assertInstanceOf(UrlHelper::class, $helper);
    }

    public function testExceptionIsRaisedIfBaseHelperIsNotAvailableInContainer()
    {
        $helpers = new HelperPluginManager($this->container);
        $factory = new UrlHelperFactory();
        $this->setExpectedException(MissingHelperException::class);
        $factory($helpers);
    }
}
