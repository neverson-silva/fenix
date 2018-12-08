<?php

namespace Fenix\Database\Collection;

use Fenix\Database\Pagination\PaginationResolverBootstrap;
use Fenix\Contracts\Pagination\PaginationResolver;
use Fenix\Support\Collection as CollectionParent;
use Fenix\Contracts\Support\Collectable;
use Fenix\Database\Pagination\Pagination;
use Fenix\Contracts\Support\Arrayable;
use Fenix\Contracts\Support\Jsonable;
use Fenix\Contracts\Database\Model;
use OutOfRangeException;
use IteratorAggregate;
use Exception;

/**
 * An extension of BaseCollection to database purposes
 * @package Fenix
 * @subpackage Database\Support
 * @license MIT
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 */
class Collection extends CollectionParent
{
    /**
     * Pages when collection is paginated
    *
    * @var string
    */
    private static $pages;
    /**
     * Output formated links
     *
     * @var string
     */
    public static $links;

    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
        static::$pages = new Pagination($this);
    }

    public function __get($name)
    {
        if ($this->count() == 1) {

            if ($this->first() instanceof Model) {

                $model =  $this->first()->getAttributes();

                if (\property_exists($model, $name)) {
                    return $model->{$name};
                } else {
                    $class = get_class($this->first());
                    throw new \Exception(sprintf("Attribute '$name' doesn't exist on model %s", $class));
                }
            }
        }
        return $this->items[$name];

    }
    
    /**
       * Paginate the collection
       *
       * @param int $maxPerPage
       * @param string $pageName
       * @param string $class
       * @return void
       */
      public function paginate(int $maxPerPage, string $pageName = 'page', $resolver = null )
      {
         static::$pages->setPerPage($maxPerPage);
         static::$pages->setPageName($pageName);
         $links = $resolver ? new $resolver(static::$pages) :  new PaginationResolverBootstrap(static::$pages);
         $this->clear();
         $this->items = static::$pages->show();
         
         static::$links = $links->links();
         return new static($this->items);
      }
  
      /**
       * Return the links from pagination
       *
       * @return string
       */
      public static function links()
      {
          return self::$links;
      }
  
    /**
     * Return a new collection from an item inside previous collection
     *
     * @param $key
     * @return static
     */
    public function reCollect($key)
    {
        foreach ($this->items as $keyChain => $value) {
            $this->items[$keyChain]->$key = new static($this->items[$keyChain]->$key);
        }
        return new static($this->items);
    }
    
    public function groupBy($table)
    {
        $results = [];

        foreach ($this->items as $key => $model) {
            $properties = $model->getAttributes();

            if (property_exists($properties, $table)) {
                foreach ($properties as $column => $value) {
                    if ($column === $table) {
                        $results[$value][] = $properties;
                    }
                }
            }          

        }    
        return new static($results);
    }

    /**
     * Called when var_dump is invoked by developer
     * @return array
     */
    public function __debugInfo()
    {
        return $this->toArray();
    }

    /**
     * Removes all values from the collection.
     * @return void
     */
    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * Increase a value within the collection
     * @param $key 
     * @param $value
     * @return $this
     */
    public function increase($key, $value)
    {
        if (!$this->hasKey($key)) {
            throw new OutOfRangeException("The key $key was not found.");
        }
        $this->items[$key] += $value;

        return $this;
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

    public function add($name, $value)
    {
        if (!$this->hasKey($name)) {
            $this->items[$name] = $value;
        }
    }

    public function get($name)
    {
        if ($this->hasKey($name)) {
            return $this->items[$name];
        }
        throw new \OutOfRangeException("Entry $name not found");
    }

    public function put($name, $value)
    {
        $this->items[$name] = $value;
    }

    public function hasKey($name)
    {
        return array_key_exists($name, $this->items);
    }
}
