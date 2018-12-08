<?php

namespace Fenix\Traits\Database;

use Fenix\Database\Collection\Collection;
use BadMethodCallException;
/**
 *
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 * @package Fenix
 * @subpackage Database\Traits
 * @license MIT
 *        
 */
trait Query
{

    /**
     * Dinamically call some query methods
     *
     * @param string $name Method name
     * @param array $args Parameters
     * @return mixed
     * @throws BadMethodCallException | If method not exists
     */
    public function __call($name, $args)
    {
        switch($name) {
            case 'whereIn':     
                return $this->in('where', ...$args);           
                break;
            case 'whereNotIn':
                return $this->notIn('where', ...$args);
                break;
            case 'whereBetween':
                return $this->between('where', ...$args);
                break;
            case 'whereNotBetween':
                return $this->notBetween('where', ...$args);
                break;
            case 'andIn':
                return $this->in('and', ...$args);
                break;
            case 'andNotIn':
                return $this->notIn('and', ...$args);
                break;
            case 'andBetween':
                return $this->between('and', ...$args);
                break;
            case 'andNotBetween':
                return $this->notBetween('and', ...$args);
                break;
            case 'orIn':
                return $this->in('or', ...$args);
                break;
            case 'orNotIn':
                return $this->notIn('or', ...$args);
                break;
            case 'orBetween':
                return $this->between('or', ...$args);
                break;
            case 'orNotBetween':
                return $this->notBetween('or', ...$args);
                break;
        }
        throw new BadMethodCallException(sprintf("Method %s::%s doesn't exist", get_class($this), $name));
    }

    /**
     * Get the current query string
     *
     * @return string
     */
    public function getQuery()
    {        
        if (is_null($this->query)) {
            $this->assembleQuery();
        }
        
        return $this->query;
    }

    /**
     * Get the current query and parameters
     *
     * @return array An array containing the query and the parameters
     */
    public function getValues()
    {
        $query = $this->getQuery();

        $params = $this->getParameters();
        $this->load();
        return compact('query', 'params');
    }

    /**
     * Updates / set the current query
     * @param int|string $statment If a number is passed the query will be updated using $skeletons
     *                             property as parameter, otherwilse will update with the parameter passed
     */
    public function setQuery($statment)
    {
        if (is_int($statment)) {
            $this->query = $this->getSkeleton($statment);
        } else {
            $this->query = $statment;
        }
    }

    /**
     * Set the type of query / statment would be build.
     * @param int $statement
     * @return $this
     * @package  Fenix
     * @subpackage Database\Traits
     */
    public function setStatement(int $statement)
    {
        $this->statement = $statement;
        return $this;
    }

    /**
     * Get current statment
     *
     * @return int
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * Set table name
     * @param string $table Table's name
     */
    public function setTable(string $table)
    {
        $this->constraints->put('table', $table);
    }

    /**
     * Get table name used in the query.
     * @return string
     */
    public function getTable()
    {
        return $this->constraints->get('table');
    }

    /**
     * Get the skeleton query to use, depend on the type of $statment
     * @param int $ske
     * @return string Skeleton query
     */
    public function getSkeleton(int $ske)
    {
        return $this->skeletons[$ske];
    }

     /**
     * Get a string of columns from an array of values
     * @param array $columns Columns from table
     * @return $this
     */
    public function setColumns(array $columns, $noTable = false)
    {
        //$columns = array_map(function($column) use($noTable){
        //    return strpos($column, '.') || $noTable ? $column : $this->withTable($column);
        //}, $columns);
        $this->constraints->put('columns', $this->colunize($columns));
        return $this;
    }

    /**
     * Set parameters
     *
     * Set parameters to binded when a query would be executed.
     *
     * @param $valor
     * @param $key | The array key
     * @param string $keyParam
     * @return $this
     */
    public function setParameters($valor, $key, $keyParam = '')
    {
        if (empty($valor) && strpos($keyParam, '@')) {
            return $this;
        }
        if ($keyParam !== '') {
            if (!$this->parameters[$key]->has($keyParam)) {
                $this->parameters[$key]->add($keyParam, $this->treatData($valor));
                
            }
        } else {
            $this->parameters[$key]->push($this->treatData($valor));
        }
        return $this;
    }

        /**
     * Set Multiples Parameters
     *
     * @see setParams($param)
     * @param array $parameters
     * @return void
     */
    public function setMultipleParameters(array $parameters, $key)
    {
        foreach ($parameters as $keyParameter => $parameter){
            $this->setParameters($parameter, $key, $keyParameter);
        }
    } 

     /**
     * Get params to be used in a database statment
     *
     * @return array An array of parameters to be binded.
     */
    public function getParameters()
    {
        switch($this->statement) {
            case self::UPDATE:
                $update = $this->parameters['update']->toArray();
                $constraints = $this->parameters['constraints']->toArray();
                return array_merge($update, $constraints);
                break;
            case self::INSERT:
                RETURN $this->parameters['insert']->toArray();
                break;
            default:
                return $this->parameters['constraints']->toArray();
        }
        
    }

    /**
     * Form the query string
     *
     * Form the query string to be executed in a database statment
     *
     * @return $this
     */
    public function assembleQuery()
    {
        $query = $this->getSkeleton($this->statement);
        if (!$this->constraints->join->isEmpty()) {
            $conditions = $this->constraints->join->implode(' ') . ' ' . $this->constraints->conditions;
        } elseif( !$this->constraints->empty('group')) {
            $conditions = $this->constraints->conditions . ' ' . $this->constraints->group;
        } 
        else {
            $conditions = $this->constraints->conditions;
        }
        $args = $this->buildArguments($this->constraints->toArray(), $conditions);////[$this->constraints->columns, $this->constraints->table, $conditions ];
        $query = \vsprintf($query, $args);
        $this->setQuery($query);
        return $this;
    }

    private function buildArguments($array, $conditions = [])
    {
        if ($this->statement == self::UPDATE) {// TABELA, COLUNA, CONDITIONS
            return [$array['table'], $array['columns'], $array['conditions']];
        } elseif ($this->statement == self::DELETE) {
            return [$array['table'], $array['conditions']];
        }
        return [trim($array['columns'] ?? '*', ', '), $array['table'], $conditions];
    }
    /**
     * Get the conditions to be used in where joins and clauses
     *
     * @param array $conditions
     * @return array
     */
    public function getConditions($conditions)
    {
        $column = $this->withTable($conditions[0]);
        if (count($conditions) == 3) {
            $operator = $conditions[1];
            $value = $conditions[2];
        } else {
            $operator = '=';
            $value = $conditions[1];
        }
        return compact('column', 'operator', 'value');
    }

     /**
     * Treat all data received from the user to prevent SQL Injection
     * @param mixed $data Any type of data to be received
     * @return mixed|string The data treated
     */
    private function treatData($data)
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                $value = addslashes(trim($value));
            }
            return $data;
        }
        return addslashes(trim($data));
    }

    /**
     * Column with table name
     *
     * @param string $column
     * @return string
     */
    public function withTable($column)
    {
        if (stripos($column, '.')) {
            return $column;
        }
        return $this->getTable() . '.' . $column;
    }

    /**
     * Handle other types of where/and/or and buiding the where clause and setting parameters
     *
     * @param string|array $clause Clause to be used together with WHERE.
     * @param string $column Column name
     * @param array $data An array container the parameters to use in the query.
     * @param $constraint
     * @return Query
     */
    public function format($clause, $column, $data, $constraint)
    {
        $column = $this->withTable($column);
        if (is_array($clause)) {
            $args = [strtoupper($constraint), $column, $clause[0], $clause[1]];
            $this->paramString($data);
            $query = vsprintf('%s %s %s ? %s ?', $args); // where/and/or
        } else {
            $query = sprintf(
                '%s %s %s (%s)', strtoupper($constraint), $column, $clause, $this->paramString($data) // where/and/or
                );
        }
        return $this->constraints($constraint, $query);
    }

    /**
     * Glue some paras into a string and pass it to $this->params property
     *
     * @param array $data An array of parameters to be setted in $params property
     * @return string          A handle it string to be put in the where clause like "?, ?"
     */
    private function paramString($data)
    {
        $strings = Collection::create();
        array_map(function($value) use($strings){
            $this->setParameters($value, 'constraints');
            $strings->push('?');
        },$data);
        return $strings->implode(', ');
    }

    /**
     * Check if a method belong to a query builder
     *
     * @param string $query
     * @return boolean
     */
    public function isQuery($query)
    {
        return method_exists($this, $query);
    }

    /**
     * Check if the query string is empty
     *
     * @return boolean Return true case it's not empty and false otherwise.
     */
    public function isEmpty()
    {
        if ($this->query === null) {
            return true;
        } elseif (empty($this->query)) {
            return true;
        }
        return false;
    }

        /**
     * Handle a  string for join constraints
     *
     * @param string $table | Name of the table to be joined.
     * @param string $primaryKey |  Primary key from base table.
     * @param string $operator |  Operator
     * @param string $foreignKey  | Foreign key
     * @param string $type  | The type of join e.g. inner
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    private function handleJoin($table, $primaryKey, $operator, $foreignKey, $type)
    {
        $args = [$type, $table, $primaryKey, $operator, $foreignKey];
        $joinString = vsprintf(' %s JOIN %s ON %s %s %s', $args);
        
        return $this->constraints('join', $joinString);
    }

    /**
     *
     * Define constraints
     *
     * This method will set the clause to use in later in the query string
     * And update the $params property.
     *
     * @param string $constraint The constraint / clause to be updated
     * @param $clause
     * @return $this
     */
    private function constraints($constraint, $clause)
    {
        $condition = '';
        if ($constraint === 'where') {
            $this->constraints->put('where', $clause);
            $condition = $clause;
        } elseif($constraint === 'and' || $constraint === 'or') {
            if (!$this->constraints->empty('where') && $this->constraints->empty('conditions')) {
                $condition = $this->constraints->where . ' ' . $clause;
            } elseif (!$this->constraints->empty('where') && !$this->constraints->empty('conditions')) {
                $condition = $this->constraints->conditions . ' ' . $clause;
            }  else {
                     throw new \PDOException('Cannot use this statement without WHERE before.');
            }
        } elseif($constraint === 'order') {
            if (!$this->constraints->contains($clause)) {
                $this->constraints->put('order', $clause);
            }
            if (!$this->constraints->empty('conditions')) {
                if ($clause !== $this->constraints->conditions) {
                    $condition = $this->constraints->conditions. ' ' . $clause;
                } else {
                    $condition = $this->constraints->conditions;
                }
            } else {
                $condition = $clause;
            }
        } elseif ($constraint === 'join') {
            $this->constraints->join->push($clause);
            return $this;
        } elseif ($constraint === 'group') {
            $this->constraints->put('group', $clause);
            return $this;
        }        
        $this->constraints->put('conditions', $condition);
        return $this;
    }
    
    /**
     * Helper methods to be used with the aggregation methods
     * @param string|array $column
     * @param string $aggregator
     * @param string $alias
     * @return \Fenix\Database\Domain\QueryBuilder
     */
    private function aggregate($column, $aggregator, $alias = null)
    {        
        $columns = explode(', ', $this->constraints->get('columns'));
        
        if (count($columns) === 1){
            $columns = explode(' , ', $this->constraints->get('columns'));
            
        }
        if ($alias !== null) {
            $alias = " AS $alias";
        }
        
        $searched = array_search($column, $columns);
        
        if ($searched !== false) {
            $columns[$searched] = sprintf('%s(%s)%s', $aggregator, $this->withTable($column), $alias);
        } else {
            $columns[] = sprintf('%s(%s)%s', $aggregator, $this->withTable($column), $alias);
        }
        $this->setColumns(array_filter($columns), function($column){
            return $column;
        }, true);
        return $this->assembleQuery();
    }

    /**
     * Help to aggregate
     * @param string|array $column
     * @param $agregator
     * @param string $alias
     * @return \Fenix\Database\Traits\QueryTrait|\Fenix\Database\Domain\QueryBuilder
     */
    public function helpToAggregate($column, $agregator, $alias = null)
    {
        $this->setStatement(self::SELECT);
        if (is_array($column)) {
            foreach ($column as $col) {
                $this->aggregate($col, $agregator, $alias);
            }
            return $this;
        }
        return $this->aggregate($column, $agregator, $alias);
    }
}
