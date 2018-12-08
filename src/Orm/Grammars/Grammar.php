<?php

namespace Fenix\Orm\Grammars;

use Fenix\Orm\Grammar as BaseGrammar;
use Fenix\Orm\QueryException;
use Fenix\Orm\Builder;
use Fenix\Date;

class Grammar extends BaseGrammar
{
     /**
     * The components that make up a select clause.
     *
     * @var array
     */
    protected $components = [
        'aggregate',
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'limit',
        'offset',
        'unions'
        ];


        /**
         * Compile where whole select
         *
         * @param Builder $builder
         * @return string
         */
        public function compilarSelect(Builder $builder)
        {

            if ($builder->aggregate) {
                return $this->compilarColumnsAggregate($builder);
            }
            $sql = $this->compilar($builder);

            return $this->concatenateSql($sql);
        }

        public function compilarcolumnsaggregate($builder)
        {
            $columns = $builder->columns ? $this->colunize($builder->columns) : '';

            $aggregate = $builder->aggregate ? $this->compilarAggregate($builder, $builder->aggregate): '';

            $joins = $builder->joins ? $this->compilarJoins($builder, $builder->joins) : '';

            $wheres = $builder->wheres ? $this->compilarWheres($builder, $builder->wheres) : '';

            $orders = $builder->orders ? $this->compilarOrders($builder, $builder->orders) : '';

            $groups = $builder->groups ? $this->compilarGroups($builder, $builder->groups) : '';

            return "select $columns, $aggregate  from {$builder->from} $joins $wheres $groups $orders";
        }

        /**
         * Compile all components de um select
         *
         * @param Builder $builder
         * @return array
         */
        public function compilar(Builder $builder)
        {
            $properties = get_object_vars($builder);

            $originalColumns = $builder->columns;

            $sql = [];
            foreach ($properties as $property => $value) {
                $method = 'compilar' . ucfirst($property);

                if ($property == 'columns') {
                    if ($builder->aggregate && $value[0] === '*') {
                        $builder->columns = [];
                        continue;
                    }
                }
                if (method_exists($this, $method) && $value) {
                    $sql[] = $this->{$method}($builder, $value);
                }
            }
            $builder->columns = $originalColumns;

            return $sql;
        }

        /**
         * Concatenate sql removing white spaces
         *
         * @param array $sql
         * @return string
         */
        public function concatenateSql(array $sql)
        {
            return trim(implode(' ', $sql));
        }
        /**
         * Compile from
         *
         * @param Builder $builder
         * @return string
         */
        public function compilarFrom(Builder $builder, $table)
        {
            return 'from ' . $table;
        }

        /**
         * Compile columns
         *
         * @param Builder $builder
         * @return string
         */
        public function compilarColumns(Builder $builder, $columns = ['*'])
        {
            if (!$this->tablePrefix) {
                $this->setTablePrefix($builder->from);
            }
   
            $select = $builder->distinct ? 'select distinct ' : 'select ';

            $columns = $this->colunize($columns);

            return $select . $columns;
        }

        /**
         * Compile all wheres within a query
         *
         * @param Builder $builder
         * @param array $wheres
         * @return sttring
         */
        public function compilarWheres(Builder $builder, $wheres)
        {
            $wheres = $this->wheresToArray($builder, $wheres);
            
            return implode(' ', $wheres);
        }

        /**
         * Compile all wheres and put them into array
         *
         * @param Builder $builder
         * @param array $wheres
         * @return string
         */
        public function wheresToArray(Builder $builder, $wheres)
        {
            $result = [];
            foreach ($wheres as $where) {
                $method = 'compilarWhere'. $where['type'];
                $result[] = $this->{$method}($builder, $where);
            }
            return $result;
        }

        /**
         * Compilar where basic where
         *
         * @param Builder $builder
         * @param array $wheres
         * @return string
         */
        public function compilarWhereBasic(Builder $builder, $wheres)
        {
            return $wheres['boolean'] . ' '. $this->colunize($wheres['column']) . ' '.
                   $wheres['operator'] .' '. $this->parameter($wheres['value']);
        }

        /**
         * Compilar whereIn
         *
         * @param Builder $builder
         * @param array $wheres
         * @return string
         */
        public function compilarWhereIn(Builder $builder, $wheres)
        {
            return $wheres['boolean'] . ' '. $this->colunize($wheres['column']) . 
                   ' in (' . $this->parametrize($wheres['values']) . ')';
        }

        /**
         * Compilar whereNotIn
         *
         * @param Builder $builder
         * @param array $wheres
         * @return string
         */
        public function compilarWhereNotIn(Builder $builder, $wheres)
        {
            return $wheres['boolean'] . ' '. $this->colunize($wheres['column']) . 
                   ' not in (' . $this->parametrize($wheres['values']) . ')';
        }

        /**
         * Compilar between
         *
         * @param Builder $builder
         * @param array $between
         * @return string
         */
        public function compilarWhereBetween(Builder $builder, $between)
        {
            return $between['boolean'] . " {$this->colunize($between['column'])} ".
                    'between ' . $this->parameter($between['values'][0]) . 
                    ' and ' . $this->parameter($between['values'][1]);
        }

        /**
         * Compilar not between
         *
         * @param Builder $builder
         * @param array $between
         * @return string
         */
        public function compilarWhereNotBetween(Builder $builder, $between)
        {
            return $between['boolean'] . " {$this->colunize($between['column'])} ".
                    'not between ' . $this->parameter($between['values'][0]) . 
                    ' and ' . $this->parameter($between['values'][1]);
        }

        /**
         * Compilar where null parte
         *
         * @param Builder $builder
         * @param array $where
         * @return string
         */
        public function compilarWhereNull(Builder $builder, $where)
        {
            return $where['boolean'] . " {$this->colunize($where['column'])} is null";
                   
        }

        /**
         * Compilar where null parte
         *
         * @param Builder $builder
         * @param array $where
         * @return string
         */
        public function compilarWhereNotNull(Builder $builder, $where)
        {
            return $where['boolean'] . " {$this->colunize($where['column'])} is not null";
        }

        /**
         * Compilar limit
         *
         * @param Builder $builder
         * @param int $limit
         * @return string
         */
        public function compilarLimit(Builder $builder, $limit)
        {
            return 'limit ' . (int) $limit;
        }


        /**
         * Compilar parte orders
         *
         * @param Builder $builder
         * @param array $orders
         * @return string
         */
        public function compilarOrders(Builder $builder, $orders)
        {
            $orders = array_map(function($order) {
                return $this->colunize($order['column']) . ' ' . $order['order'];
            },(array) $orders);

            return 'order by ' . implode(', ', $orders);
        }

        /**
         * Compilar groups
         *
         * @param Builder $builder
         * @param array $groups
         * @return string
         */
        public function compilarGroups(Builder $builder, $groups)
        {
            return 'group by ' . $this->colunize($groups);
        }

        /**
         * compileAggretate
         *
         * @param Builder $builder
         * @param [type] $aggregate
         * @return void
         */
        public function compilarAggregate(Builder $builder, $aggregate)
        {
            $aggregate = array_map(function($aggreg){
                $alias = isset($aggreg['alias']) ? ' as ' . $aggreg['alias'] : '';
                return $aggreg['function']. '(' . $this->colunize($aggreg['columns']) . ')' . $alias;
            }, $aggregate);
            $aggregate = implode(', ', $aggregate);
            return !$builder->columns ? 'select ' . $aggregate : $aggregate ;
        }

        public function compilarJoins(Builder $builder, $joins)
        {
            $joins = array_map(function($join) use($builder){
                if (isset($join['boolean'])) {
                    return $this->compilarWithJoin($builder, $join);
                }
                $args = array_merge([array_pop($join)], $join);
                return vsprintf('%s join %s on %s %s %s', $args);
            }, $joins);
            return implode(' ', $joins);
        }

        /**
         * Helper to joins
         *
         * @param Builder $builder
         * @param array $join
         * @return string
         */
        public function compilarWithJoin(Builder $builder, array $join)
        {
            return $this->compilarWhereBasic($builder, $join);
        }

        /**
         * Compilar insert for query
         *
         * @param Builder $builder
         * @param array $values
         * @return string
         */
        public function compilarInsert(Builder $builder, array $values)
        {
            $forInsert = $this->prepareForInsert($builder, $values);
            if (empty($values)) {
                throw new QueryException("No values to insert");
            }

            return 'insert into ' . $builder->from . ' (' . $forInsert['columns']  .') '  . 
                    'values (' . $forInsert['values'] . ')';
        }

        /**
         * Prepare values to insert
         *
         * @param Builder $builder
         * @param array $values
         * @return array
         */
        protected function prepareForInsert(Builder $builder, array $values)
        {
            $columns = $this->colunize(array_keys($values));

            $values = array_map(function($value) use($builder){
                $value = $this->isDate($value) ? $value->getDate() : $value;
                $builder->addParameter('insert', $value);
                return $this->parameter($value);
            }, array_values($values));
            $values = implode(', ', $values);
            return compact('columns', 'values');
        }

        /**
         * Compile update query
         *
         * @param Builder $builder
         * @param array $values
         * @return string
         */
        public function compilarUpdate(Builder $builder, array $values)
        {
            $where = $this->compilarWheres($builder, $builder->wheres);

            $forUpdate = $this->prepareForUpdate($builder, $values);

            return 'update ' . $builder->from  . ' set ' .
                    $forUpdate . ' ' . $where;
        }

        /**
         * Prepare values for update format
         *
         * @param Builder $builder
         * @param array $values
         * @return string
         */
        protected function prepareForUpdate(Builder $builder, array $values)
        {
            return implode(', ', array_map(function($value, $column) use($builder){
                $value = $this->isDate($value) ? $value->getDate() : $value;
                $builder->addParameter('update', $value);
                return "$column = " . $this->parameter($value);
            }, array_values($values), array_keys($values)));
        }

        /**
         * Compilar delete
         *
         * @param Builder $builder
         * @param array $where
         * @return string
         */
        public function compilarDelete(Builder $builder, array $where = [])
        {
            if (!empty($where)) {
                $builder->where($where);
            }
            $where = $this->compilarWheres($builder, $builder->wheres);

            return 'delete from ' . $builder->from . " $where"; 

        }

        public function isDate($value)
        {
            return $value instanceof Date;
        }
      
}