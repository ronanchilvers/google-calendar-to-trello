<?php

namespace App\Provider;

use App\Storage\Helper;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
* Storage provider
*
* @author Ronan Chilvers <ronan@d3r.com>
*/
class StorageProvider implements ServiceProviderInterface
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function register(Container $pimple)
    {
        $pimple['app.storage'] = function () {
            return new Helper();
        };
    }
}
