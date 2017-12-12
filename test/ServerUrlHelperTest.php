<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Expressive\ZendView;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ProphecyInterface;
use Psr\Http\Message\UriInterface;
use Zend\Expressive\Helper\ServerUrlHelper as BaseHelper;
use Zend\Expressive\ZendView\ServerUrlHelper;

class ServerUrlHelperTest extends TestCase
{
    /**
     * @var BaseHelper|ProphecyInterface
     */
    private $baseHelper;

    public function setUp()
    {
        $this->baseHelper = $this->prophesize(BaseHelper::class);
    }

    public function createHelper()
    {
        return new ServerUrlHelper($this->baseHelper->reveal());
    }

    public function testInvocationProxiesToBaseHelper()
    {
        $this->baseHelper->generate('/foo')->willReturn('https://example.com/foo');
        $helper = $this->createHelper();
        $this->assertEquals('https://example.com/foo', $helper('/foo'));
    }

    public function testSetUriProxiesToBaseHelper()
    {
        $uri = $this->prophesize(UriInterface::class);
        $this->baseHelper->setUri($uri->reveal())->shouldBeCalled();
        $helper = $this->createHelper();
        $helper->setUri($uri->reveal());
    }
}
