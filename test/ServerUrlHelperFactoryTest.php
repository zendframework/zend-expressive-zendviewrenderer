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
use Zend\Expressive\Helper\ServerUrlHelper as BaseServerUrlHelper;
use Zend\Expressive\ZendView\Exception\MissingHelperException;
use Zend\Expressive\ZendView\ServerUrlHelper;
use Zend\Expressive\ZendView\ServerUrlHelperFactory;
use Zend\View\HelperPluginManager;

class ServerUrlHelperFactoryTest extends PHPUnit_Framework_TestCase
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
        $baseHelper = $this->prophesize(BaseServerUrlHelper::class);
        $this->container->has(BaseServerUrlHelper::class)->willReturn(true);
        $this->container->get(BaseServerUrlHelper::class)->willReturn($baseHelper->reveal());
        $helpers = new HelperPluginManager($this->container->reveal());
        $factory = new ServerUrlHelperFactory();
        $helper = $factory($helpers);
        $this->assertInstanceOf(ServerUrlHelper::class, $helper);
    }

    public function testExceptionIsRisedIfBaseHelperIsNotAvailableInContainer()
    {
        $this->container->has(BaseServerUrlHelper::class)->willReturn(false);
        $helpers = new HelperPluginManager($this->container->reveal());
        $factory = new ServerUrlHelperFactory();
        $this->setExpectedException(MissingHelperException::class);
        $factory($helpers);
    }
}
