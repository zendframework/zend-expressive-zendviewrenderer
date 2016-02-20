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
use Interop\Container\ContainerInterface;
use Zend\Expressive\Helper\UrlHelper as BaseUrlHelper;
use Zend\Expressive\ZendView\Exception\MissingHelperException;
use Zend\Expressive\ZendView\UrlHelper;
use Zend\Expressive\ZendView\UrlHelperFactory;
use Zend\View\HelperPluginManager;

class UrlHelperFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testCreatesUrlViewHelper()
    {
        $baseHelper = $this->prophesize(BaseUrlHelper::class);
        $this->container->has(BaseUrlHelper::class)->willReturn(true);
        $this->container->get(BaseUrlHelper::class)->willReturn($baseHelper->reveal());
        $helpers = new HelperPluginManager($this->container->reveal());
        $factory = new UrlHelperFactory();
        $helper = $factory($helpers);
        $this->assertInstanceOf(UrlHelper::class, $helper);
    }

    public function testExceptionIsRisedIfBaseHelperIsNotAvailableInContainer()
    {
        $this->container->has(BaseUrlHelper::class)->willReturn(false);
        $helpers = new HelperPluginManager($this->container->reveal());
        $factory = new UrlHelperFactory();
        $this->setExpectedException(MissingHelperException::class);
        $factory($helpers);
    }
}
