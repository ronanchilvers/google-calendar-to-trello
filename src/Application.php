<?php

namespace App;

use App\Interfaces\ContainerAwareInterface;
use App\Traits\ContainerAwareTrait;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;

/**
* Application class
*
* @author Ronan Chilvers <ronan@d3r.com>
*/
class Application extends SymfonyApplication
{
    use ContainerAwareTrait;

    /**
     * Class constructor
     *
     * Overridden to allow injection of the container
     *
     * @param  Pimple\Container $pimple
     * @param  string $name
     * @param  string $version
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct($pimple, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->setContainer($pimple);
        parent::__construct($name, $version);
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function add(Command $command)
    {
        if ($command instanceof ContainerAwareInterface) {
            $command->setContainer($this->getContainer());
        }

        return parent::add($command);
    }
}
