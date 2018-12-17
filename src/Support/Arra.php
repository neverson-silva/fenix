<?php

namespace Fenix\Support;

use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Traversable;
use Countable;

class Arra implements ArrayAccess, IteratorAggregate, Countable
{
    private $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * Get all items in the array
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }


    /**
     * Get the array values
     *
     * @return array
     */
    public function values()
    {
        return array_values($this->items);
    }


    /**
     * Get the first value
     * @return mixed
     */
    public function first()
    {
        $items = $this->values();

        return $items[0];
    }

    /**
     * Get the last value
     * @return mixed
     */
    public function last()
    {
        $items = $this->values();

        return end($items);
    }

    /**
     * add a key value to array
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function set($name, $value)
    {
        $this->items[$name] = $value;

        return $this;
    }
    /**
     * Check if the key exist in the array
     *
     * @param $name
     * @return boolean
     */
    public function has($name)
    {
        return array_key_exists($name, $this->items);
    }

    /**
     * Check if a value exists
     *
     * @param $name
     * @return bool
     */
    public function exists($name)
    {
        return isset($this->items[$name]);
    }

    /**
     * Get the value from the array
     *
     * @param mixed $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if ($this->has($name)) {

            $items = $this->items[$name];

            return is_array($items) ? new static($items) : $items;
        }
        return $default;
    }

    /**
     * Remove a value from array
     * @param $name
     */
    public function remove(...$name)
    {
        if (count($name) == 1) {
            $name = $name[0];

            if ($this->has($name)) {
                unset($this->items[$name]);
            }
        } else {
            foreach ($name as $n) {
                if ($this->has($n)) {
                    unset($this->items[$n]);
                }
            }
        }

        return $this;
    }

    /**
     * Reverse items
     *
     * @return Arra
     */
    public function reverse()
    {
        return new static(array_reverse($this->items));
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
        return new ArrayIterator($this->items);
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Transform a multidimensional array into a single one
     */
    public function flatten()
    {

    }

    /**
     * Implode
     *
     * @param $glue
     * @return Strin
     */
    public function implode($glue)
    {
        return new Strin(implode($glue, $this->mapString($this->items)));
    }

    /**
     * Map Strin instances
     *
     * @param array $values
     * @return array
     */
    private function mapString(array $values)
    {
        return array_map(function($value) {
            return $value instanceof Strin ? $value->getValue() : $value;
        }, $values);
    }

    /**
     * Map an Arra
     *
     * @param callable $callback
     * @return Arra
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->items);

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
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
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
     * Check wheter the array is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }

    public function countEquals($count)
    {
        return $this->count() === $count;
    }

    /**
     * Add a new value
     *
     * @param mixed $value
     * @return Arra
     */
    public function push($value)
    {
        $this->items[] = $value;

        return $this;
    }
}