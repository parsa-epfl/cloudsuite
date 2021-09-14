<?php
/**
 * @link      http://github.com/zendframework/zend-servicemanager for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Interop\Container\ContainerInterface as InteropContainerInterface;

/**
 * Interface for service locator
 */
interface ServiceLocatorInterface extends
    PsrContainerInterface,
    InteropContainerInterface
{
    /**
     * Build a service by its name, using optional options (such services are NEVER cached).
     *
     * @param  string $name
     * @param  null|array  $options
     * @return mixed
     * @throws Exception\ServiceNotFoundException If no factory/abstract
     *     factory could be found to create the instance.
     * @throws Exception\ServiceNotCreatedException If factory/delegator fails
     *     to create the instance.
     * @throws ContainerExceptionInterface if any other error occurs
     */
    public function build($name, array $options = null);
}
