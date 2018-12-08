<?php

namespace Fenix\Traits\Support\Collection;

use Fenix\Support\SuperIterator;

trait Generic
{
    /**
     * Gets the Iterator
     * @return CollectionIterator
     */
    public function getIterator()
    {
        return new SuperIterator($this->getInternalArrayValues());
    }

    /**
     * Returns the internal array stored values
     * @return mixed
     */
    abstract public function &getInternalArrayValues();

    /**
     * Removes all values from the collection.
     * @return void
     */
    abstract public function clear() : void;


    /**
     * Returns a shallow copy of the object
     * @return Generic
     */
    public function duplicate()
    {
        return clone $this;
    }

    /**
     * Converts the collection to an array
     * @return array
     */
    public function toArray() : array
    {
        $items = $this->getInternalArrayValues();
        return is_array($items) ? $items : (array) $items;
    }

    /**
     * Return a json representation of the object
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
        return $this->toArray();
    }

    /**
     *  Returns whether the set is empty
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->count() === 0;
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
        $items = $this->getInternalArrayValues();

        return count($items);
    }

    /**
     * Returns the first value in the set
     * @return void
     */
    public function first()
    {
        $items = $this->getInternalArrayValues();

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

        $items = $this->getInternalArrayValues();

        return $items[$index];
    }

    /**
     * Returns the sum of all values in the set
     *
     * NOTE: Arrays and objects are considered equal to zero when calculating the sum.
     *
     * @return int
     */
    public function sum(): int
    {
        //$items = $this->getInternalArrayValues();

        return array_sum($this->toArray());
    }

    /**
     * If the key is valid in the items
     * @param $key
     * @return bool
     */
    protected function validKey($key)
    {
        return $key >= 0 && $key < $this->count();
    }

}