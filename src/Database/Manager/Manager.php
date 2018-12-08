<?php

namespace Fenix\Database\Manager;

use Fenix\Database\Domain\QueryBuilder;
use Fenix\Database\Collection\Collection;
use InvalidArgumentException;
use BadMethodCallException;
use PDOException;

/**
 * Database Manager
 * @package Fenix
 * @subpackage Database\Manager
 * @license MIT
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 */

class Manager
{
        /**
     * A query builder
     *
     * @var QueryBuilder
     */
    private $query;
    
    /**
     * Static connection to database
     *
     * @var \PDO
     */
    private static $connector;
    
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct()
    {
        $this->refreshQueryBuilder();
    }

    public function refreshQueryBuilder()
    {
        if (!$this->query instanceof QueryBuilder) {
            $this->query = new QueryBuilder();
        }
    }

      /**
     * Connect to database
     * @param array $config PDO configurations
     */
    public function connectToDatabase(array $config)
    {
        
        $this->pdo = Connector::getConnection($config);
        
    }

    /**
     * Make the connection globally available
     * @return void
     */
    public function makeGlobal()
    {
        static::$connector = $this->pdo;
        
        unset($this->pdo);
        
    }

    /**
     * Create a new instance from a static context and set the table's name.
     *
     * @param string $table Table's name
     * @return static
     */
    public static function table($table)
    {
        $new = new static();
        
        $new->setTable($table);
        
        return $new;
    }

    /**
     * Set table name
     *
     * @param string $table Table's name
     * @return $this
     */
    public function setTable($tableName)
    {
        $this->refreshQueryBuilder();
        $this->query->setTable($tableName);
        return $this;
    }

    public function static()
    {
        return (new \ReflectionClass($this))->getStaticProperties();
    }

    /**
     * Dinamically calls the query builder to form a query string
     *
     * @param string $method The name of the method
     * @param array $args
     * @return Fenix\Database\Manager
     */
    public function __call($method, $args)
    {
        if (!method_exists($this->query, $method)) {
            $message = get_class($this) . '::' . $method;
            throw new BadMethodCallException("Method $message doesn't exist.");
        }
        $this->query = $this->query->$method(...$args);
        $new = clone $this;
        if ($method === 'insert' || $method === 'update' || $method === 'delete')  {
            return $new->executeStatement();
        }
        return $new;
    }

    /**
     *
     *@throws \InvalidArgumentException Case not query is builded yet.
     * @return Collection
     */
    public function get()
    {
        if ($this->query->isEmpty()) {
            throw new InvalidArgumentException("To get the results you need to build a query first.");
        }
        $execution = $this->query->getValues();
        $this->query->load();
        if (is_null(static::$connector)) {
            throw new \PDOException('No connection available. A global connection must be available.');
        }
        try {
            $statment = static::$connector->prepare($execution['query']);
            $statment->execute(array_values($execution['params']));
        } catch (PDOException $exception) {
            throw  $exception;
        }

        return new Collection($statment->fetchAll());
    }

    public function executeStatement()
    {
        $this->refreshQueryBuilder();

        if ($this->query->isEmpty()) {
            throw new InvalidArgumentException("To execute a statment you need to build a query first.");
        }
        
        if (is_null(static::$connector)) {
            throw new \PDOException('No connection available. A global connection must be available.');
        }
        $execution = $this->query->getValues();

        $this->query->load();
        try {
            static::$connector->beginTransaction();
            $statment = static::$connector->prepare($execution['query']);
            $result = $statment->execute(array_values($execution['params']));
            $statment->closeCursor();
            if ($this->query->getStatement() == $this->query::INSERT) {                
                $id = static::$connector->lastInsertId();
                static::$connector->commit();
                return $id;
            }
            static::$connector->commit();
            return $result;
        } catch (PDOException $e) {
            static::$connector->rollback();
            throw new PDOException($e->getMessage() . ". [{$execution['query']}]");
        }
    }

    /**
     * Verifica se o method existe no Query Builder
     *
     * @param string $method Method name
     * @return bool
     */
    public function methodExists($method)
    {
        if (!$this->query->isQuery($method) && !\method_exists($this, $method)) {
            return false;
        }
        return true;
    }

    /**
     * Call an stored procedure from database
     * @param string $procedure
     * @param mixed $params
     * @return bool|mixed
     */
    public function callProcedure(string $procedure, $params = null)
    {
        if (!is_null($params)) {
            $query = sprintf('CALL %s(?)', $procedure);
            $stm = static::$connector->prepare($query);
            foreach ($params as $key => $param) {
                $stm->bindParam($key + 1, $param);
            }
            return $stm->execute();
        } else {
            $query = sprintf('CALL %s', $procedure);
            $stm = static::$connector->prepare($query);
            return $stm->execute();
        }
    }

    public function rawQuery($query)
    {
        $statment = static::$connector->prepare($query);
        $statment->execute();
        return new Collection($statment->fetchAll());

    }
}
