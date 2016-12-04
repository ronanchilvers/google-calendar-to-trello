<?php

namespace App\Interfaces;

use Pimple\Container;

/**
 * Interface for objects that are container aware
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
interface ContainerAwareInterface
{
    /**
     * Set the container into this object
     *
     * @param  Pimple\Container $pimple
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setContainer(Container $pimple);
}
