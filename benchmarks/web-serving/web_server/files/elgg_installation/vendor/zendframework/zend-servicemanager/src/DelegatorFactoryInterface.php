<?php
/**
 * @link      http://github.com/zendframework/zend-servicemanager for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager;

/**
 * Backwards-compatibility shim for DelegatorFactoryInterface.
 *
 * Implementations should update to implement only Zend\ServiceManager\Factory\DelegatorFactoryInterface.
 *
 * If upgrading from v2, take the following steps:
 *
 * - rename the method `createDelegatorWithName()` to `__invoke()`, and:
 *   - rename the `$serviceLocator` argument to `$container`, and change the
 *     typehint to `Interop\Container\ContainerInterface`
 *   - merge the `$name` and `$requestedName` arguments
 *   - add the `callable` typehint to the `$callback` argument
 *   - add the optional `array $options = null` argument as a final argument
 * - create a `createDelegatorWithName()` method as defined in this interface, and have it
 *   proxy to `__invoke()`, passing `$requestedName` as the second argument.
 *
 * Once you have tested your code, you can then update your class to only implement
 * Zend\ServiceManager\Factory\DelegatorFactoryInterface, and remove the `createDelegatorWithName()`
 * method.
 *
 * @deprecated Use Zend\ServiceManager\Factory\DelegatorFactoryInterface instead.
 */
interface DelegatorFactoryInterface extends Factory\DelegatorFactoryInterface
{
    /**
     * A factory that creates delegates of a given service
     *
     * @param ServiceLocatorInterface $serviceLocator the service locator which requested the service
     * @param string                  $name           the normalized service name
     * @param string                  $requestedName  the requested service name
     * @param callable                $callback       the callback that is responsible for creating the service
     *
     * @return mixed
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback);
}
