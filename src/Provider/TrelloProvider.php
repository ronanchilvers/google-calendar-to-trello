<?php

namespace App\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Trello\Client;

/**
* Trello container provider
*
* @author Ronan Chilvers <ronan@d3r.com>
*/
class TrelloProvider implements ServiceProviderInterface
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function register(Container $pimple)
    {
        $pimple['api.trello'] = function () use ($pimple) {
            $config = $pimple['app.config'];
            $client = new Client();
            $client->authenticate(
                $config->get('trello.key'),
                $config->get('trello.token'),
                Client::AUTH_URL_CLIENT_ID
            );

            return $client;
        };
    }
}
