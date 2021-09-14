<?php
/**
 * @link      http://github.com/zendframework/zend-servicemanager for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager;

interface ConfigInterface
{
    /**
     * Configure a service manager.
     *
     * Implementations should pull configuration from somewhere (typically
     * local properties) and pass it to a ServiceManager's withConfig() method,
     * returning a new instance.
     *
     * @param ServiceManager $serviceManager
     * @return ServiceManager
     */
    public function configureServiceManager(ServiceManager $serviceManager);

    /**
     * Return configuration for a service manager instance as an array.
     *
     * Implementations MUST return an array compatible with ServiceManager::configure,
     * containing one or more of the following keys:
     *
     * - abstract_factories
     * - aliases
     * - delegators
     * - factories
     * - initializers
     * - invokables
     * - lazy_services
     * - services
     * - shared
     *
     * In other words, this should return configuration that can be used to instantiate
     * a service manager or plugin manager, or pass to its `withConfig()` method.
     *
     * @return array
     */
    public function toArray();
}
