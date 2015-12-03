<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\ZendView;

use ArrayObject;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Expressive\Helper\UrlHelper as BaseHelper;
use Zend\Expressive\ZendView\UrlHelper;

class UrlHelperTest extends TestCase
{
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
        $this->baseHelper->generate('resource', ['id' => 'sha1'])->willReturn('/resource/sha1');
        $helper = $this->createHelper();
        $this->assertEquals('/resource/sha1', $helper('resource', ['id' => 'sha1']));
    }
}
