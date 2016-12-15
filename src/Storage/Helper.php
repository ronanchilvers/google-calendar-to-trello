<?php

namespace App\Storage;

use App\Traits\ConfigAwareTrait;
use Carbon\Carbon;
use Google_Service_Calendar_Event;
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
     * Store an event with its associated card
     *
     * @param  Google_Service_Calendar_Event $event
     * @param  array $card
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function storeEventCard(Google_Service_Calendar_Event $event, array $card)
    {
        $sql = "
            INSERT INTO event_cards (
                event_google_id,
                event_card_id,
                event_created
            ) VALUES (
                :google_id,
                :card_id,
                :created
            )
        ";
        $connection = $this->connection();
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('google_id', $event->getId());
        $stmt->bindValue('card_id', $card['id']);
        $stmt->bindValue('created', Carbon::now()->format('Y-m-d H:i:s'));
        if (!$stmt->execute()) {
            return false;
        }

        return true;
    }

    /**
     * Check if a given event already has a card entry
     *
     * @param  Google_Service_Calendar_Event $event
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function eventExists(Google_Service_Calendar_Event $event)
    {
        $sql = "SELECT COUNT(*) AS event_count FROM event_cards WHERE event_google_id = :google_id";
        $connection = $this->connection();
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('google_id', $event->getId());
        $result = $stmt->execute();
        $data = $result->fetchArray();
        if (0 < $data['event_count']) {
            return true;
        }

        return false;
    }

    /**
     * Initialize the database schema
     *
     * This method should be idempotent
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function initializeSchema()
    {
        $queries   = [];
        $queries[] = "
            CREATE TABLE IF NOT EXISTS event_cards (
                event_id INTEGER PRIMARY KEY,
                event_google_id VARCHAR(128),
                event_card_id VARCHAR(64),
                event_created DATETIME,
                event_updated DATETIME
            )
        ";
        $queries[] = "
            CREATE INDEX IF NOT EXISTS event_cards_google_id ON event_cards (event_google_id)
        ";
        $queries[] = "
            CREATE INDEX IF NOT EXISTS event_cards_card_id ON event_cards (event_card_id)
        ";
        $connection = $this->connection();
        foreach ($queries as $query) {
            if (!$connection->exec($query)) {
                throw new Exception('Unable to execute query : ' . $query);
            }
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
            $this->initializeSchema();
        }

        return $this->connection;
    }
}
