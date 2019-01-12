<?php

namespace Fenix\Orm;

use Fenix\Orm\Grammars\Grammar;
use Fenix\Orm\Collection\Collection;
use InvalidArgumentException;

class Builder
{

    public $connection;

    public $grammar;

    /**
     * Connection
     *
     * @var array
     */
    public $columns = [];
    
    /**
     * Table name
     *
     * @var string
     */
    public $aggregate = [];

    /**
     * Table name
     *
     * @var string
     */
    public $from;

    /**
     * Joins
     *
     * @var array
     */
    public $joins = [];

    /**
     * Wheres
     *
     * @var array
     */
    public $wheres = [];

    /**
     * Groups
     *
     * @var array
     */
    public $groups = [];

    /**
     * Orders
     *
     * @var array
     */
    public $orders = [];

    /**
     * Limit of rows
     *
     * @var int
     */
    public $limit;


     /**
     * The current query value bindings.
     *
     * @var array
     */
    public $parameters = [
        'select' => [],
        'from'   => [],
        'join'   => [],
        'where'  => [],
        'having' => [],
        'order'  => [],
        'union'  => [],
        'update' => [],
        'insert' => [],
    ];

    public $distinct = false;

     /**
     * All of the available clause operators.
     *
     * @var array
     */
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];

    /**
     * Builder constructor.
     * @param Connection $connection
     * @param Grammar $grammar
     * @param Model|null $model
     */
    public function __construct(Connection $connection, Grammar $grammar, Model $model = null)
    {
        $this->connection = $connection;
        $this->grammar = $grammar;
        $this->model = $model;
    }

    /**
     * Get results from database
     *
     * @param array $columns
     * @return Collection|Model
     */
    public function get($columns = ['*'])
    {
        if (!$this->hasColumns()){
            $this->addSelect($columns);
        }

        $results =  new Collection($this->connection->select(
            $this->convertToSql(), $this->getParameters()
        ));

        if (!$this->model) {
            return $results;
        }
        if ($results->count() == 1){
            return $this->model->newInstance($results->first());
        }

        return $results->map(function($result){
            return $this->model->cloneInstance( $result);
        });
    }

    public function sql()
    {
        return ['sql' => $this->convertToSql(), 'params' => $this->getParameters() ];
    }

    /**
     * Convert the query select to sql
     *
     * @return string
     */
    public function convertToSql()
    {
        $sql = $this->grammar->compilarSelect($this);
        $this->reset();
        return $sql;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function reset()
    {
        $this->columns = [];
        $this->aggregate = [];
        $this->from = null;
        $this->joins = [];
        $this->wheres = [];
        $this->groups = [];
        $this->orders = [];
        $this->limit = null;
        $this->distinct = false;
        
        return $this;
    }

    /**
     * Select columns
     *
     * @param array $columns
     * @return Builder
     */
    public function select($columns = ['*'])
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        $this->columns = $columns;

        return $this;
    }

    public function hasColumns()
    {
        return !empty($this->columns);
    }

    /**
     * Distinct values
     *
     * @return Builder
     */
    public function distinct()
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * Define table  name
     *
     * @param $table
     * @return Builder
     */
    public function from($table)
    {
        $this->from = $table;

        $this->grammar->setTablePrefix($table);
        
        return $this;
    }

    /**
     * And clause
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return Builder
     */
    public function and($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->where($column, $operator, $value, $boolean);
    }

      /**
     * And clause
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @param string $boolean
     * @return Builder
     */
    public function or($column, $operator = null, $value = null, $boolean = 'or')
    {
        return $this->where($column, $operator, $value, $boolean);
    }

    /**
     * Add where to the query
     *
     * @param  ...$conditions
     * @return Builder
     */
    public function where($column, $operator = null, $value = null, $boolean = 'where')
    {
        if (is_array($column)) {
            return $this->addWheres($column, $boolean);
        }

        [$value, $operator] = $this->getConditions($value, $operator);

        $type = 'Basic';

        $this->addParameter('where', $value);

        $this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');

        return $this;
    }


    /**
     * where in query
     *
     * @param string $column
     * @param mixed $values
     * @param string $boolean
     * @param boolean $not
     * @return Builder
     */
    public function whereIn($column, ...$values)
    {
        return $this->inOrNotBetween($column, $values);
    }
    
    /**
     * where not in into query
     *
     * @param string $column
     * @param string $value
     * @param string $boolean
     * @param boolean $not
     * @return Builder
     */
    public function whereNotIn($column, ...$values)
    {
        return $this->inOrNotBetween($column, $values, 'and', true);
    }

    /**
     * Adds a where
     *
     * @param  $column
     * @param  ...$values
     * @return Builder
     */
    public function whereBetween($column, ...$values)
    {
        return $this->inOrNotBetween($column, $values, 'and', false, true);
    }
    
    /**
     * Adds a not between
     *
     * @param  $column
     * @param  ...$values
     * @return Builder
     */
    public function whereNotBetween($column, ...$values)
    {
        return $this->inOrNotBetween($column, $values, 'and', true, true);
    }

    /**
     * Where Null to query
     *
     * @param string $column
     * @param mixed $value
     * @param string $boolean
     * @param boolean $not
     * @return Builder
     */
    public function whereNull($column, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        $boolean = isset($this->wheres[0]) ? 'and' : 'where';

        $this->wheres[] = compact('type', 'column','boolean');

        return $this;
    }

    /**
     * Where Null to query
     *
     * @param string $column
     * @param mixed $value
     * @param string $boolean
     * @param boolean $not
     * @return Builder
     */
    public function whereNotNull($column, $boolean = 'and')
    {
        return $this->whereNull($column, $boolean, true);
    }

    /**
     * If is in Or not
     *
     * @param string $column
     * @param  mixed $values
     * @param string $boolean
     * @param boolean $not
     * @return Builder
     */
    public function inOrNotBetween($column, $values, $boolean = 'and', $not = false, $between = false)
    {
        if ($between) {
            $type = 'Between';
        } else {
            $type = $not ? 'NotIn' : 'In';
        }

        $boolean = isset($this->wheres[0]) ? 'and' : 'where';

        $values = count($values) == 1 ? $values[0] : $values;

        $this->addParameter('where', $values);

        if ($between) {
            $this->wheres[] = compact('type', 'column', 'values', 'boolean', 'not');
        } else {
            $this->wheres[] = compact('type', 'column', 'values', 'boolean');
        }

        return $this;  
    }

    /**
     * Get value and operator
     *
     * @param string $value
     * @param string $operator
     * @return array
     */
    private function getConditions($value, $operator)
    {
        if (is_null($value)) {
            return [$operator, '='];
        }
        if ($this->invalidOperator($operator)) {
            throw new InvalidArgumentException('Illegal operator');
        }
        return [$value, $operator];
    }

    /**
     * Add array of wheres
     *
     * @param array $wheres
     * @param string $boolean
     * @return Builder
     */
    protected function addWheres($wheres, $boolean)
    {
        $type = 'basic';
        if (is_array($wheres[0])) {
            foreach ($wheres as $where) {
                $boolean = isset($this->wheres[0]) ? 'and' : 'where';
                [$value, $operator] = $this->getConditions($where[2] ?? null, $where[1]);
                $column = $where[0];
                $this->addParameter('where', $value);
                $this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');
            }
        } else {
            $boolean = isset($this->wheres[0]) ? 'and' : 'where';
            [$value, $operator] = $this->getConditions($wheres[2] ?? null, $wheres[1]);
            $column = $wheres[0];
            $this->addParameter('where', $value);
            $this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');
        }
        return $this;

    }

    /**
     * Alias to set limit of rows for a query
     *
     * @param int $limit
     * @return Builder
     */
    public function rows($limit)
    {
        return $this->limit($limit);
    }

    /**
     * Set the limit value for the query
     *
     * @param int $limit
     * @return Builder
     */
    public function limit($limit)
    {
        if ($limit >= 0) {
            $this->limit = $limit;
        }
        return $this;
    }

    /**
     * Add a order by to query
     *
     * @param mixed ...$columns
     * @return Builder
     */
    public function orderBy($column, $order = 'asc')
    {
        $this->orders[] = ['column' =>$column, 'order' => $order];

        return $this;
    }

    /**
     * Add a order by to query
     *
     * @param mixed ...$columns
     * @return Builder
     */
    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add a ordered latest record into database
     *
     * @param string $column
     * @return Builder
     */
    public function latest($column = 'created_at')
    {
        return $this->orderByDesc($column);
    }

    /**
     * Add a ordered oldest record into database
     *
     * @param string $column
     * @return Builder
     */
    public function oldest($column = 'created_at')
    {
        return $this->orderBy($column);
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
     * @return Builder
     */
    public function min($column, $alias = null, $run = false)
    {
        return $this->aggregate('min', $column, $alias, $run);
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
     * @return Builder
     */
    public function max($column, $alias = null, $run = false)
    {
        return $this->aggregate('max', $column, $alias, $run);
    }
    
    /**
     * 
     * The is a alias to average method.
     * @param string|array $column The column name
     * @param string $alias A alias the will be use as the column name after fetch database
     * @see Builder::average()
     * @return Builder
     */
    public function avg($column, $alias = null, $run = false)
    {
        return $this->average($column, $alias, $run);
    }
    
    /**
     * Returns a count of the number of rows with different non-NULL expr values.
     *
     * If there are no matching rows, COUNT(DISTINCT) returns 0.
     *
     * @param string|array $column The column name
     * @param string $alias A alias the will be use as the column name after fetch database
     * @return QueryBuilder
     * 
     */
    public function average($column, $alias = null, $run = false)
    {
        return $this->aggregate('avg', $column,  $alias, $run);
    }
    
     /**
     * Returns a count of the number of rows with different non-NULL expr values.
     *
     * If there are no matching rows, COUNT(DISTINCT) returns 0.
     *
     * @param string|array $column The column name
     * @param string $alias A alias the will be use as the column name after fetch database
     * @return Builder
     */
    public function count($column, $alias = null, $run = false)
    {
        return $this->aggregate('count', $column,  $alias, $run);
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
    public function sum($column, $alias = null, $run = false)
    {
        return $this->aggregate('sum', $column,  $alias, $run);        
    }

    /**
     * Executa uma função de agregação
     *
     * @param string $function
     * @param array $columns
     * @return Builder
     */
    public function aggregate($function, $columns = ['*'], $alias = null, $run = false)
    {
        if ($run) {
            $results = $this->setAggregate($function, $columns)
                            ->get($columns);
            return $results;
        }
        return $this->setAggregate($function, $columns, $alias);
    }

    /**
     * Run aggregate function without run query
     *
     * @param string $function
     * @param mixed $columns
     * @return Builder
     */
    private function setAggregate($function, $columns, $alias)
    {
        $this->aggregate[] = $alias ? compact('function', 'columns', 'alias') 
                                  : compact('function', 'columns');
        if (empty($this->groups)) {
            $this->orders = null;
            $this->parameters['order'] = [];
        }
        return $this;
    }

    /**
     * Add group by to query
     *
     * @param mixed ...$columns
     * @return Builder
     */
    public function groupBy(...$columns)
    {
        if (empty($this->groups)) {
            $this->groups = $columns;
        } else {
            $this->groups = array_merge((array) $this->merge, $columns);
        }
        return $this;
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
     * @return Builder
     */
    public function join(string $table, string $primaryKey, string $operator, 
                        string $foreignKey, $type = 'inner')
    {
        if ($this->invalidOperator($operator)) {
            throw new InvalidArgumentException('Illegal operator');
        }
        $this->joins[] = compact('table', 'primaryKey', 'operator', 'foreignKey', 'type');

        return $this;
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
     * @return Builder
     */
    public function leftJoin(string $table, $primaryKey, string $operator, $foreignKey)
    {
        return $this->join($table, $primaryKey, $operator, $foreignKey, 'left outer');
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
     * @return Builder
     */
    public function rightJoin(string $table, $primaryKey, string $operator, $foreignKey)
    {
        return $this->join($table, $primaryKey, $operator, $foreignKey, 'right outer');
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
        return $this->join($table, $primaryKey, $operator, $foreignKey, 'full');    
    }

    /**
     * Cross join
     *
     * Get a cross join
     *
     * @param string $table Name of the table to be joined.
     * @param string $primaryKey Primary key from base table.
     * @param string $operator Operator
     * @param string $foreignKey Foreign key
     * @return Builder
     */
    public function crossJoin($table, $primaryKey, $operator, $foreignKey)
    {
        return $this->join($table, $primaryKey, $operator, $foreignKey, 'cross');
    }

    
    /**
     * Or join
     *
     * @param ...$conditions
     * @return Builder
     */
    public function andJoin($column, $operator = null, $value = null)
    {
        return $this->withJoin($column, $operator, $value);
    }

    /**
     * Or join
     *
     * @param ...$conditions
     * @return Builder
     */
    public function orJoin($column, $operator = null, $value = null)
    {
        return $this->withJoin($column, $operator, $value, 'or');
    }

    /**
     * Add clauses to join
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return Builder
     */
    public function withJoin($column, $operator = null, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->getConditions($value, $operator);

        if ($this->invalidOperator($operator)) {
            throw new InvalidArgumentException('Illegal operator');
        }

        $this->joins[] = compact('column', 'operator', 'value', 'boolean');
        $this->addParameter('join', $value);

        return $this;
    }

    /**
     * Verify if is a legal operator
     *
     * @param $operator
     * @return bool
     */
    private function invalidOperator($operator)
    {
        return array_search($operator, $this->operators) === false;
    }

    /**
     * Add parameter to binding
     *
     * @param string $type
     * @param mixed $value
     * @return void
     */
    public function addParameter($type, $value)
    {
        $this->parameters[$type][] = $value;
    }

    /**
     * Get parameters to run the query
     *
     * @return array
     */
    public function getParameters()
    {
        //for now just keeping simple just where
        $wheres =  $this->parameters['where'];

        if (!empty($this->parameters['update'])) {
            $wheres = array_merge($this->parameters['update'], $wheres);
        } elseif (!empty($this->parameters['insert'])) {
            $wheres = $this->parameters['insert'];
        } elseif (!empty($this->parameters['join'])) {
            $wheres = array_merge($this->parameters['join'], $wheres);
        }

        foreach ($this->parameters as &$parameter) {
            $parameter = [];
        }
        return $wheres;
    }

    /**
     * compact type, query, boolean
     * @todo Implement
     * @return void
     */
    // public function whereExists(Closure $callback, $boolean = 'and', $not = false)
    // {

    // }

    /**
     * @todo Implement
     *
     * @param Closure $callback
     * @param string $boolean
     * @param boolean $not
     * @return void
     */
    // public function whereNotExists(Closure $callback, $boolean = 'and', $not = false)
    // {

    // }

    /**
     * @todo Implement
     *
     * @param [type] $query
     * @param [type] $boolean
     * @param [type] $not
     * @return void
     */
    // public function addWhereExistsQuery($query, $boolean, $not)
    // {

    // }

    /**
     * Return new instance for subquerys
     *
     * @return Builder  
     */
    // public function forSubQuery()
    // {
    //     return new static($this->connection, $this->grammar);
    // }

    /**
     * Add a new select column to the query.
     *
     * @param  array|mixed  $column
     * @return Builder
     */
    public function addSelect($column)
    {
        $column = is_array($column) ? $column : func_get_args();

        if (empty($this->columns)) {
            return $this->select($column);
        } else {
            $this->columns = array_merge((array) $this->columns, $column);
        }

        return $this;
    }

    /**
     * Delete records from database
     *
     * @param array $conditions
     * @return int
     */
    public function delete($conditions = [])
    {
        $query = $this->grammar->compilarDelete($this, $conditions);

        if (!$this->from && $this->grammar->getTablePrefix()) {
            $this->from = $this->grammar->getTablePrefix();

            return $this->delete(empty($conditions) ? $conditions : []);
        }
        $this->reset();
        return $this->connection->delete($query, $this->getParameters());
    }

    /**
     * Update records in database
     *
     * @param array $conditions
     * @return int
     */
    public function update(array $values)
    {
        $query = $this->grammar->compilarUpdate($this, $values);

        if (!$this->from && $this->grammar->getTablePrefix()) {
            $this->from = $this->grammar->getTablePrefix();

            return $this->update($values);
        }

        $this->reset();
        return $this->connection->update($query, $this->getParameters());
    }

    /**
     * Insert values into database
     *
     * @param array $values
     * @return int
     */
    public function insert(array $values)
    {
        $query = $this->grammar->compilarInsert($this, $values);

        if (!$this->from && $this->grammar->getTablePrefix()) {
            $this->from = $this->grammar->getTablePrefix();

            return $this->insert($values);
        }
        $this->reset();

        return $this->connection->insert($query, $this->getParameters());
    }

    /**
     * Insert values into database and get last inserted id
     *
     * @param array $values
     * @return int
     */
    public function insertWithLastInsertedId(array $values)
    {
        $query = $this->grammar->compilarInsert($this, $values);

        if (!$this->from && $this->grammar->getTablePrefix()) {
            $this->from = $this->grammar->getTablePrefix();

            return $this->insertWithLastInsertedId($values);
        }
        $this->reset();

        return $this->connection->insertWithLastInsertedId($query, $this->getParameters());
    }

    /**
     * Get the connection instance
     *
     * @return Conection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Call an stored procedure from database
     * @param string $procedure
     * @param mixed $params
     * @return bool|mixed
     */
    public function callProcedure(string $procedure, $params = null)
    {
        $connection = $this->getConnection();

        if (!is_null($params)) {
            $query = sprintf('CALL %s(?)', $procedure);

            $stm = $connection->prepared($query);//static::$connector->prepare($query);

            foreach ($params as $key => $param) {
                $stm->bindParam($key + 1, $param);
            }
            return $stm->execute();
        } else {
            $query = sprintf('CALL %s', $procedure);
            $stm = $connection->prepared($query);
            return $stm->execute();
        }
    }

    public function rawQuery($query, $select = true)
    {

        $connection = $this->getConnection();

        $stm = $connection->prepared($query);

        $stm->execute();

        if ($select) {
            $result = new Collection($stm->fetchAll());

            if ($this->model) {
                if ($result->count() == 1) {
                    return $this->model->cloneInstance($result->first());
                }
                return $result->map(function($item){
                    return $this->model->cloneInstance($item);
                });
            }

            return $result;
        }
    }
}