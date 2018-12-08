<?php

namespace Fenix\View\Components;


abstract class Component
{
    protected $value;

    /**
     * Get values dinamicaly
     *
     * @param string $name
     * @throws Exception
     * @return string|mixed
     */
    public function __get($name)
    {
        if (!$this->hasComplexType()) {
            //throw new \Exception("Property $name doesn't exist.");
            return null;
        }
        if ($this->isArray()) {
            return $this->value[$name] ?? null;
        }
        return $this->value->{$name} ?? null;
    }

    /**
     * Set values dinamicaly
     *
     * @param string $name
     * @throws Exception
     * @return string|mixed
     */
    public function __set($name, $params)
    {
        if (!$this->hasComplexType()) {
            throw new \Exception("Property $name doesn't exist.");
        }
        if ($this->isArray()) {
            $this->value[$name] = $params;
        } else {
            $this->value->{$name} = $params;
        }
    }
    
    public function __isset($name)
    {
        if (!$this->hasComplexType()) {
            return false;
        }
        if ($this->isArray()) {
            return isset($this->value[$name]);
        }
        return isset($this->value->{$name});
    }
                     
    public function __unset($name)
    {
        if (!$this->hasComplexType()) {
            throw new \Exception("Property $name doesn't exist.");
        }
        if ($this->isArray()) {
            unset($this->value[$name]);
        } else {
           unset($this->value->{$name});
       }
    }
                     
                     

    /**
     * Set Value
     *
     * @param $value
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Set values
     *
     * @param array $values
     * @return void
     */
    public function setValues(array $values)
    {
        $this->value = $values;
    }

    /**
     * Get Value
     *
     * @return string|stdClass
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get Values
     * @throws Exception
     * @return array
     */
    public function getValues() : array
    {
        if (!$this->isArray()) {
            throw new \Excpetion(sprintf(
                "%s::%s can only return array, type %s returned.", 
                get_class($this), "getValues", gettype($this->value)
            ));
        }
        return $this->value;
    }

    public function hasComplexType()
    {
        return is_array($this->value) || is_object($this->value);
    }

    /**
     * Check if a value is an array
     *
     * @return boolean
     */
    public function isArray()
    {
        return is_array($this->value);
    }

    /**
     * Check if a value is object
     *
     * @return boolean
     */
    public function isObject()
    {
        return is_object($this->value);
    }

    /**
     * Check if a complex type is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        if ($this->hasComplexType()) {
            return empty( (array) $this->value);
        }
        return true;
    }

    /**
     * Check if value is null
     *
     * @return boolean
     */
    public function isNull()
    {
        return is_null($this->value);
    }

    /**
     * Init the value property with an array
     *
     * @return void
     */
    public function initWithArray()
    {
        $this->value = [];
    }

    /**
     * Init the value property with an object
     *
     * @return void
     */
    public function initWithObject()
    {
        $this->value = new \stdClass();
    }


    /**
     * Push values into the component
     *
     * @param mixed $value
     * @return void
     */
    public function push($value)
    {
        if (!$this->isArray()) {
            throw new \Exception("Can only push value to an array variable");
        }
        $this->value[] = $value;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function firstValue()
    {
        if (!$this->hasComplexValue()) {
            throw new \Exception("Can you get first value of complex type.");
        }
        if ($this->isArray()) {
            return $this->value[0];
        }
        return $this->value{0};
    }

    /**
     * Check if a key exists
     *
     * @param $key
     * @return boolean
     */
    public function exists($key)
    {
        if ($this->isArray()) {
            return isset($this->value[$key]);
        }
        return isset($this->value->{$key});
    }
}
