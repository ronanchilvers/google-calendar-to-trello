<?php

namespace App\Traits;

use Pimple\Container;

/**
 * Trait for objects that have the container as a dependency
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait ContainerAwareTrait
{
    /**
     * The container object
     *
     * @var Pimple\Container
     */
    private $container;

    /**
     * Set the container object
     *
     * @param  Pimple\Container $pimple
     * @return Pimple\Container
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setContainer(Container $pimple)
    {
        $this->container = $pimple;

        return $this->container;
    }

    /**
     * Get the container object
     *
     * @return Pimple\Container
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getContainer()
    {
        return $this->container;
    }
}
