<?php

/**
 * This file contains makes it possible to manage sessions for the event push server
 *
 * @package    BZiON\Session
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

/**
 * A session handler that uses the MySQL database to store session data
 * @package BZiON\Session
 */

namespace BZIon\Session;

class DatabaseSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var \Database The database instance
     */
    private $database;

    /**
     * @var bool Whether gc() has been called
     */
    private $gcCalled = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->database = \Database::getInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        $this->gcCalled = false;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        // We need to make sure we do not return session data that is already considered garbage according
        // to the session.gc_maxlifetime setting because gc() is called after read() and only sometimes
        $results = $this->database->query("SELECT data FROM sessions WHERE id = ? AND timestamp > ? LIMIT 1",
            array($sessionId, $this->getOldestTimestamp()));

        if (isset($results[0]) && isset($results[0]['data'])) {
            return base64_decode($results[0]['data']);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        // We delay gc() to close() so that it is executed outside the transactional and blocking read-write process.
        // This way, pruning expired sessions does not block them from being started while the current session is used.
        $this->gcCalled = true;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        // delete the record associated with this id
        $this->database->execute("DELETE FROM sessions WHERE id = ?", $sessionId);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        $encoded = base64_encode($data);

        $this->database->execute(
            "INSERT INTO sessions (id, data, timestamp) VALUES (?, ?, UTC_TIMESTAMP())
             ON DUPLICATE KEY UPDATE data = ?, timestamp = UTC_TIMESTAMP();",
            array($sessionId, $encoded, $encoded));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->gcCalled) {
            // Delete the session records that have expired
            $this->database->execute("DELETE FROM session WHERE timestamp <= ?",
                $this->getOldestTimestamp());
        }

        return true;
    }

    /**
     * Make sure that we are not using expired sessions
     * @return string The oldest timestamp that is not considered expired, in a
     *                MySQL-readable format
     */
    private function getOldestTimestamp()
    {
        $maxLifetime = (int) ini_get('session.gc_maxlifetime');
        $oldestTimestamp = \TimeDate::now()->subSeconds($maxLifetime);

        return $oldestTimestamp->toMysql();
    }
}
