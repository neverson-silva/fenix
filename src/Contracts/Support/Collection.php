<?php
namespace Fenix\Contracts\Support;

use IteratorAggregate;
use Traversable;
use Countable;
/**
 * Base Collection interface
 *
 * @author Neverson Silva
 */
interface Collection extends Traversable, Countable, IteratorAggregate
{
    /**
     * Return all items inside the collection
     * @return array
     */
    public function all() : array;
    /**
     * Return all keys in collection
     * @param bool $toLower  If true all keys must be returned lower case
     * @return array
     */
    public function keys($toLower = false) : array;
    /**
     * Return an array with only the collection values
     * @return array
     */
    public function values() : array ;
    /**
     * Check if a collection has a key
     * @param string $key
     * @return bool
     */
    public function has(string $key) : bool ;
    /**
     * Check if the collection has a value
     * @param string $value
     * @return bool
     */
    public function contains(string $value) : bool;
    /**
     * Get a element by it's key
     * @param string|integer $key
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function get($key);
    /**
     * Get a element by it's value
     * @param string|integer $value
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function value($value);
    /**
     * Set new pair of items on a collection
     * @param $key
     * @param $values
     * @param bool $replace
     * @return void
     */
    public function set($key, $values, $replace = false) : void;
    /**
     * Adds new items to the current collection set
     * @param array $items
     */
    public function add(array $items) : void;
    /**
     * Clear the collection
     */
    public function clear() : void;
    /**
     * Replace an existing value in the collection using their key
     * @param $name
     * @param $value
     * @throws \InvalidArgumentException
     */
    public function replace($name, $value) : void;
    /**
     * Remove an value
     * @param $key
     * @throws \InvalidArgumentException
     * @return bool
     *
     */
    public function remove($key) : bool;
    /**
     * Return an array representation of the collection
     * @return array
     */
    public function toArray() : array ;
    /**
     * Check if the collection is empty
     * @return bool
     */
    public function isEmpty() : bool;
}