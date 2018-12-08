<?php

namespace Fenix\Traits\Support\Collection;

use Fenix\Contracts\Support\Listable;
use InvalidArgumentException;
use OutOfBoundsException;
use OutOfRangeException;
use UnderflowException;
use IteratorAggregate;

/**
 * A ArrayList implementes Listable and  describes the behaviour of values arranged in a single, linear dimension.
 * Some languages refer to this as a "List". It’s similar to an array that uses incremental integer keys, with the
 * exception of a few characteristics:
 *  Values will always be indexed as [0, 1, 2, …, size - 1].
 *  Only allowed to access values by index in the range [0, size - 1].
 *
 * Use cases:
 *   Wherever you would use an array as a list (not concerned with keys).
 *   A more efficient alternative to SplDoublyLinkedList and SplFixedArray.
 *
 * @package Meltdown\DS
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 * @see http://php.net/manual/pt_BR/class.ds-sequence.php
 *
 */
trait GenericList
{
    use Generic;
    use AcessorOfArray;

    protected $list = [];

    public function __construct(array $values = [])
    {
        if (!empty($values)) {
            $this->pushAll($values);
        }
    }

    /**
     * Returns a shallow copy of the collection.
     * @return Collectable
     */
    public function copy()
    {
       return $this->duplicate();
    }

    /**
     * Check if the collection has a key
     * @return bool
     */
    public function has($key): bool
    {
       return isset($this->list[$key]);
    }

    /**
     * Returns the internal array stored values
     * @return mixed
     */
    public function &getInternalArrayValues()
    {
        return $this->list;
    }

    /**
     * Removes all values from the collection.
     * @return void
     */
    public function clear(): void
    {
        $this->list = [];
        $this->checkCapacity();
    }


    /**
     * Updates all values by applying a callback function to each value in the sequence
     * @param callable $callback
     * @return void
     */
    public function apply(callable $callback): void
    {
        foreach ($this->list as $key => $value) {
            $this->list[$key] = $callback($value);
        }
    }

    /**
     * Check if a values exists within the container/collection/array
     * @param mixed ...$values
     * @return bool
     */
    public function contains(...$values): bool
    {
        foreach ($values as $value){
            if ($this->find($value) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Creates a new commonArray using a callable to determine which values to include
     * @param int $options
     * @param callable $callback
     * @return Listable
     */
    public function filter(callable $callback = null, $options = 0): Listable
    {
        return new static(array_filter($this->list, $callback ?: 'boolval', $options ));
    }

    /**
     * Attempts to find a value's index
     * @param mixed $value
     * @return mixed
     */
    public function find($value)
    {
        return array_search($value,$this->list, true);
    }

    /**
     * Returns the value at a given index
     * @param int $index
     * @return mixed
     */
    public function get(int $index)
    {
        if (!$this->has($index)) {
            throw new InvalidArgumentException("Index '{$index}' not found the list.");
        }
        return $this->list[$index];
    }

    /**
     * Inserts values into the sequence at a given index
     *
     * Note: You can insert at the index equal to the number of values
     * @param int $index
     * @param mixed ...$values
     * @return mixed
     */
    public function insert(int $index, ...$values)
    {
        if (!$this->validKey($index) && $index !== $this->count()) {
            throw new OutOfRangeException(
                "Triyng to get index on a illegal context. The Index must be 
                absolute value and be lower than "  . $this->count()
            );
        }
        array_slice($this->list, $index, 0, $values);
    }

    /**
     * Joins all values together as a string using an optional separator between each value.
     * @param string $glue
     * @return string
     */
    public function join(string $glue = ''): string
    {
        return implode($glue, $this->list);
    }

    /**
     * Returns the result of applying a callback function to each value in the sequence.
     * @param callable $callback
     * @return Listable
     */
    public function map(callable $callback, $keys = null): Listable
    {
        return new static(array_map($callback, $this->list));
    }

    /**
     * Returns the result of adding all given values to the sequence
     *
     * Note: The current instance won't be affected.
     *
     * @param mixed $values A traversable object or an array.
     *
     * @return Listable
     */
    public function merge($values): Listable
    {
        $copy = $this->copy();
        $copy->pushAll($values);
        return $copy;
    }

    /**
     * Removes and returns the last value
     * @return array
     */
    public function pop()
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Cannot pop an element on a empty ". get_class($this)  . ".");
        }
        $popped = array_pop($this->list);
        $this->checkCapacity();

        return $popped;
    }

    /**
     * Adds values to the end of the list
     * @param mixed ...$values
     * @return mixed
     */
    public function push(...$values)
    {
        $this->pushAll($values);

    }

    /**
     * Pushes all values of either an array or traversable object.
     * @param $values
     */
    private function pushAll($values)
    {
        foreach ($values as $value) {
            $this->list[] = $value;
        }
        $this->checkCapacity();
    }

    /**
     * Reduces the sequence to a single value using a callback function
     * @param callable $callback
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial)
    {
        return array_reduce($this->list, $callback, $initial);
    }

    /**
     * Removes and returns a value by index
     * @param $index
     * @return mixed
     */
    public function remove($index, $default)
    {
        if ( ! $this->validIndex($index)) {
            throw new OutOfRangeException(
                "Triyng to get index on a illegal context. The Index must be 
                absolute value and be lower than "  . $this->count()
            );
        }
        $value = array_splice($this->array, $index, 1, null)[0];

        $this->checkCapacity();

        return $value;
    }

    /**
     * Reverses the list in-place
     * @return void
     */
    public function reverse(): void
    {
        $this->list = array_reverse($this->list);
    }

    /**
     * Returns a reversed copy
     * @return Listable
     */
    public function reversed(): Listable
    {
        $reversed =  $this->copy();
        $reversed->reverse();
        return $reversed;
    }

    /**
     * Rotates the list by a given number of rotations, which is equivalent to successively calling
     * $list->push($sequence->shift()) if the number of rotations is positive, or
     * $slist->unshift($list->pop()) if negative.
     * @param int $rotations
     * @return mixed
     */
    public function rotate(int $rotations)
    {
        for ($r = $this->normalizeRotations($rotations); $r > 0; $r--) {
            array_push($this->array, array_shift($this->array));
        }
    }

    /**
     * Converts negative or large rotations into the minimum positive number
     * of rotations required to rotate the sequence by a given $r.
     * @see https://github.com/php-ds/polyfill/blob/master/src/Traits/GenericSequence.php
     */
    private function normalizeRotations(int $r)
    {
        $n = count($this);

        if ($n < 2) return 0;

        if ($r < 0) return $n - (abs($r) % $n);

        return $r % $n;
    }

    /**
     * Uptade a existent value at a given index
     * @param int $index
     * @param mixed $value
     * @throws OutOfRangeException | If the index is not valid
     * @return mixed
     */
    public function set(int $index, $value)
    {
        if (!$this->validKey($index)) {
            throw new OutOfRangeException(
                "To update a value you must enter a existent index."
            );
        }
        $this->list[$index] = $value;
    }

    /**
     * Removes and returns the first value
     * @return mixed
     */
    public function shift()
    {
        if ($this->isEmpty()) {
            throw new \UnderflowException("Cannot shift an element on a empty ". get_class($this)  . ".");
        }
        $shifted = array_shift($this->list);

        $this->checkCapacity();

        return $shifted;
    }

    /**
     * Adds values to the front of the list, moving all the current values forward to
     * make room for the new values
     * @param  $values
     * @return void
     */
    public function unshift(...$values): void
    {
        if ($values) {
            $this->list = array_merge($values, $this->list);
            $this->checkCapacity();
        }
    }

    /**
     * Returns a sub-list of a given range
     *
     * index: The index at which the sub-list starts
     * If positive, the list will start at that index in the list.
     * If negative, the list will start that far from the end.
     *
     * length: If a length is given and is positive, the resulting list will have up to that many
     * values in it. If the length results in an overflow, only values up to the end of the
     * list will be included. If a length is given and is negative, the list will stop
     * that many values from the end. If a length is not provided, the resulting list will
     * contain all values between the index and the end of the list.
     *
     * @param int $index
     * @param int $length
     * @return CommonArray
     */
    public function slice(int $index, int $length = null): Listable
    {
        if (is_null($length)) {
            $length = $this->count();
        }
        return new static(array_slice($this->list, $index, $length));
    }

    /**
     * Sorts the list in-place, using an optional comparator function
     *
     * Note: The comparison function must return an integer less than, equal to, or greater than zero if the first argument is
     * considered to be respectively less than, equal to, or greater than the second. Note that before
     * PHP 7.0.0 this integer had to be in the range from -2147483648 to 2147483647
     *
     * Caution:
     * Returning non-integer values from the comparison function, such as float, will result in an internal
     * cast to integer of the callback's return value. So values such as 0.99 and 0.1 will both be cast
     * to an integer value of 0, which will compare such values as equal.
     *
     * @param callable $comparator
     * @return mixed
     */
    public function sort(callable $comparator = null)
    {
        if ($comparator) {
            usort($this->array, $comparator);
        } else {
            sort($this->array);
        }
    }

    /**
     * Returns a sorted copy, using an optional comparator function
     *
     * Note: The comparison function must return an integer less than, equal to, or greater than zero if the first argument is
     * considered to be respectively less than, equal to, or greater than the second. Note that before
     * PHP 7.0.0 this integer had to be in the range from -2147483648 to 2147483647
     *
     * Caution:
     * Returning non-integer values from the comparison function, such as float, will result in an internal
     * cast to integer of the callback's return value. So values such as 0.99 and 0.1 will both be cast
     * to an integer value of 0, which will compare such values as equal.
     *
     * @param callable $comparator
     * @return mixed
     */
    public function sorted(callable $comparator = null): Listable
    {
        $sorted =  $this->copy();
        $sorted->sort($comparator);
        return $sorted;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
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
        if ($offset === null) {
            $this->push($value);
        } else {
            $this->set($offset, $value);
        }
    }


    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        if (is_integer($offset) && $this->validIndex($offset)) {
            $this->remove($offset);
        }
    }

     /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return is_integer($offset)
            && $this->validIndex($offset)
            && $this->get($offset) !== null;
    }

 /**
     * @inheritdoc
     */
    public function &offsetGet($offset)
    {
        if ( ! $this->validIndex($offset)) {
            throw new OutOfRangeException();
        }
        return $this->array[$offset];
    }
}