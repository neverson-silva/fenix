<?php

namespace Fenix\Orm;

class Manager
{
    /**
     * @var Connection
     */
    private $connection;

    private static $connectionStatic;

    /**
     * Manager constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        static::$connectionStatic = $connection;
    }

    /**
     * Call connection methods dinamically
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
       return (new static(static::$connectionStatic))->getConnection()->{$name}(...$arguments);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Connect to database
     * @param array $config
     */
    public function connectToDatabase(array $config)
    {
        if (!$this->connection->isConnected()) {
            $this->connection->connect($config);
        }
    }
}