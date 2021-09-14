<?php
/**
 * @link      http://github.com/zendframework/zend-servicemanager for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager;

/**
 * Backwards-compatibility shim for InitializerInterface.
 *
 * Implementations should update to implement only Zend\ServiceManager\Initializer\InitializerInterface.
 *
 * If upgrading from v2, take the following steps:
 *
 * - rename the method `initialize()` to `__invoke()`, and:
 *   - rename the `$serviceLocator` argument to `$container`, and change the
 *     typehint to `Interop\Container\ContainerInterface`
 *   - swap the order of the arguments (so that `$instance` comes second)
 * - create an `initialize()` method as defined in this interface, and have it
 *   proxy to `__invoke()`, passing the arguments in the new order.
 *
 * Once you have tested your code, you can then update your class to only implement
 * Zend\ServiceManager\Initializer\InitializerInterface, and remove the `initialize()`
 * method.
 *
 * @deprecated Use Zend\ServiceManager\Initializer\InitializerInterface instead.
 */
interface InitializerInterface extends Initializer\InitializerInterface
{
    /**
     * Initialize
     *
     * @param $instance
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator);
}
