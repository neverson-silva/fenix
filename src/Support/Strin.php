<?php

namespace Fenix\Support;

class Strin
{
    private $value;


    /**
     * Strin constructor
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Implode an array into a string
     *
     * @param array $values
     * @param string $glue
     * @return string
     */
    public static function join(array $values, $glue = ' ')
    {
        if ($this->value) {
            $values = array_unshift($values, $this->value);
        }
        return array_implode($glue, $values);
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
        return new static(array_implode($glue, $values));
    }

    /**
     * @todo remeber of convert a string into a array of strin
     *
     * @param [type] $needle
     * @return void
     */
    public function toCollection($needle)
    {
        return new Collection(array_explode($needle, $this->values));
    }

    /**
     * Preg match a string
     *
     * @param string $pattern
     * @param string $value
     * @param int $options
     * @return array
     */
    public function matchAll(string $pattern, string $value = null, $options = PREG_PATTERN_ORDER)
    {
        $value = $value ?? $this->value;

        preg_match_all($pattern, $value, $matches, $options);

        return $matches;
    }

    /**
     * Match values with regex into a string
     *
     * @param string $pattern
     * @param string $value
     * @param boolean $getMatches
     * @return boolean|array
     */
    public function match(string $pattern, string $value = null, $getMatches = false)
    {
        $value = $value ?? $this->value;

        $matched = preg_match($pattern, $value, $matches);

        if ($getMatches) {
            return $matches;
        }

        return $matched;
    }   

    public function replace(string $pattern, string $replace, $value = null )
    {
        $value = $value ?? $this->value;

        $original = $this->value;

        $replaced = $this->isPattern($pattern) ?
                    preg_replace($pattern, $replace, $value) :
                    str_replace($pattern, $replace, $value);

        $this->value = $replaced;

        return $original !== $replaced ? $replaced : false;
    }

    /**
     * Verify if a string is a regex pattern
     *
     * @param string $pattern
     * @return boolean
     */
    private function isPattern(string $pattern)
    {
        return preg_match('/\/(.*)\//', $pattern);
    }

    /**
     * Format a string
     */

    public static function format($value, ...$params)
    {
        return vsprintf($value, $params);
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
        return strlen($this->value);
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
    public function rtrim($trimmed = null)
    {
        $this->value = rtrim($this->value, $trimmed);

        return $this;
    }

    public function trim($trim = null)
    {
        $this->value = trim($this->value, $trimmed);

        return $this;
    }

    /**
     * Alias to rtrim
     * Strip whitespace (or other characters) from the end of a string
     *
     * @param $trimmed
     * @return Strin
     */
    public function ltrim($trimmed = null)
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

    public function upper($value = null)
    {

    }

    public function lower($value = null)
    {

    }

    public function ucwords($value = null)
    {

    }

    public function ucfirst($value = null)
    {

    }

    public function substr()
    {
        
    }

}