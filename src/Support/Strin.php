<?php

namespace Fenix\Support;

use InvalidArgumentException;


class Strin
{
    private $value;


    public function __toString()
    {
        return $this->value;
    }

    /**
     * Strin constructor
     *
     * @param string $value
     */
    public function __construct(string $value = null)
    {
        $this->value = $value;
    }

    /**
     * Get Strin value
     *
     * @return string
     */
    public function getValue($value = null)
    {
        if ($value !== null && $value instanceof self) {
            return $value->getValue();
        }
        return $this->value;
    }

    /**
     * Get a pattern
     *
     * @param mixed $pattern
     * @return string
     */
    private function getPattern($pattern)
    {
        return $pattern instanceof self ? $pattern->getvalue() : $pattern;
    }

    /**
     * Implode an array into a string
     *
     * @param array $values
     * @param string $glue
     * @return string
     */
    public function join(array $values, $glue = ' ')
    {
        if ($this->value) {
            $values = array_unshift($values, $this->value);
        }
        return implode($glue, static::mapString($values));
    }


    /**
     * Explode into new Arra instance
     * @param $delimiter
     * @return Arra
     */
    public function split($delimiter) : Arra
    {
        $new = new Arra(explode($delimiter, $this->value));

        return $new->map(function($value){
            return new static($value);
        });
    }

     /**
     * Implode an array into a string in a static context
     *
     * @param array $values
     * @param string $glue
     * @return string
     */
    public static function implode(array $values, $glue = '')
    {
        return implode($glue, static::mapString($values));
    }

    /**
     * Explode a Strin and return a Arra
     *
     * @param string $delimiter
     * @param string $values
     * @return Arra
     */
    public static function explode($delimiter, $values) : Arra
    {
        return new Arra(
                array_map(function($value){
                    return new static ($value);
                }, explode($delimiter, $values))
            );
    }

    /**
     * Transform an array of values into a Strin instance
     * 
     * @todo remember of convert an array of String into string
     *
     * @param array $values
     * @param string $glue
     * @return String
     */
    public static function toString(array $values, $glue = ' ')
    {
        return new static(implode($glue, static::mapString($values)));
    }

    /**
     * Map strin to an array
     *
     * @param array $values
     * @return array
     */
    private static function mapString(array $values)
    {
        return array_map(function($value){
            return (string) $value;
        }, $values);
    }

    /**
     * @todo remeber of convert a string into a array of strin
     *
     * @param [type] $needle
     * @return void
     */
    public function toCollection($needle)
    {
        return new Collection(explode($needle, $this->values));
    }

    /**
     * Preg match a string
     *
     * @param string $pattern
     * @param string $value
     * @param int $options
     * @return Arra
     */
    public function matchAll(string $pattern, string $value = null, $options = PREG_PATTERN_ORDER)
    {
        $value = $this->getValue($value);

        $pattern = $this->getPattern($pattern);

        preg_match_all($pattern, $value, $matches, $options);

        return new Arra($matches);
    }

    /**
     * Match values with regex into a string
     *
     * @param string $pattern
     * @param string $value
     * @param boolean $getMatches
     * @return boolean|array
     */
    public function match(string $pattern, $value = null, $getMatches = false)
    {
        $value = $this->getValue($value);

        $pattern = $this->getPattern($pattern);

        $matched = preg_match($pattern, $value, $matches);

        if ($getMatches) {
            return empty($matches) ? false : $matches;
        }

        return $matched == 1 ? true : false;
    }   

    /**
     * Replace portion of a string
     *
     * @param string $pattern
     * @param string $replace
     * @param string $value
     * @param boolean $new
     * @return boolean
     */
    public function replace($pattern, $replace = null, $value = null, $new = false )
    {
       $pattern = $this->getPattern($pattern);

        $value = $this->getValue($value);

        $replaced = $this->isPattern($pattern) ?
                    preg_replace($pattern, $replace, $value) :
                    str_replace($pattern, $replace, $value);

        $original = $this->value;


        if ($new) {
            return $replaced;
        }

        $this->value = $replaced;

        return $original !== $replaced ? $replaced : false;
    }

    /**
     * Replace a string and return a new instance
     *
     * @param string $pattern
     * @param string $replace
     * @param string $value
     * @return Strin
     */
    public function replaceNew($pattern, $replace, $value = null)
    {
        return new static($this->replace($pattern, $replace, $value, true));
    }

    /**
     * Verify if a string is a regex pattern
     *
     * @param string $pattern
     * @return boolean
     */
    private function isPattern($pattern)
    {
        if (is_array($pattern)) {
            foreach ($pattern as $value) {
                if (!preg_match('/^\/(.*)\/$/', $value instanceof self ?
                    $value->getValue() : $value )) {
                    return false;
                }
            }
            return true;
        }
        return preg_match('/^\/(.*)\/$/', $pattern instanceof self ? $pattern->getValue() : $pattern);
    }

    /**
     * Format a string
     */

    public static function format($value, ...$params)
    {
        if (is_array($params[0])) {
            $params = $params[0];
        } else {
            
        }
        $params = array_map(function($param){
            return (string) $param;
        },$params);

        return vsprintf($value, $params);
    }

    /**
     * Format
     *
     * @param  ...$params
     * @return Strin
     */
    public function formatWith(...$params)
    {
        $this->value = static::format($this->value, $params);

        return $this;
    }

    /**
     * Get the size of a string in a static context
     *
     * @param string $value
     * @return integer
     */
    public static function size(string $value)
    {
        return strlen($value);
    }

    /**
     * Get the lenght of the internal string value
     *
     * @param string $value
     * @return integer
     */
    public function lenght()
    {
        return static::size($this->value);
    }

    /**
     * Change the current internal string value
     *
     * @param string $value
     * @return Strin
     */
    public function change(string $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Quote string with slashes
     * @return Strin
     */
    public function addSlashes()
    {
        $this->value = addSlashes($this->value);

        return $this;
    }

    /**
     * Strip whitespace (or other characters) from the end of a string
     *
     * @param $trimmed
     * @return Strin
     */
    public function rtrim($trimmed = ' ')
    {
        $this->value = rtrim($this->value, $trimmed);

        return $this;
    }

    /**
     * Trim
     *
     * @param string $trim
     * @return Strin
     */
    public function trim($trim = ' ')
    {
        $this->value = trim($this->value, $trim);

        return $this;
    }

    /**
     * Alias to rtrim
     * Strip whitespace (or other characters) from the end of a string
     *
     * @param $trimmed
     * @return Strin
     */
    public function ltrim($trimmed = ' ')
    {
        $this->value = ltrim($this->value, $trimmed);

        return $this;
    }

    /**
     * Print a string
     *
     * @param string $value
     * @return void
     */
    public function print($value = null)
    {
        print ($value ?? $this->value);
    }

    /**
     * Echo out a value
     *
     * @param string $value
     * @return void
     */
    public static function echo($value)
    {
        echo $value;
    }

    /**
     * Reverse a string
     *
     * @param string $value
     * @return string
     */
    public function reverse($value = null)
    {
        return strrev($value ?? $this->value);
    }

    /**
     * All charecters to upper
     *
     * @param string $value
     * @return string
     */
    public function upper($value = null)
    {
        $value = $this->getValue($value);

        $this->value = strtoupper($value);

        return $this->value;
    }

    /**
     * All characters to lower
     *
     * @param string $value
     * @return string
     */
    public function lower($value = null)
    {
        $value = $this->getValue($value);

        $this->value = strtolower($value);

        return $this->value;
    }

    /**
     * All first letters to upper case
     *
     * @param string $value
     * @return string
     */
    public function ucwords($value = null)
    {
        $value = $this->getValue($value);

        $this->value = ucwords($value);

        return $this->value;
    }

    /**
     * First charecter to upper case
     *
     * @param string $value
     * @return string
     */
    public function ucfirst($value = null)
    {
        $value = $this->getValue($value);

        $this->value = ucfirst($value);

        return $this->value;
    }

    /**
     *  Retorna uma parte de uma string
     *
     * @param int $start
     * @param int $length
     * @return Strin
     */
    public function substr($start, $length)
    {
        return new static(substr($this->value, $start, $length));
    }

    /**
     * Encontra a primeira ocorrencia de uma string sem diferenciar maiúsculas e minúsculas
     * @return int|boolean
     */
    public function position($needle)
    {
        return stripos($this->value, $needle);
    }

    /**
     * Html entities
     *
     * @return Strin
     */
    public function entitiesHtml()
    {
        $this->value = htmlentities($this->value);

        return $this;
    }

    /**
     * Compare two strings
     *
     * @param string $value
     * @return boolean
     */
    public function equals($value)
    {
        return $value instanceof self ? 
               $value->getValue() === $this->getValue() :
               $value === $this->getValue();
    }

    public static function compare($value, $second)
    {
        $value = $value instanceof self ? $value->getValue() : $value;
        $second = $second instanceof self ? $second->getValue() : $second;

        return $value === $second;
    }

    /**
     * Create a Strin comparing default value
     *
     * @param string $value
     * @param string $equal
     * @param string $default
     * @return Strin
     */
    public static function withDefault($value, $equal, $default)
    { 
        $value = $value == $equal ? $default : $value;

        return new static($value);
    }

    /**
     * Create a new instance
     *
     * @param string $value
     * @return Strin
     */
    public static function create($value)
    {
        return new static((string) $value);
    }

    public function wrap(array $values)
    {
        if (count($values) > 2) {
            throw new InvalidArgumentException("The array of values can contain only 2 values");
        }
        $this->value = $values[0] . $this->value . $values[1];

        return $this;
    }
}