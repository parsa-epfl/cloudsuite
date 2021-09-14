<?php
/**
 * @link      http://github.com/zendframework/zend-servicemanager for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager;

use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\InvalidServiceException;

/**
 * Interface for a plugin manager
 *
 * A plugin manager is a specialized service locator used to create homogeneous objects
 */
interface PluginManagerInterface extends ServiceLocatorInterface
{
    /**
     * Validate an instance
     *
     * @param  object $instance
     * @return void
     * @throws InvalidServiceException If created instance does not respect the
     *     constraint on type imposed by the plugin manager
     * @throws ContainerException if any other error occurs
     */
    public function validate($instance);
}
