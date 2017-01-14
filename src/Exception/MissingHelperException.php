<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-zendviewrenderer for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-zendviewrenderer/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\ZendView\Exception;

use DomainException;
use Interop\Container\Exception\ContainerException;

class MissingHelperException extends DomainException implements
    ContainerException,
    ExceptionInterface
{
}
