<?php

namespace App\Storage;

use App\Traits\ConfigAwareTrait;
use Noodlehaus\Config;
use SQLite3;

/**
* Storage Helper
*
* This class is responsible for allowing access to simple local data storage. It is primarily used to
* work out if we've seen a given event before.
*
* @author Ronan Chilvers <ronan@d3r.com>
*/
class Helper
{
    /**
     * The path to the db file
     *
     * @var string
     */
    protected $path;

    /**
     * The instantiated connection object
     *
     * @var SQLite3
     */
    protected $connection;

    /**
     * Class constructor
     *
     * @param  The path to the database file
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Class destructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __destruct()
    {
        if ($this->connection instanceof SQLite3) {
            $this->connection->close();
        }
    }

    /**
     * Get the database connection
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function connection()
    {
        if (!$this->connection instanceof SQLite3) {
            $this->connection = new SQLite3($this->path);
        }

        return $this->connection;
    }
}
