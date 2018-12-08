<?php

namespace Fenix\Contracts\Support;

use InvalidArgumentException;
use OutOfRangeException;
use UnderflowException;

/**
 * Describes the behaviour of values arranged in a single, linear dimension. Some languages refer
 * to this as a "List". It’s similar to an array that uses incremental integer keys,
 * with the exception of a few characteristics:
 * @package Meltdown\Contracts\DS
 * @see Sequence
 * @see http://php.net/manual/pt_BR/class.ds-sequence.php
 */
interface Listable extends Capacity
{

    /**
     * Updates all values by applying a callback function to each value in the sequence
     * @param callable $callback
     * @return void
     */
    public function apply ( callable $callback ) : void;

    /**
     * Check if a values exists within the container/collection/array
     * @param mixed ...$values
     * @return bool
     */
    public function contains ( ...$values ) : bool;

    /**
     * Creates a new commonArray using a callable to determine which values to include
     * @param int $options
     * @param callable $callback
     * @return Listable
     */

    public function filter ( callable $callback, $options = 0) : Listable;

    /**
     * Attempts to find a value's index
     * @param mixed $value
     * @return mixed
     */
    public function find ( $value );

    /**
     * Returns the value at a given index
     * @param int $index
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function get ( int $index );

    /**
     * Inserts values into the sequence at a given index
     *
     * Note: You can insert at the index equal to the number of values
     * @param int $index
     * @param mixed ...$values
     * @return mixed
     */
    public function insert ( int $index,  ...$values );

    /**
     * Joins all values together as a string using an optional separator between each value.
     * @param string $glue
     * @return string
     */
    public function join(string $glue) : string;


    /**
     * Returns the first element
     * @return  mixed
     */
    public function first ();

    /**
     * Returns the last value in the sequence.
     * @throws UnderflowException if empty.
     * @return  mixed
     */
    public function last ();

    /**
     * Returns the result of applying a callback function to each value in the sequence.
     * @param callable $callback
     * @return Listable
     */
    public function map ( callable $callback, $keys = null) : Listable;

    /**
     * Returns the result of adding all given values to the sequence
     *
     * Note: The current instance won't be affected.
     *
     * @param mixed $values A traversable object or an array.
     *
     * @return Listable
     */
    public function merge ( $values ) : Listable;


    /**
     * Removes and returns the last value
     * @return array
     */
    public function pop () ;

    /**
     * Adds values to the end of the sequence
     * @param mixed ...$values
     * @return mixed
     */
    public function push (...$values );

    /**
     * Reduces the sequence to a single value using a callback function
     * @param callable $callback
     * @param mixed $initial
     * @return mixed
     */
    public function reduce ( callable $callback, $initial);

    /**
     * Removes and returns a value by index
     * @param $index
     * @return mixed
     */
    public function remove( $index, $default);

    /**
     * Reverses the list in-place
     * @return void
     */
    public function reverse () : void;

    /**
     * Returns a reversed copy
     * @return Listable
     */
    public function reversed () : Listable;

    /**
     * Rotates the list by a given number of rotations, which is equivalent to successively calling
     * $list->push($sequence->shift()) if the number of rotations is positive, or
     * $slist->unshift($list->pop()) if negative.
     * @param int $rotations
     * @return mixed
     */
    public function rotate (int $rotations );

    /**
     * Uptade a existent value at a given index
     * @param int $index
     * @param mixed $value
     * @throws OutOfRangeException | If the index is not valid
     * @return mixed
     */
    public function set ( int $index , $value );

    /**
     * Removes and returns the first value
     * @return mixed
     */
    public function shift () ;


    /**
     * Adds values to the front of the list, moving all the current values forward to
     * make room for the new values
     * @param  $values
     * @return void
     */
    public function unshift (... $values ) : void;

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
    public function slice ( int $index,  int $length ) : Listable;

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
    public function sort ( callable $comparator);

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
    public function sorted ( callable $comparator ) : Listable;

    /**
     * Returns the sum of all values in the list
     *
     * Note: Arrays and objects are considered equal to zero when calculating the sum.
     * @return int
     */
    public function sum () : int;

}