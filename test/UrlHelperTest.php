<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\ZendView;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ProphecyInterface;
use Zend\Expressive\Helper\UrlHelper as BaseHelper;
use Zend\Expressive\ZendView\UrlHelper;

class UrlHelperTest extends TestCase
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
        return new UrlHelper($this->baseHelper->reveal());
    }

    public function testInvocationProxiesToBaseHelper()
    {
        $this->baseHelper->generate('resource', ['id' => 'sha1'], [], null, [])->willReturn('/resource/sha1');
        $helper = $this->createHelper();
        $this->assertEquals('/resource/sha1', $helper('resource', ['id' => 'sha1']));
    }

    public function testUrlHelperAcceptsQueryParametersFragmentAndOptions()
    {
        $this->baseHelper->generate(
            'resource',
            ['id' => 'sha1'],
            ['foo' => 'bar'],
            'fragment',
            ['reuse_result_params' => true]
        )->willReturn('PATH');
        $helper = $this->createHelper();
        $this->assertEquals(
            'PATH',
            $helper('resource', ['id' => 'sha1'], ['foo' => 'bar'], 'fragment', ['reuse_result_params' => true])
        );
    }

    /**
     * In particular, the fragment identifier needs to be null.
     */
    public function testUrlHelperPassesExpectedDefaultsToBaseHelper()
    {
        $this->baseHelper->generate(
            null,
            [],
            [],
            null,
            []
        )->willReturn('PATH');
        $helper = $this->createHelper();
        $this->assertEquals(
            'PATH',
            $helper()
        );
    }
}
