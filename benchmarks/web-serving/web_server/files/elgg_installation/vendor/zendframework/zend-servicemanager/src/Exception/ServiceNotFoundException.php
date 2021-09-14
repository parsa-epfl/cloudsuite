<?php
/**
 * @link      http://github.com/zendframework/zend-servicemanager for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager\Exception;

use Interop\Container\Exception\NotFoundException;
use InvalidArgumentException as SplInvalidArgumentException;

/**
 * This exception is thrown when the service locator do not manage to find a
 * valid factory to create a service
 */
class ServiceNotFoundException extends SplInvalidArgumentException implements
    ExceptionInterface,
    NotFoundException
{
}
