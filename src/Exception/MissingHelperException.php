<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\ZendView\Exception;

use DomainException;
use Interop\Container\Exception\ContainerException;

class MissingHelperException extends DomainException implements
    ContainerException,
    ExceptionInterface
{
}
