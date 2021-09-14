<?php
/**
 * @see       https://github.com/zendframework/zend-servicemanager for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Zend\ServiceManager;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * @internal for use in abstract plugin manager
 */
final class PsrContainerDecorator implements ContainerInterface
{
    /**
     * @var PsrContainerInterface
     */
    private $container;

    public function __construct(PsrContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * @return PsrContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
