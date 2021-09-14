<?php
/**
 * @link      http://github.com/zendframework/zend-servicemanager for the canonical source repository
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ServiceManager\AbstractFactory;

use ArrayObject;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

final class ConfigAbstractFactory implements AbstractFactoryInterface
{

    /**
     * Factory can create the service if there is a key for it in the config
     *
     * {@inheritdoc}
     */
    public function canCreate(\Interop\Container\ContainerInterface $container, $requestedName)
    {
        if (! $container->has('config') || ! array_key_exists(self::class, $container->get('config'))) {
            return false;
        }
        $config = $container->get('config');
        $dependencies = $config[self::class];

        return is_array($dependencies) && array_key_exists($requestedName, $dependencies);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
    {
        if (! $container->has('config')) {
            throw new ServiceNotCreatedException('Cannot find a config array in the container');
        }

        $config = $container->get('config');

        if (! (is_array($config) || $config instanceof ArrayObject)) {
            throw new ServiceNotCreatedException('Config must be an array or an instance of ArrayObject');
        }

        if (! array_key_exists(self::class, $config)) {
            throw new ServiceNotCreatedException('Cannot find a `' . self::class . '` key in the config array');
        }

        $dependencies = $config[self::class];

        if (! is_array($dependencies)
            || ! array_key_exists($requestedName, $dependencies)
            || ! is_array($dependencies[$requestedName])
        ) {
            throw new ServiceNotCreatedException('Dependencies config must exist and be an array');
        }

        $serviceDependencies = $dependencies[$requestedName];

        if ($serviceDependencies !== array_values(array_map('strval', $serviceDependencies))) {
            $problem = json_encode(array_map('gettype', $serviceDependencies));
            throw new ServiceNotCreatedException('Service message must be an array of strings, ' . $problem . ' given');
        }

        $arguments = array_map([$container, 'get'], $serviceDependencies);

        return new $requestedName(...$arguments);
    }
}
