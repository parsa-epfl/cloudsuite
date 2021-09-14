<?php
/**
 * @link      http://github.com/zendframework/zend-servicemanager for the canonical source repository
 * @copyright Copyright (c) 2015-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager\Initializer;

use Interop\Container\ContainerInterface;

/**
 * Interface for an initializer
 *
 * An initializer can be registered to a service locator, and are run after an instance is created
 * to inject additional dependencies through setters
 */
interface InitializerInterface
{
    /**
     * Initialize the given instance
     *
     * @param  ContainerInterface $container
     * @param  object             $instance
     * @return void
     */
    public function __invoke(ContainerInterface $container, $instance);
}
