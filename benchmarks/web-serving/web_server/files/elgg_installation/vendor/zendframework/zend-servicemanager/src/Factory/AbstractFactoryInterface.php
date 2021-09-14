<?php
/**
 * @link      http://github.com/zendframework/zend-servicemanager for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager\Factory;

use Interop\Container\ContainerInterface;

/**
 * Interface for an abstract factory.
 *
 * An abstract factory extends the factory interface, but also has an
 * additional "canCreate" method, which is called to check if the abstract
 * factory has the ability to create an instance for the given service. You
 * should limit the number of abstract factories to ensure good performance.
 * Starting from ServiceManager v3, remember that you can also attach multiple
 * names to the same factory, which reduces the need for abstract factories.
 */
interface AbstractFactoryInterface extends FactoryInterface
{
    /**
     * Can the factory create an instance for the service?
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName);
}
