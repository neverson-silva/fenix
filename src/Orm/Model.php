<?php

namespace Fenix\Orm;

use Fenix\Contracts\Database\Model as ModelContract;
use Fenix\Orm\Grammars\Grammar;
use Fenix\Support\Collection;
use Fenix\Orm\Connection;
use IteratorAggregate;
use JsonSerializable;
use Fenix\TypeHint;
use ArrayObject;
use Traversable;
use Fenix\Date;
use Exception;
use Countable;

class Model extends Relationship implements JsonSerializable, IteratorAggregate, ModelContract, Countable
{
    /**
     * @var
     */
    protected $connection;

    /**
     * @var
     */
    protected $table;

    /**
     * @var
     */
    protected $primaryKey;

    /**
     * @var object
     */
    protected $attributes;

    protected $relations = [];

    protected $fillable = [];

    /**
     * Model constructor.
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->attributes = (object)$this->attributes;

        $this->setAttributes($attributes);

        if (!$this->table) {
            $this->setTable();
        }

        if (!$this->primaryKey) {
            $this->setPrimaryKey();
        }

        $this->getDefaultConnection();
    }

    /**
     * Definining Model table's name
     * Default Model class name lower case + s
     *
     * @param string $table Model's table name [optional]
     * @return void
     */
    public function setTable($table = null)
    {
        if ($table === null) {
            $table = $this->pluralize($table);
        }
        $this->table = $table;

        return $this;
    }

    /**
     * Get model tables name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Pluralize a string
     * @param string $table
     * @return string|mixed
     */
    private function pluralize($table)
    {
        $pattern = '/(a$|e$|i$|(^a)o$|u$)/';
        $class = explode('\\', get_class($this));
        if (preg_match($pattern, end($class))) {
            $table = strtolower(array_pop($class)) . 's';
        } elseif (preg_match('/(ao$)/', end($class))) {
            $table = preg_replace('/(ao$)/', 'oes', strtolower(array_pop($class)));
        } elseif (preg_match('/(l$)/', end($class))) {
            $table = preg_replace('/(l$)/', 'is', strtolower(array_pop($class)));
        } else {
            $table = strtolower(array_pop($class)) . 'es';
        }
        return $table;
    }

    /**
     * Setting model primary_key
     * Defult lowercase model name + _id
     *
     * @param string $key PrimaryKey name
     * @return Model
     */
    public function setPrimaryKey(string $key = null)
    {
        if (empty($key)) {
            $class = explode('\\', get_class($this));
            $this->primaryKey = strtolower(array_pop($class)) . '_id';
            return $this;
        }
        $this->primaryKey = $key;
        return $this;
    }

    /**
     * Get primary key name
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param $attributes set an array of attributes
     */
    public function setAttributes($attributes)
    {
        if (empty($attributes) || !is_array($attributes)) {
            return;
        }
        foreach ($attributes as $column => $value) {
            $this->setAttribute($column, $value);
        }
    }

    /**
     * Get Model attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return (array) $this->attributes;
    }

    /**
     * Set a attribute
     *
     * @param $attribute
     * @param $value
     */
    public function setAttribute($attribute, $value)
    {
        $this->attributes->{$attribute} = $value;
    }

    /**
     * Get attribute
     * @param $attribute
     * @return null
     */
    public function getAttribute($attribute)
    {
        return $this->hasAttribute($attribute) ? $this->attributes->{$attribute} : null;
    }

    /**
     * Has attribute
     *
     * @param $attribute
     * @return bool
     */
    public function hasAttribute($attribute)
    {
        return isset($this->attributes->{$attribute}) || property_exists($this->attributes, $attribute);
    }

    /**
     * Check if has a relation
     *
     * @param $relation
     * @return bool
     */
    public function hasRelation($relation)
    {
        if (is_array($this->relations)) {
            return isset($this->relations[$relation]);
        }
        return property_exists($this->relations, $relation);
    }

    /**
     * Set relation
     *
     * @param $relation
     * @param $value
     */
    public function setRelation($relation, $value)
    {
        if (is_array($this->relations)) {
            $this->relations[$relation] = $value;
        } else {
            $this->relations->{$relation} = $value;
        }
        return $this;
    }

    /**
     * Get a relation
     *
     * @param $relation
     * @return mixed
     */
    public function getRelation($relation)
    {
        return is_array($this->relations) ? $this->relations[$relation] : $this->relations->{$relation};
    }

    /**
     * Default connection
     */
    public function getDefaultConnection()
    {
        $this->connection = Connection::newConnection();
    }

    /**
     * @return mixed
     */
    public function getConnection() : Connection
    {
        return $this->connection;
    }

    /**
     * @param mixed $connection
     */
    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * Get last inserted id in the table model
     *
     * @return int
     */
    public function lastInsertedId()
    {
        $id = $this->connection->table($this->getTable())
                                ->max('devedor_id', 'id')
                                ->get()->first()->id;

        return TypeHint::castType($id);
    }

    /**
     * @param array $attributes
     * @return Model
     */
    public function newInstance($attributes = [])
    {

        $new = new static((array) $attributes);

        $new->setConnection(
            $this->getConnection()
        );

        $new->setTable($this->getTable());

        $new->setPrimaryKey($this->getPrimaryKey());

        return $new;
    }


    /**
     * Clone the instance
     *
     * @param array $attributes
     * @return Model
     */
    public function cloneInstance($attributes = [])
    {
        $new = clone $this;

        $new->clearAttr();

        $new->setConnection(
            $this->getConnection()
        );

        $new->setTable($this->getTable());

        $new->setPrimaryKey($this->getPrimaryKey());

        $new->setAttributes((array) $attributes);

        return $new;
    }

    /**
     * Clear attributes
     */
    public function clearAttr()
    {
        $this->attributes = (object)[];
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayObject((array)$this->attributes);
    }

    /**
     * Mass Create of An Record
     * @throws Exception
     * @param array $create
     * @return boolean
     */
    public function create(array $create): bool
    {
        if (empty($this->fillable)) {
            throw new \Exception('No columns available for mass insertion');
        }
        $fillable = $this->fillable;
        foreach ($create as $insertColumn => $value) {
            if (array_search($insertColumn, $fillable) !== false) {
                continue;
            } else {
                throw new \Exception("Column $insertColumn not available for mass insertion.");
            }
        }
        return $this->serve()->insert($create);
    }

    /**
     * Retrieve all records from database
     *
     * @return  Collection
     */
    public static function all()
    {
        $new = new static();

        return $new->serve()->get();
    }

    /**
     * Find a record in database
     *
     * @param int|string $id
     * @return void
     */
    public function find($id)
    {
        $instance = $this->where($this->getPrimaryKey(), $id)->get();
        return $instance instanceof self ? $instance : $this;
    }

    /**
     * Create/Save a new record in database
     *
     * @param array $data
     * @return boolean
     */
    public function save(array $data = []): bool
    {
        if (!empty($update)) {
            $this->setAttributes($update);
        }

        return $this->serve(false)->insert($this->getAttributes());
    }


    /**
     * Update record in database
     *
     * @param array $columns
     * @param array $conditions
     * @return boolean
     */
    public function update(array $update = [])
    {
        if (!empty($update)) {
            $this->setAttributes($update);
        }

        $primary = $this->getPrimaryKey();

        return $this->serve(false)
                    ->where($primary, $this->{$primary})
                    ->update($this->getAttributes());
    }

    /**
     * Delete a record in database
     *
     * @param int $id
     * @param mixed $columnCondition
     * @return boolean
     */
    public function delete(string $column = '', $id = '', $operator = null): bool
    {
        $column = $column ?: $this->getPrimaryKey();

        $id = $id ?: $this->{$this->getPrimaryKey()};

        $operator = $operator ?: '=';

        return $this->serve(false)
                    ->delete([$column, $operator, $id]);
    }


    /**
     * Get first row of a table
     * @return mixed
     */
    public function first()
    {
        return $this->all()->first();
    }

    /**
     * Get last row of a table
     * @return Model
     */
    public function last()
    {
        return $this->all()->last();
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toJson();
    }

    /**
     * When object is printed
     * @return false|string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Returns a json representation
     * @return false|string
     */
    public function toJson($options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    {
        $this->dateToString();
        return json_encode($this->getAttributes(), $options);
    }

    /**
     * Stringfy date
     */
    public function dateToString()
    {
        foreach ($this as $key => $value) {
            if ($value instanceof Date) {
                $this->attributes->{$key} = $this->getAttribute($key)->getDate();
            }
        }
    }

    /**
     * Serves the query builder
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return Builder
     */
    public static function where($column, $operator = null, $value = null, $boolean = 'where') : Builder
    {
        $new = new static();

        return $new->serve()->where($column, $operator, $value, $boolean);
    }

    public function __call($name, $arguments)
    {
         return $this->serve()->{$name}(...$arguments);
    }

    public function table($table)
    {
        $this->setTable($table);

        $builder =  $this->getConnection()->newBuilder($this)->from($this->getTable());

        return $builder;
    }

    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    public function __get($name)
    {
        if ($this->hasAttribute($name)) {
            return $this->getAttribute($name);
        } elseif ($this->hasRelation($name)) {
            return $this->getRelation($name);
        }
        return null;
    }

    public function __isset($name)
    {
        return property_exists($this->attributes, $name);
    }

    public function __unset($name)
    {
        if ($this->hasAttribute($name)) {
            unset($this->attributes->{$name});
        }
    }

    /**
     * Serves the model
     *
     * @param bool $with
     * @return Builder
     */
    public function serve($with = true) : Builder
    {
        return $this->getConnection()->table($this->getTable(), $with ? $this : null);
    }

    public function isEmpty()
    {
        return empty($this->getAttributes() );
    }

    public function count()
    {
        return count($this->getAttributes());
    }

    /**
     * Undocumented function
     *
     * @param array $data
     * @return integer
     */
    public function saveWithId(array $data = [])
    {
        if (!empty($update)) {
            $this->setAttributes($update);
        }

        return $this->serve(false)->insertWithLastInsertedId($this->getAttributes());
    }

}