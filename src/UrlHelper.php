<?php
/**
 * @see       http://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\ZendView;

use Zend\Expressive\Helper\UrlHelper as BaseHelper;
use Zend\View\Helper\AbstractHelper;

class UrlHelper extends AbstractHelper
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
     * Proxies to `Zend\Expressive\Helper\UrlHelper::generate()`
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function __invoke($route = null, $params = [])
    {
        return $this->helper->generate($route, $params);
    }
}
