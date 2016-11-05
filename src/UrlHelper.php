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
     * @param string $routeName
     * @param array  $routeParams
     * @param array  $queryParams
     * @param string $fragmentIdentifier
     * @param array  $options       Can have the following keys:
     *                              - router (array): contains options to be passed to the router
     *                              - reuse_result_params (bool): indicates if the current RouteResult
     *                              parameters will be used, defaults to true
     *
     * @return string
     */
    public function __invoke(
        $routeName = null,
        array $routeParams = [],
        array $queryParams = [],
        $fragmentIdentifier = '',
        array $options = []
    ) {
        return $this->helper->generate($routeName, $routeParams, $queryParams, $fragmentIdentifier, $options);
    }
}
