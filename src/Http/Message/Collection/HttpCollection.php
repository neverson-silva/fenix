<?php
namespace Fenix\Http\Message\Collection;

use Fenix\Contracts\Support\Collection;
use InvalidArgumentException;
use ArrayIterator;

/**
 * HttpCollection
 *
 * @author Neverson Silva
 */
class HttpCollection implements Collection
{
    /*
     * @var array
     */
    protected $items;
    
    public function __construct(array $items = [])
    {
        if (!empty($items)) {
            $this->add($items);
        } else {
            $this->items = $items;
        }
    }
    
    public function __toString()
    {
        return json_encode($this->toArray());
    }
    
    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return ArrayIterator
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
    
    /**
     * Return all items inside the collection
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }
    
    /**
     * Return all keys in collection
     * @param bool $toLower If true all keys must be returned lower case
     * @return array
     */
    public function keys($toLower = false): array
    {
        $keys = array_keys($this->all());
        if ($toLower) {
            foreach ($keys as $key => $value) {
                $keys[$key] = strtolower($value);
            }
            return $keys;
        }
        return $keys;
    }
    
    /**
     * Return an array with only the collection values
     * @return array
     */
    public function values(): array
    {
        return array_values($this->items);
    }
    
    /**
     * Check if a collection has a key
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        if (is_null($this->items)) {
            return false;
        }
        return array_key_exists($key, $this->items);
    }
    
    /**
     * Check if the collection has a value
     * @param string $value
     * @return bool
     */
    public function contains(string $value): bool
    {
        if (is_null($this->items)) {
            return false;
        }
        return in_array($value, $this->items);
    }
    
    /**
     * Get a element by it's key
     * @param string|integer $key
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $this->items[$key];
        }
        throw new InvalidArgumentException("The key doesn't exists on this collection.");
    }
    
    /**
     * Get a element by it's value
     * @param string|integer $value
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function value($value)
    {
        if ($this->contains($value)) {
            foreach ($this->items as $key => $val) {
                if ($val === $value) {
                    return $val;
                }
            }
        }
        throw new InvalidArgumentException("Value {$value} not found.");
    }
    
    /**
     * Set new pair of items on a collection
     * @param $key
     * @param $values
     * @param bool $replace
     * @return void
     */
    public function set($key, $values, $replace = false): void
    {
        if ($this->has($key)) {
            throw new InvalidArgumentException("The key {$key} already exists.");
        }
        $this->items[$key] = $values;
    }
    
    /**
     * Adds new items to the current collection set
     * @param array $items
     */
    public function add(array $items): void
    {
        foreach ($items as $key => $values) {
            $this->set($key, $values);
        }
    }
    
    /**
     * Clear the collection
     */
    public function clear(): void
    {
        $this->items = [];
    }
    
    /**
     * Replace an existing value in the collection using their key
     * @param $name
     * @param $value
     * @throws \InvalidArgumentException
     */
    public function replace($name, $value): void
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException("The key {$name} doesn't exists on this collection");
        }
        $this->items[$name] = $value;
    }
    
    /**
     * Remove an value
     * @param $key
     * @throws \InvalidArgumentException
     * @return bool
     *
     */
    public function remove($key): bool
    {
        if (!$this->has($key)) {
            throw new InvalidArgumentException("The key {$key} doesn't exists on this collection");
        }
        unset($this->items[$key]);
        return true;
    }
    
    /**
     * Return an array representation of the collection
     * @return array
     */
    public function toArray(): array
    {
        return (array) $this->items;
    }
    
    /**
     * Check if the collection is empty
     * @return bool
     */
    public function isEmpty(): bool
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
        return count($this->items);
    }
}
