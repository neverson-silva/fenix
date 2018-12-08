<?php

namespace Fenix\Orm;

use PDOStatement;
use PDOException;
use PDO;

/**
 * Connect to databases
 *
 * @license MIT
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 */
class Connection
{
    private static $pdo;

    protected $fetchMode = PDO::FETCH_OBJ;

    protected $grammar;

    public function __construct(Grammar $grammar, array $config = [])
    {
        $this->grammar = $grammar;
        if (!empty($config)) {
            $this->connect($config);
        }
    }

    /**
     * @return Connection
     */
    public static function newConnection()
    {
        return new static(new Grammars\Grammar());
    }

    /**
     * Connect to database
     *
     * @param array $config
     * 
     * @return Connection
     */
    public function connect(array $config)
    {
        if (empty($config)) return;

        if (!static::$pdo) {
            $dns = sprintf(
                '%s:host=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['database'],
                $config['charset']
            );
            try {
                self::$pdo = new PDO($dns, $config['username'], $config['password'], $config['options']);
            } catch (PDOException $e) {
                exit(utf8_encode($e->getMessage()));
            }
        }
        $this->setDefaultFetchMode();
        return $this;
    }

    /**
     * Get current connection
     *
     * @return PDO
     */
    public function getPdo()
    {
        return static::$pdo;
    }

    /**
     * Get Grammar
     *
     * @return Grammar
     */
    public function getGrammar() : \Fenix\Orm\Grammars\Grammar
    {
        return $this->grammar;
    }

    /**
     * Returns a new builder
     *
     * @return void
     */
    public function newBuilder($model) : Builder
    {
        return new Builder($this, $this->getGrammar(), $model);
    }

    /**
     * Add attributes to PDO connection
     *
     * @param int $attribute
     * @param mixed $value
     * @return Connection
     */
    public function addAttribute($attribute, $value)
    {
        static::$pdo->setAttribute($attribute, $value);

        return $this;
    }

    /**
     * Set default fetch mode
     *
     * @return Connection
     */
    public function setDefaultFetchMode()
    {
        return $this->addAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->fetchMode);
    }

    /**
     * Begin fluent query
     *
     * @param $table
     * @return Builder
     */
    public function table($table, $model = null)
    {
        return $this->newBuilder($model)
                    ->from($table);
    }


    /**
     * Run a select on database
     *
     * @param string $query
     * @param array $parameters
     * @throws PDOException
     * @return mixed
     */
    public function select($query, $parameters)
    {

        $statement = $this->run($query, $parameters, true);

        $this->bindValues($statement, $parameters);
        try {
            $statement->execute();

        } catch (\Throwable $exception) {
            throw $exception;
        }

        $results = $statement->fetchAll();

        $statement->closeCursor();

        return $results;
    }

    /**
     * Run a delete on database
     *
     * @param string $query
     * @param $parameters
     * @throws PDOException
     * @return int
     */
    public function delete(string $query, $parameters)
    {
        $statement = $this->run($query, $parameters);

        return $this->affectedRows($statement, [$query, $parameters]);
    }

    /**
     * Run a update on database
     *
     * @param string $query
     * @param array $parameters
     * @throws PDOException
     * @return int
     */
    public function update(string $query, $parameters)
    {
        $statement = $this->run($query, $parameters);

        return $this->affectedRows($statement, [$query, $parameters]);
    }

    /**
     * Run a update on database
     *
     * @param string $query
     * @param array $parameters
     * @throws PDOException
     * @return int
     */
    public function insert(string $query, $parameters)
    {
        $statement = $this->run($query, $parameters);

        return $this->affectedRows($statement, [$query, $parameters]);
    }

    /**
     * Get the number of affected rows by statment
     *
     * @param PDOStatement $statement
     * @return void
     */
    protected function affectedRows(PDOStatement $statement, $values)
    {
        [$query, $parameters] = $values;
        try {

            $statement->execute();

            $this->commit();

            $statement->closeCursor();

            return $statement->rowCount();

        } catch (PDOException $e) {

            $this->rollBack();

            $message = json_encode(compact('query', 'parameters'));

            throw new PDOException('Error ' . $e->getMessage() .' ' . $message);
        }

    }

    /**
     * Prepare and bind values to statment before do anything
     *
     * @param string $query
     * @param array $parameters
     * @return PDOStatement
     */
    protected function run($query, $parameters, $select = false)
    {
        try {

            if (!$select) {
                $this->beginTransaction();
            }


            $statement = $this->prepared($query);

            $this->bindValues($statement, $parameters); 

        } catch (PDOException $e) {

            $message = json_encode(compact('query', 'parameters'));

            throw new PDOException('Error ' . $e->getMessage() .' ' . $message);
        }

        return $statement;
    }

    /**
     * Prepare statement
     *
     * @param string $query
     * @return PDOStatement
     */
    public function prepared(string $query) : PDOStatement
    {
        return $this->getPdo()->prepare($query);
    }

    /**
     * Begin transaction
     *
     * @return Connection
     */
    public function beginTransaction()
    {
        $this->getPdo()->beginTransaction();

        return $this;
    }

    /**
     * Commit transaction
     *
     * @return Connection
     */
    public function commit()
    {
        $this->getPdo()->commit();

        return $this;
    }

    /**
     * Rollback transaction
     *
     * @return Connection
     */
    public function rollBack()
    {
        $this->getPdo()->rollBack();

        return $this;
    }

    /**
     * Bind values
     *
     * @param \PDOStatement $statement
     * @param array $parameters
     * @return void
     */
    public function bindValues(PDOStatement &$statement, $parameters)
    {
        foreach($parameters as $key => $value) {
            $statement->bindValue(
                $key + 1, $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * Verify is if connected
     * @return bool
     */
    public function isConnected()
    {
        return $this->getPdo() instanceof PDO;
    }

}

