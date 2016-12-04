<?php

namespace App\Provider;

use Exception;
use Google_Client;
use Google_Service_Calendar;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
* Google API client provider
*/
class GoogleProvider implements ServiceProviderInterface
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function register(Container $pimple)
    {
        $pimple['api.google'] = function () use ($pimple) {

            $config = $pimple['app.config'];
            $cwd    = $pimple['app.cwd'];
            $scopes = implode(' ', [
                Google_Service_Calendar::CALENDAR_READONLY
            ]);

            $secret = $cwd . $config->get('google.secret');
            $token  = $cwd . $config->get('google.token');

            $client = new Google_Client();
            $client->setApplicationName($pimple['app.name']);
            $client->setScopes($scopes);
            $client->setAccessType('offline');
            $client->setAuthConfig($secret);

            if (!file_exists($token)) {
                $output  = new ConsoleOutput();
                $authUrl = $client->createAuthUrl();
                $output->writeln("Open the following link in your browser:");
                $output->writeln($authUrl);

                $output->writeln('Enter verification code: ');
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

                // Store the credentials to disk.
                if (!file_exists(dirname($token))) {
                    mkdir(dirname($token), 0700, true);
                }
                file_put_contents($token, json_encode($accessToken));
                $output->writeln("Credentials saved to {$token}\n");
            }
            $accessToken = json_decode(file_get_contents($token), true);
            $client->setAccessToken($accessToken);
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                if (!file_put_contents($token, json_encode($client->getAccessToken()))) {
                    throw new Exception('Unable to refresh access token');
                }
            }

            return $client;
        };

        $pimple['api.calendar'] = function () use ($pimple) {
            $api = new Google_Service_Calendar($pimple['api.google']);

            return $api;
        };
    }
}
