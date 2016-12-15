<?php

namespace App\Traits;

use Noodlehaus\Config;

/**
 * Trait for objects that have the config object as a dependency
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait ConfigAwareTrait
{
    /**
     * The injected config object
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    private $config;

    /**
     * Set the config object
     *
     * @param  Noodlehaus\Config $value
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setConfig(Config $value)
    {
        $this->config = $value;
    }

    /**
     * Get the config object
     *
     * @return Config
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getConfig()
    {
        return $this->config;
    }
}
