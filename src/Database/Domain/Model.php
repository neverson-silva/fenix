<?php

namespace Fenix\Database\Domain;

use Fenix\Contracts\Database\Model as ModelContract;
use Fenix\Database\Collection\Collection;
use Fenix\Database\Manager\Manager as DB;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

class Model extends Relationship implements ModelContract, JsonSerializable, IteratorAggregate
{
    protected $table;
    
    protected $primaryKey;
    
    protected $attributes = [];
    
    protected $fillable;
    
    protected $manager;
    
    public function __construct($attributes = [], $notFirst = false)
    {
        $this->refreshManager();
        $this->setAttributes($attributes, $notFirst);
        if (empty($this->table)) {
            $this->setTable();
        } else {
            $this->manager->setTable($this->getTable());
        }
        if (empty($this->primaryKey)) {
            $this->setPrimaryKey();
        }
        if ($this->loadRelations && !empty($this->attributes)) {
            $this->loadAllRelations();
        }
    }

    private function refreshManager() 
    {
        if (!$this->manager instanceof DB) {
            $this->manager = new DB();
        }
    }

    /**
     * A string representation from object
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
    
    public function __isset($name)
    {
        if (is_object($attributes = ($this->attributes))) {
            return isset($attributes->{$name});
        }
        return isset($attributes[$name]);
    }

    public function __unset($name)
    {
        if (is_object($attributes = ($this->attributes))) {
            unset($attributes->{$name});
        } else {
            unset($attributes[$name]);
        }
    }
    
    /**
     * Dinamically get model atributes.
     *
     * @param  $name
     * @return mixed
     */
    public function __get($name)
    {
        $property = $this->attributes;
        if(property_exists($this, $name)) {
            return $this->$name;
        } elseif ($property instanceof Collection) {
            return  $property->get($name);
        } elseif (is_object($property) && property_exists($property, $name)) {
            return $property->$name;
        } elseif(is_array($property)) {
            return $property[$name] ?? null;
        }
        return null;
    }
    
    /**
     * Set attributes
     *
     * @param string $name Name of the attributes | column name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (!is_object($this->attributes)) {
            $this->attributes =  (object) $this->attributes;
        } 
        $this->attributes->{$name} = $value;
        $this->attributes = (array) $this->attributes;
        
        $this->filterScalar();
    }

    private function filterScalar()
    {
        if (isset($this->attributes['scalar'])) {
            unset($this->attributes['scalar']);
        }
    }
    
    /**
     * Set a model attribute
     *
     * @param stdClass|array $attributes
     * @return void
     */
    public function setAttributes($attributes, $notFirst = false)
    {
        if ($notFirst) {
            $this->attributes = $attributes;
            return;
        } elseif (is_array($attributes) ) {
            $this->attributes = array_shift($attributes);
        } else {
            $this->attributes = $attributes;
        }

    }


    /**
     * Get model attributes
     *
     * @return Collection|object|array
     */
    public function getAttributes($getRelated = true)
    {
        $atributos = (object) $this->attributes;

        if (!$getRelated) {
            if ($this->exclude) {
                foreach ($this->exclude as $exclude) {
                    if (property_exists($atributos, $exclude)) {
                        unset($atributos->{$exclude});
                    }
                }
            }
        }
        return (array) $atributos;
    }
    
    /**
     * Handle dinamically calls
     *
     * @param string $name
     * @param array $args
     * @return Model|Collectable
     */
    public function __call($name, $args)
    {
        $this->refreshManager();
        if (empty($this->getTable())) {
            $this->setTable();
            $this->manager->setTable($this->getTable());
        }

        if ($this->manager->methodExists($name)) {
            $this->manager = $this->manager->$name(...$args);
            if ($this->manager instanceof Collection) {
                $results = $this->getManager();
                if ($results->count() === 1) {
                    $new = clone $this;
                    $new->setTable($this->getTable());
                    $new->setAttributes($results->first(), false);
                    return $new;
                    return new static($results->first());
                }
                return $results->map(function($result){
                    $new = clone $this;
                    $new->setTable($this->getTable());
                    $new->setAttributes( $result, false);
                    return $new;
                });
            }
            $new = clone $this;
            $value = $new->getManager();            
            return $value instanceof DB ? $new : $value;
        } else {
            $method = get_class($this) . '::' . $name;
            throw new \BadMethodCallException("Method $method doesn't exist.");
        }
    }
       
    /**
     * 
     * {@inheritDoc}
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return $this->attributes;
    }
    
    /**
     * Return a json representation of model attributes fetched from database
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->attributes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Return a new model from static context
     * 
     * @return \Fenix\Database\Domain\Model
     */
    public static function make()
    {
        return new static();
    }

    public function getManager()
    {
        return $this->manager;
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
        $this->refreshManager();
        $this->manager->setTable($table);
    }
    
    /**
     * Get table name
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
        } elseif(preg_match('/(l$)/', end($class))) {
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
     * {@inheritDoc}
     * @see 
     */
    public function all(): Collection
    {
        $results =  $this->select('*')->orderBy($this->getPrimaryKey() ?? 1, 'ASC')->get();
        return $results instanceof self ? (new Collection([$results])): $results;
        
    }
    
    /**
     * Find a record in database
     *
     * @param int|string $id
     * @return void
     */
    public function find($id)
    {
        return $this->select('*')->where($this->getPrimaryKey(), $id)->get();
    }

    /**
     * {@inheritDoc}
     * @see \Fenix\Contracts\Database\Model::create()
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
        return $this->insert($create);
        
    }

    /**
     * {@inheritDoc}
     * @see \Fenix\Contracts\Database\Model::delete()
     */
    public function delete(string $column = '', $id = '', $operator = NULL): bool
    {
        $column = $column !== ''   && !is_numeric($column) ? $column : $this->getPrimaryKey();
        $id = $id !== '' ? $id : $this->{$this->getPrimaryKey()};
        return $this->manager->delete($column, $id, $operator);
    }

    public function excludeFrom($data)
    {
        if ($this->exclude) {
            foreach ($this->exclude as $exclude) {
                if (in_array($exclude, $data)) {
                    unset($data[$exclude]);
                } else {
                    if (property_exists($data, $exclude)) {
                        unset($data->{$exclude});
                    }
                }
            }
        }
        return $data;
    }

    /**
     * {@inheritDoc}
     * @see \Fenix\Contracts\Database\Model::save()
     */
    public function save(array $data = []): bool
    {
        if (empty($data)) {
            $data = (array) $this->getAttributes(false);
        }
        $data = $this->excludeFrom($data);

        $insert = $this->manager->insert($data);

        return (int) $insert;
        
    }

    /**
     * {@inheritDoc}
     * @see \Fenix\Contracts\Database\Model::update()
     */
    public function update(array $update = [])
    {
        if (empty($update)) {
            $update = (array) $this->getAttributes(false);
        }
        $id = $this->{$this->getPrimaryKey()};
        return $this->manager->where($this->getPrimaryKey(), $id)->update($update);
        
    }

     public function isEmpty()
    {
        return empty($this->attributes);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->attributes);
    }


}
