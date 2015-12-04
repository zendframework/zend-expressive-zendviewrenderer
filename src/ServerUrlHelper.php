<?php
/**
 * @see       http://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\ZendView;

use Psr\Http\Message\UriInterface;
use Zend\Expressive\Helper\ServerUrlHelper as BaseHelper;
use Zend\View\Helper\AbstractHelper;

/**
 * Alternate ServerUrl helper for use in Expressive.
 */
class ServerUrlHelper extends AbstractHelper
{
    /**
     * @var BaseHelper
     */
    private $helper;

    /**
     * @param BaseHelper $helper
     */
    public function __construct(BaseHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Return a path relative to the current request URI.
     *
     * Proxies to `Zend\Expressive\Helper\ServerUrlHelper::generate()`.
     *
     * @param null|string $path
     * @return string
     */
    public function __invoke($path = null)
    {
        return $this->helper->generate($path);
    }

    /**
     * Proxies to `Zend\Expressive\Helper\ServerUrlHelper::setUri()`
     * @param UriInterface $uri
     */
    public function setUri(UriInterface $uri)
    {
        $this->helper->setUri($uri);
    }
}
