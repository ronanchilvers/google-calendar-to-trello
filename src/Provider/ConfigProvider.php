<?php

namespace App\Provider;

use Noodlehaus\Config;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
* Config provider
*
* @author Ronan Chilvers <ronan@d3r.com>
*/
class ConfigProvider implements ServiceProviderInterface
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function register(Container $pimple)
    {
        $pimple['app.config'] = function () use ($pimple) {
            $cwd    = $pimple['app.cwd'];
            $config = new Config([
                $cwd . '/dist/config.yaml',
                $cwd . '/config.yaml'
            ]);

            return $config;
        };
    }
}
