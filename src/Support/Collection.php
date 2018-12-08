<?php

namespace Fenix\Support;

use Fenix\Traits\Support\Collection\DotNotation;
use Fenix\Traits\Support\Collection\SpecificCase;
use Fenix\Contracts\Support\Arrayable;
use Fenix\Contracts\Support\Jsonable;
use OutOfRangeException;
use IteratorAggregate;
use JsonSerializable;
use ArrayIterator;
use ArrayAccess;
use Countable;
use Traversable;

class Collection implements ArrayAccess, Arrayable, Jsonable,
                            JsonSerializable, Countable, IteratorAggregate
{
    use SpecificCase;
    use DotNotation;

    protected $items;

    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }
                              
    public function __get($name)
    {
        if ($this->hasKey($name)) {
            return $this->get($name);
        }
        return null;
    }

    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Create new collection from static context
     * @param array $items
     * @return \static
     */
    public  static function create($items = [])
    {
        return new static($items);
    }

    /**
     * Return all items in collection
     *
     * @return mixed
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Get an item from collection and remove it from collection
     * returning the item
     * @param $name
     * @return mixed
     */
    public function pull($name)
    {
        $item = $this->get($name);
        $this->forget($name);
        return $item;
    }

    /**
     * Increase a value within the collection
     * @param $name
     * @param $value
     * @return $this
     */
    public function increase($name, $value)
    {
        if (!$this->hasKey($name)) {
            throw new OutOfRangeException("The key $name was not found.");
        }
        $item = $this->get($name) + $value;
        $this->put($name, $item);

        return $this;
    }

    /**
     * Add a new value in the collection
     * Only add new keys
     * @param string|int $key
     * @param mixed $value
     * @throws \OutOfBoundsException
     * @return void
     */
    public function add($key, $value)
    {
        if ($this->hasKey($key)) {
            throw new \OutOfBoundsException("The key $key already exists");
        }
        if (strpos($key, '.')) {
            $this->dotNotation($key, $value);
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Remove a value in the collection
     *
     * @param string|integer $name
     */
    public function forget($name)
    {
        if (strpos($name, '.')) {
            return $this->remove($name);
        }
        if ($this->hasKey($name)) {
            unset($this->items[$name]);
        }
    }

    /**
     * Clear the collection
     */
    public function clear() : void
    {
        $this->items = [];
    }


    /**
     * Check if a key exist
     * @param $name
     * @return boolean
     */
    public function has($name)
    {
        return $this->hasKey($name);
    }

    /**
     * Check if a key exists in the collection
     * @param string|int $name
     * @return boolean
     */
    public function hasKey($name)
    {
        if (strpos($name, '.') !== false) {
            
            $items = &$this->items;
            
            $names = explode('.', $name);
            $lastItem = array_pop($names);
            foreach ($names as $name) {
                $items = &$items[$name];
            }
            return isset($items[$lastItem]);
        
        }
        return array_key_exists($name, $this->items);
    }

    /**
     * Check if a value exists in the collection
     *
     * @param string|int $name
     * @return boolean
     */
    public function hasValue($value)
    {
        return array_search($value, $this->items) !== false;
    }

    /**
     * Check if a value exists in collection
     * @param array $values
     * @return bool
     */
    public function contains(...$values)
    {
        foreach ($values as $index => $value) {
            if (array_search($value, $this->items) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if the collection is empty
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }

    /**
     * Calculate the sum of values in an array
     * @return integer
     */
    public function sum()
    {
        return array_sum($this->items);
    }

    /**
     * Get the collection keys
     * @return array
     */
    public function keys()
    {
        return array_keys($this->items);
    }

    /**
     * Get onlu the collection values
     * @return array
     */
    public function values()
    {
        return array_values($this->items);
    }

    /**
     * Run a callback function on each item
     * @param callable $callback
     * @return \static
     */
    public function map(callable $callback)
    {
        $keys = $this->keys();

        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * Run a filter callback function to all items
     * @param callable|null $callback
     * @param int $options
     * @return static|array
     */
    public function filter(callable $callback = null, $options = ARRAY_FILTER_USE_BOTH)
    {
        if ($this->isEmpty()) {
            return [];
        }
        if (!is_null($callback)) {
            return new static(array_filter($this->items, $callback, $options));
        }
        return new static(array_filter($this->items, 'boolval', $options));
    }


    /**
     * Return an array representation
     * @return array The values in array
     */
    public function toArray()
    {
        return $this->items;
    }

    /**
     * Transforna todas as strings da coleção em caixa alta
     *
     * @param string|int $key
     * @return static
     */
    public function toLower($key = '')
    {
        //if ($key === 'values' || $key === 'keys') {
        if ($key === '') {
            return $this->map(function($value) {
                return strtolower($value);
            });
        }
        $items = new static();
        $item =  $this->onEach(function($item, $itemKey) use ($key, $items){
            if ($key === 'keys') {
                $items->add(strtolower($itemKey), $item);
            } elseif($key === 'values') {
                $items->add($itemKey, strtolower($item));
            }
        });
        return $items;
    }

    /**
     * Implode the items into a string
     * @param mixed ...$values
     * @return string
     */
    public function implode(...$values)
    {
        
        if (count($values) === 1) {
            return implode($values[0], $this->items);
        } 
        if ($values[0] == 'keys') {
            return implode($values[1], $this->keys());
        } 
        if ($values[0] === 'values') {
            return implode($values[1], $this->values());
        }
    }

    /**
     * Executa uma função de callback em cada item da coleção
     *
     * @param callable $callback
     * @return $this
     */
    public function onEach(callable $callback)
    {
        foreach ($this->items as $key => &$value) {
            if ($callback($value, $key) === false) {
                break;
            }
        }
        return $this;
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }


    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Return a json representation of the object
     * @return string The object
     */
    public function toJson($options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }
            return $value;
        }, $this->items);
    }


    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param  mixed  $items
     * @see Illuminate\Support\Collection at Laravel
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }
        return (array) $items;
    }

    /**
     * The erase method removes an item from the collection by its key:
     *
     * @param $toErase
     * @return static
     */
    public function erase($toErase)
    {
        if (self::is_multidimensional($this->items)) {
            return $this->filter(function ($value, $key) use ($toErase) {
                return erasing($value, $toErase);
            });
        }
        return erasing($this->items, $toErase);
    }

    /**
     * Splits an array into arrays with size elements. The last chunk may contain
     * less than size elements.
     *
     * This method is an alias to chunk()
     *
     * @param int $size              | Size of new collection
     * @param boolean $preserveKeys  | When set to TRUE keys will be preserved. Default is FALSE which
     *                               | will reindex the chunk numerically
     * @return static
     */
    public function split(int $size, $preserveKeys = false)
    {
        return $this->chunk($size, $preserveKeys);
    }
    /**
     * Chunks an array into arrays with size elements. The last chunk may contain
     * less than size elements.
     * @param int $size              | Size of new collection
     * @param boolean $preserveKeys  | When set to TRUE keys will be preserved. Default is FALSE which
     *                               | will reindex the chunk numerically
     * @return static
     */
    public function chunk(int $size, $preserveKeys = false)
    {
        return new static(array_chunk($this->items, $size, $preserveKeys));
    }
    /**
     * Rotate the keys in the collection
     * @param int $rotations
     * @return static
     */
    public function rotate(int $rotations)
    {
        for ($r = $this->normalizeRotations($rotations); $r > 0; $r--) {
            array_push($this->items, array_shift($this->items));
        }
        return new static($this->items);
    }
    /**
     * @param $search
     * @param $replace
     * @return Collection
     */
    public function replaceKeys($search, $replace)
    {
        $keys = str_replace($search, $replace, $this->keys());
        return $this->map(function($items, $keys) use($search, $replace){
            $newItem = [];
            foreach ($items as $key => $item) {
                if ($key == $search) {
                    $key = str_replace($search, $replace, $key);
                    $newItem[$key] = $item;
                } else {
                    $newItem[$key] = $item;
                }
            }
            return $newItem;
        });
    }
    /**
     * Filter the collection base on key => value or
     * If is a collection of data from database
     * It can filter through a condition like column condition value
     *
     * @param string $index
     * @param  ...$amountCondition
     * @return static
     */
    public function where($index, ...$amountCondition)
    {
        $conds = [$index, $amountCondition[0], $amountCondition[1] ?? null];
        return $this->filter(function ($value, $key) use ($index, $amountCondition, $conds) {
            if (count($amountCondition) == 1) {
                return $value[$index] ?? $value->{$index} == $amountCondition[0];
            }
            return $this->conditional($value, $conds);
        });
    }
    /**
     * Pluck all keys/properties from all items inside collection
     * @param $value
     * @param null $key
     * @return Collection
     */
    public function pluck($value, $key = null)
    {
        return $this->map(function($items) use($value) {
            return is_array($items) ? $items[$value] : $items->$value;
        });
       throw new OutOfRangeException($key);
    }
    /**
     * Remove and return the fist element in the collection
     * @return mixed
     */
    public function shift()
    {
        $shifted = array_shift($this->items);
        return $shifted;
    }
    /**
     * Adds values to the front of the list, moving all the current values forward to
     * make room for the new values
     * @param  $values
     * @return static
     */
    public function unshift(...$values)
    {
       // if ($values) {
            $items = array_merge($values, $this->items);
            return new static($items);
        //}
        //return new static($items ?? $this->items);
    }
    /**
     * Remove an return the last element in the collection
     * @return mixed
     */
    public function pop()
    {
        $popped = array_pop($this->items);
        return $popped;
    }
    /**
     * Push new items into the end of the collection
     * @param $items
     */
    public function push($items) : void
    {
        $this->items[] = $items;
    }
    /**
     * Reduces the map to a single value using a callback function
     *
     * @param callable $callback | mixed callback ( mixed $carry , mixed $key , mixed $value )
     *                           | The return value of the previous callback, or initial if it's the first iteration.
     *                           | The value of the current iteration.
     *
     * @param null $initial | The initial value of the carry value. Can be NULL.
     * @return mixed             | The return value of the final callback.
     */
    public function reduce(callable $callback, $initial = null)
    {
        return new static(array_reduce($this->items, $callback, $initial));
    }
    /**
     * Merge an array inside the collection
     * @param array|\Traversable $merge
     * @return Collection
     */
    public function merge($merge = null)
    {
        $items = $this->items;

        $merge = $merge instanceof self ? $merge->toArray() : func_get_args();

        return new static(array_merge($items, $merge));
    }
    /**
     * Merge recursively an array inside the collection
     * @param mixed ...$merge
     * @return static
     */
    public function merge_recursive(...$merge)
    {
        $merged = array_merge_recursive($this->items, $merge);
        return new static($merged);
    }
    /**
     * Exchanges all keys with their associated values in an collection
     * @return Collection|static
     */
    public function flip()
    {
        if (self::is_multidimensional($this->items)) {
            return $this->map(function($item) {
                return array_flip($item);
            });
        }
        return new static(array_flip($this->items));
    }
    /**
     * Return a new collection with their items in reverse order
     * @param bool $preserKeys  | If their keys will be preserved
     * @return static
     */
    public function reverse(bool $preserKeys = false)
    {
        return new static(array_reverse($this->items, $preserKeys));
    }
    /**
     *  Searches the array for a given value and returns the first corresponding key if successful
     * @param mixed needle
     * @param bool $strict
     * @return static
     */
    public function search(mixed $needle, bool $strict = false)
    {
        $items = $this->items;
        return array_search($needle, $items, $strict);
    }
    /**
     * Find items in the collection by their value an return the corresponding array | object
     * @param $search
     * @param bool $strict
     * @return static
     */
    public function find($search, bool $strict = false)
    {
        if (!self::is_multidimensional($this->items)) {
            $key = array_search($search, $this->items, $strict);
            return new static($this->items[$key]);
        }
        return $this->filter(function($items, $key) use($search){
            foreach ($items as $key =>$item) {
                if ($item === $search) {
                    return $items;
                }
            }
        });
    }
    /**
     * Extract a partial from collection
     * @param int $offset
     * @param $length
     * @return static
     */
    public function slice(int $offset, int $length = null)
    {
        return new static(array_slice($this->items, $offset, $length));
    }
    /**
     * Computes the difference of arrays with additional index check and return an array with different
     *
     * @param array $array
     * @return array
     */
    public function differsAssoc(array $array)
    {
        $items = $this->toArray();
        return array_diff_assoc($items, $array);
    }
    /**
     * Computes the difference of arrays with additional index using keys
     *
     * @param array $array
     * @return array
     */
    public function differsKey(array $array)
    {
        $items = $this->toArray();
        return array_diff_key($items, $array);
    }
    /**
     * Return the first element in the collection
     *
     * with the values
     * @return static
     */
    public function firstCollect()
    {
        return new static($this->first());
    }
    /**
     * Return the first element in the collection
     * with the values
     * @return static
     */
    public function lastCollect()
    {
        return new static($this->last());
    }
    /**
     * Explode an item from collection
     *
     * @param  $key
     * @param  $separator
     * @return array
     * @throws Exception
     */
    public function explode($key, $separator) : array
    {
        if (self::is_multidimensional($this->items) || is_array($this->items[$key])) {
            throw new Exception("Cannot explode arrays only strings can be explode into a new array.");
        }
        return explode($separator, $this->items[$key]);
    }
    /**
     *  Removes duplicate values from an array
     *
     * Takes an input array and returns a new array without duplicate values.
     * Note that keys are preserved. If multiple elements compare equal under the
     * given sort_flags, then the key and value of the first equal element
     * will be retained.
     *
     * NOTE: Two elements are considered equal if and only if (string) $elem1 === (string)
     * $elem2 i.e. when the string representation is the same, the first element
     * will be used.
     *
     * @param int $flags
     * @return Collection
     */
    public function unique($flags = SORT_STRING)
    {
        return new static(array_unique($this->items, $flags));
    }
//    public function toUpper()
//    {
//        return $this->map(function($items) {
//            return array_map(function($item){
//                return strtoupper($item);
//            }, $items);
//        });
//    }
//
//    public function toLower()
//    {
//        return $this->map(function($items) {
//            return array_map(function($item){
//                return strtolower($item);
//            }, $items);
//        });
//    }
    public function capitalize()
    {
        return $this->map(function($items) {
            return array_map(function($item){
                return ucwords($item);
            }, $items);
        });
    }

    public function empty($name)
    {
        return empty($this->items[$name]) || is_null($this->items[$name]);
    }

    public function isArray($name)
    {
        if ($name === 'first') {
            return is_array($this->first());
        } elseif ($name === 'last') {
            return is_array($this->last());
        }
        return is_array($this->items[$name]);
    }

    public function isObject($name)
    {
        if ($name === 'first') {
            return is_object($this->first());
        } elseif ($name === 'last') {
            return is_object($this->last());
        }
        return is_object($this->items[$name]);
    }

    /**
     * Returns the first value in the set
     * @return mixed
     */
    public function first()
    {
        $items = $this->items;
        if ($this->isEmpty()) {
            throw new \UnderflowException("Cannot get the first element on a empty ". get_class($this)  . ".");
        }
        $items = array_values($items);

        return $items[0];
    }
    /**
     * Returns the last value in the set
     * @return mixed
     */
    public function last()
    {
        if ($this->isEmpty()) {
            throw new \UnderflowException("Cannot get the last element on a empty ". get_class($this)  . ".");
        }
        $index = $this->count() - 1;
        $items = $this->items;
        return $items[$index];
    }

    /**
     * Gets an item from collection
     * @param $key
     * @return mixed|static
     */
    public function value($key)
    {
        if (!$this->has($key)) {
            throw new \OutOfBoundsException("
                    You can only get items that exist in the collection. Key $key not found."
            );
        }
        $items = $this->get($key);
        if (is_array($items) && count($items) > 1) {
            return new static($items);
        }
        return $items;
    }

    public function getByKeyValue($key, $value)
    {
        return $this->filter(function($item) use($key, $value){
           return $item->{$key} == $value;
        });
    }

     /**
     * Group the collection by a key
     *
     * @param  $group
     * @return static
     */
    public function groupBy($group)
    {
        $results = [];
        foreach ($this->items as $key => $value) {
            $isArray = is_array($value) ? true : false;
            $value = $isArray ? (object) $value : $value;
            if (property_exists($value, $group)) {
                foreach ($value as $property => $obValue) {
                    if ($value->$group == $obValue) {
                        $results[$obValue][] = $isArray ? (array) $value : $value;
                    }
                }
            }
        }
        return new static($results);
    }

    /**
     * Check if in the collection has a object
     * @param string $instance
     * @return bool
     */
    public function hasInstance(string $instance)
    {
        foreach ($this->items as $item) {
            $class = get_class($item);
            if ($instance == $class) {
                return true;
            }
        }
        return false;
    }
    
                              
    public function ksort($options = null)
    {
        ksort($this->items, $options);
        return $this;
    }

    /**
     * Reject values that doesn match
     * @param $callback
     * @param $value
     * @return array|Collection
     */
    public function reject($callback, $value)
    {
        if (!is_callable($callback)) {
            return $this->filter(function($item, $key) use($value, $callback){
               return $item->{$callback} != $value;
            });
        }
    }


    /**
     * Get only values that match
     * @param $callback
     * @param $value
     * @return array|Collection
     */
    public function only($callback, $value)
    {
        if (!is_callable($callback)) {
            return $this->filter(function($item, $key) use($value, $callback){
                return $item->{$callback} == $value;
            });
        }
    }
}
