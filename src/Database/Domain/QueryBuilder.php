<?php

namespace Fenix\Database\Domain;

use Fenix\Database\Collection\Collection;
use Fenix\Traits\Database\Query;
use InvalidArgumentException;

/**
 * The QueryBuilder
 *
 * Class responsible for build queries to used it
 * @package Fenix
 * @subpackage Database\Builder
 * @license MIT
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 */
class QueryBuilder
{
    use Query;

    const SELECT = 1;
    
    const UPDATE = 2;
    
    const DELETE = 3;
    
    const INSERT = 4;

     /**
     * Parameters to be used in a statment
     *
     * @var array|Collection
     */
    public $parameters = [
        'constraints' => '',
        'update' => '',
        'insert' => ''
    ];
    
    /**
     * Query string
     *
     * @var string
     */
    private $query;
    
    /**
     * Statment to be executed
     *
     * @var int
     */
    private $statement;
    
    /**
     * Constraints to where cause
     *
     * @var array|Collection
     */
    private $constraints = [
        'columns' => null,
        'table' => null,
        'join' => null,
        'where' => null,
        'limit' => null,
        'order' => null,
        'group' => null,
        'conditions' => null
    ];
    
    /**
     * The statments supported
     * @var array
     */
    private $skeletons = [
        self::SELECT => 'SELECT %s FROM %s %s',
        self::UPDATE => 'UPDATE %s SET %s %s',
        self::DELETE => 'DELETE FROM %s %s',
        self::INSERT => 'INSERT INTO %s (`%s`) VALUES (%s)',
    ];

    public function __construct()
    {
        $this->load();
    }

    /**
     * Load the initial state of the object
     *
     * @return void
     */
    public function load()
    {
 
        $this->constraints = new Collection($this->constraints);
        $this->constraints->put('join', new Collection());
        foreach ($this->parameters as &$parameters){
            $parameters = new Collection();
        }
        $this->query = '';
    }

     /**
     * Retrieve records from database passing what columns we want as parameter
     *
     * @param ...$columns | Columns name
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function select(...$columns)
    {
        $this->setStatement(self::SELECT);
        if (empty($columns)) {
            $columns = ['*'];
        }
        return $this->setColumns($columns)->assembleQuery();
    }

    /**
     * Where condition
     *
     * @param  ...$conditions | Conditions to form where clause
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function where(...$conditions)
    {
        if ($this->constraints->get('columns') == null) {
            $this->constraints->put('columns', $this->withTable('*'));
        }
        $conditions = $this->getConditions($conditions);

        $clause = sprintf('WHERE %s %s ?', $conditions['column'], $conditions['operator']);
        
        $key = sprintf('%s@%s', $conditions['column'], $conditions['value']);

        return $this->setStatement(self::SELECT)
                    ->setParameters($conditions['value'], 'constraints', $key)
                    ->constraints('where', $clause)
                    ->assembleQuery();


    }

    
    /**
     * Where is null clause
     *
     * Build a where clause with column that must be null in database.
     *
     * @param string $column Column name that has null value in database
     *
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function whereNull($column)
    {
        return $this->setStatement(self::SELECT)
                    ->constraints('where', sprintf('WHERE %s IS NULL', $this->withTable($column)))
                    ->assembleQuery();
    }

    /**
     * Where is not null clause
     *
     * Build a where clause with column that must not be null in database.
     *
     * @param string $column Column name that has not null value in database
     *
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function whereNotNull($column)
    {
        return $this->setStatement(self::SELECT)
                    ->constraints('where', sprintf('WHERE %s IS NOT NULL', $this->withTable($column)))
                    ->assembleQuery();
    }


     /**
     * The in method verifies that a given column's value is contained within the given array
     *
     * @param string $statement
     * @param string $column  The column name that will be used in where clause
     * @param mixed ...$data   The data the will be filtrated
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function in($statement, $column, ...$data)
    {
        return $this->setStatement(self::SELECT)
                    ->format('IN', $column, $data, $statement)
                    ->assembleQuery();
    }

    /**
     * The notIn method verifies that the given column's value is not
     * contained in the given array
     * @param string $statement
     * @param string $column   The column name that will be used in where clause
     * @param mixed ...$data   The data the will be filtrated
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function notIn($statement, $column, ...$data)
    {
        return $this->setStatement(self::SELECT)
                    ->format('NOT IN', $column, $data, $statement)
                    ->assembleQuery();
    }

    /**
     * between clause
     * This method verifies that a column's value is between two values:
     *
     * @param string $statement 
     * @param string $column Column name
     * @param mixed $first First condition value
     * @param mixed $second Second condition value
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function between($statement, $column, $first, $second)
    {
        return $this->setStatement(self::SELECT)
                    ->format(['BETWEEN', 'AND'], $column, compact('first', 'second'), $statement)
                    ->assembleQuery();
    }

     /**
     * not between clause
     * This method verifies that a column's value lies outside of two values:
     *
     * @param string $column Column name
     * @param mixed $first First condition value
     * @param mixed $second Second condition value
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function notBetween($statement, $column, $first, $second)
    {
        return $this->setStatement(self::SELECT)
                    ->format(['NOT BETWEEN', 'AND'], $column, compact('first', 'second'), $statement)
                    ->assembleQuery();
    }

    /**
     * Query and condition
     *
     * Add "and" clause to current query string.
     *
     * @param array ...$and Parameters to use in the query.
     * @throws InvalidArgumentException In cause where constraint in the property $constraint is empty
     *                                   Because to use and it is necessary have and WHERE first.
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function and(...$conditions)
    {
        $conditions = $this->getConditions($conditions);

        $clause = sprintf('AND %s %s ?', $conditions['column'], $conditions['operator']);
        
        $key = sprintf('%s@%s', $conditions['column'], $conditions['value']);

        return $this->setStatement(self::SELECT)
                    ->setParameters($conditions['value'], 'constraints', $key)
                    ->constraints('and', $clause)
                    ->assembleQuery();
    }

     /**
     * Query or condition
     *
     * Add "or" clause to current query string.
     *
     * @param array ...$or Parameters to use in the query.
     * @throws InvalidArgumentException In cause where constraint in the property $constraint is empty
     *                                   Because to use and it is necessary have and WHERE first.
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function or(...$conditions)
    {
        $conditions = $this->getConditions($conditions);
        $clause = sprintf('OR %s %s ?', $conditions['column'], $conditions['operator']);
        $key = sprintf('%s@%s', $conditions['column'], $conditions['value']);
        return $this->setStatement(self::SELECT)
                    ->setParameters($conditions['value'], 'constraints', $key)
                    ->constraints('or', $clause)
                    ->assembleQuery();

    }

     /**
     * Limit number of rows the query will return
     *
     * @param int $rows Number of max rows
     *
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function rows(int $rows)
    {
        $clause = "LIMIT $rows";
        
        if (!$this->constraints->empty('conditions')) {
            $clause = $this->constraints->get('conditions') . ' ' .$clause;
        }
        $this->constraints->put('conditions', $clause);
        return $this->assembleQuery();
    }

     /**
     * Contrainst a order by
     * @param string|array $columns Columns to be parsed in a columns string query
     *                              you a simple string with the column name or an
     *                              array of strings.
     *
     * @param string $order         Type to be ordered ASC or DESC
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function orderBy($columns, $order = 'DESC')
    {  
        $columns = $this->colunize(is_array($columns) ? $columns : [$columns]);

        $clause = sprintf("ORDER BY %s %s", $columns, strtoupper($order));
       
        return $this->setStatement(self::SELECT)
                    ->constraints('order', $clause)
                    ->assembleQuery();
    }

    public function groupBy(...$columns)
    {
        $columns = $this->colunize($columns);

        $clause = sprintf('GROUP BY %s', $columns);
       
        return $this->setStatement(self::SELECT)
                    ->constraints('group', $clause)
                    ->assembleQuery();
    }

    /**
     * Turn an arary of values into query string
     *
     * @param array $columns
     * @return string
     */
    protected function colunize(array $columns) 
    {
        $columns = array_map(function($column){
            return $this->withTable($column);
        }, is_array($columns) ? $columns : [$columns]);
        return implode(', ', $columns);
    }

    /**
     * Inner join
     *
     * Get a inner join
     *
     * @param string $table Name of the table to be joined.
     * @param string $primaryKey Primary key from base table.
     * @param string $operator Operator
     * @param string $foreignKey Foreign key
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function join(string $table, string $primaryKey, string $operator, string $foreignKey)
    {
        return $this->handleJoin($table, $primaryKey, $operator, $foreignKey, 'INNER')->assembleQuery();
    }

     /**
     * Left join
     *
     * Get a Left join
     *
     * @param string $table Name of the table to be joined.
     * @param string $primaryKey Primary key from base table.
     * @param string $operator Operator
     * @param string $foreignKey Foreign key
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function leftJoin($table, $primaryKey, $operator, $foreignKey)
    {
        return $this->handleJoin($table, $primaryKey, $operator, $foreignKey, 'LEFT OUTER')->assembleQuery();
    }

    /**
     * Right join
     *
     * Get a right join
     *
     * @param string $table Name of the table to be joined.
     * @param string $primaryKey Primary key from base table.
     * @param string $operator Operator
     * @param string $foreignKey Foreign key
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function rightJoin($table, $primaryKey, $operator, $foreignKey)
    {
        return $this->handleJoin($table, $primaryKey, $operator, $foreignKey, 'RIGHT OUTER')->assembleQuery();
    }

    /**
     * Full join
     *
     * Get a full join
     *
     * @param string $table Name of the table to be joined.
     * @param string $primaryKey Primary key from base table.
     * @param string $operator Operator
     * @param string $foreignKey Foreign key
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function fullJoin($table, $primaryKey, $operator, $foreignKey)
    {
        return $this->handleJoin($table, $primaryKey, $operator, $foreignKey, 'FULL')->assembleQuery();
    }

    /**
     * Cross join
     *
     * Get a full join
     *
     * @param string $table Name of the table to be joined.
     * @param string $primaryKey Primary key from base table.
     * @param string $operator Operator
     * @param string $foreignKey Foreign key
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function crossJoin($table, $primaryKey, $operator, $foreignKey)
    {
        return $this->handleJoin($table, $primaryKey, $operator, $foreignKey, 'CROSS')->assembleQuery();
    }

    /**
     * And join
     *
     * @param ...$conditions
     * @return $this
     */
    public function andJoin(...$conditions)
    {
        $conditions = $this->getConditions($conditions);

        $join = vsprintf('AND %s %s %s', $conditions);

        $this->constraints->join->push($join);

        return $this->assembleQuery();
    }

    /**
     * Or join
     *
     * @param ...$conditions
     * @return $this
     */
    public function orJoin(...$conditions)
    {
        $conditions = $this->getConditions($conditions);

        $join = vsprintf('OR %s %s %s', $conditions);

        $this->constraints->join->push($join);

        return $this->assembleQuery();
    }

    private function getJoin()
    {
        $join = $this->constr;
    }

    /**
     * Build a query to create a new record in database
     *
     * @param array $newRecord An associative array with key => values being columns name and their values
     *
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function insert(array $newRecord)
    {
        $this->setStatement(self::INSERT);

        $this->setMultipleParameters($newRecord, 'insert');

        $insertParams = $this->parameters['insert'];
        
        $columns = $insertParams->implode('keys', '`, `');

        $placeholders = [];

        foreach ($insertParams as $value) {
            $placeholders[] = '?';
        }
        $placeholders = \implode(', ', $placeholders);

        $query = $this->getSkeleton($this->statement);

        $query = sprintf($query, $this->constraints->get('table'), $columns, $placeholders);
        
        $this->setQuery($query);

        return $this;
    }

    /**
     * Build a query to update a record on the database
     *
     *
     * @param array $update An associative array with key => values being columns name and their values
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function update(array $data)
    {
        $this->setStatement(self::UPDATE);

        $this->setMultipleParameters($data, 'update');

        $columns = [];

        foreach ($this->parameters['update']->toArray() as $column  => $value){
            $columns[] = "$column = ?";
        }

        $columns = implode(', ', $columns);

        $this->constraints->put('columns', $columns);

        return $this->assembleQuery();
    }

     /**
     * Create a query to delete records from database
     *
     * @param string $column Name of the column that will be used in the clause
     * @param string|int $id Value to delimit the clause search
     * @param $operator | Relational operator
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function delete(string $column = '', $id = '', $operator = null)
    {
        if ($this->constraints->empty('conditions')) {
            if ($operator !== null) {
                $this->where($column, $operator, $id);
            } else {
                $this->where($column, $id);
            }
        }
        return $this->setStatement(self::DELETE)
                    ->assembleQuery();
    }  

    /**
     *
     * Returns the minimum value of expr. MIN() may take a string argument
     * In such cases, it returns the minimum string value.
     *
     * If there are no matching rows, MIN() returns NULL.
     *
     * @param string|array $column The column name
     * @param string $alias A alias the will be use as the column name after fetch database
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function min($column, $alias = null)
    {
        return $this->helpToAggregate($column, 'MIN', $alias);
    }
    
    /**
     *
     * Returns the maximum value of expr. MAX() may take a string argument
     * 
     * In such cases, it returns the maximum string value. 
     * If there are no matching rows, MAX() returns NULL.
     * 
     * @param string|array $column The column name
     * @param string $alias A alias the will be use as the column name after fetch database
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function max($column, $alias = null)
    {
        return $this->helpToAggregate($column, 'MAX', $alias);
    }
    
    /**
     * 
     * The is a alias to average method.
     * @param string|array $column The column name
     * @param string $alias A alias the will be use as the column name after fetch database
     * @see \Fenix\Database\Domain\QueryBuilder::average()
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function avg($column, $alias = null)
    {
        return $this->average($column, $alias);
    }
    
    /**
     * Returns a count of the number of rows with different non-NULL expr values.
     *
     * If there are no matching rows, COUNT(DISTINCT) returns 0.
     *
     * @param string|array $column The column name
     * @param string $alias A alias the will be use as the column name after fetch database
     * @return \Fenix\Database\Domain\QueryBuilder
     * 
     */
    public function average($column, $alias = null)
    {
        return $this->helpToAggregate($column, 'AVG', $alias);
    }
    
     /**
     * Returns a count of the number of rows with different non-NULL expr values.
     *
     * If there are no matching rows, COUNT(DISTINCT) returns 0.
     *
     * @param string|array $column The column name
     * @param string $alias A alias the will be use as the column name after fetch database
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function count($column, $alias = null)
    {
        return $this->helpToAggregate($column, 'COUNT', $alias);
    }
    
    /**
     * Returns the sum of expr. If the return set has no rows, SUM()
     * 
     * If there are no matching rows, SUM() returns NULL.
     * 
     * @param string|array $column The column name
     * @param string $alias A alias the will be use as the column name after fetch database
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    public function sum($column, $alias = null)
    {
        return $this->helpToAggregate($column, 'SUM', $alias);
        
    }  
}