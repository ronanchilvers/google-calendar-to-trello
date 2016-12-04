<?php

namespace App\Traits;

use Pimple\Container;

trait ContainerAwareTrait
{
    /**
     * The container object
     *
     * @var Pimple\Container
     */
    protected $container;

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
