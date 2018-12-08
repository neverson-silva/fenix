<?php

namespace Fenix\Orm;

abstract class Grammar
{
    protected $tablePrefix;

    /**
     * Turn an arary of values into query string
     *
     * @param array $columns
     * @return string
     */
    public function colunize($columns) 
    {
        $columns = array_map(function($column){
            return $this->withTable($column);
        }, is_array($columns) ? $columns : [$columns]);
        return implode(', ', $columns);
    }

    /**
     * Parameter a value
     *
     * @param string $value
     * @return string
     */
    public function parameter($value)
    {
        return '?';
    }

    /**
     * Parametrize a array of columns
     *
     * @param array $values
     * @return string
     */
    public function parametrize(array $values)
    {
        return implode(', ', array_map([$this, 'parameter'], $values));
    }
    /**
     * With table to get better performance in database
     *
     * @param string $column
     * @return string
     */
    public function withTable($column)
    {
        if (stripos($column, '.') || !$this->tablePrefix) {
            return $column;
        }
        
        return $this->getTablePrefix() . '.' . $column;
    }

    /**
     * Get table prefix
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Set table prefix
     *
     * @return void
     */
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;
    }

}