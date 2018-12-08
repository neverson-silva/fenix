<?php

namespace Fenix;

use Fenix\Support\Collection as BaseCollection;
use Fenix\Contracts\Support\Collection;
use Fenix\Orm\Model;
use Fenix\Date;

class TypeHint
{

    /**
     * Get type of a value
     *
     * @param $value
     * @return null|string
     */
    public function getType($value)
    {
        if ($this->isInteger($value)) {
            return 'integer';
        } elseif ($this->isFloat($value)) {
            return 'float';
        } elseif ($this->isDate($value) && !$this->isDateTime($value)) {
            return 'date';
        } elseif($this->isDateTime($value)) {
            return 'dateTime';
        } elseif ($this->isArray($value)) {
            return 'array';
        } elseif ($this->isCollection($value)) {
            return 'collection';
        } elseif ($this->isString($value)) {
            return 'string';
        }
        return null;
    }

    /**
     * Cast a value to their right type
     * @param $value
     * @return mixed
     */
    public static function castType($value)
    {
        $typeHint = new static();
        $type = $typeHint->getType($value);


        if (is_null($type)) {
            return $value;
        }

        $method = 'to' . ucfirst($type);
        return $typeHint->{$method}($value);
    }

    /**
     * Check if a string is a number
     * @param $value
     * @return bool
     */
    public function isNumeric($value)
    {
        return is_numeric($value);
    }

    /**
     * Check if value has a float pointing number
     * @param $value
     * @return bool
     */
    public function isFloat($value)
    {
        if (is_object($value)) {
            return false;
        }
        if (stripos($value, ',')) {
            $value = str_replace(',', '.', str_replace('.', '', $value));
        }
        return is_float($value) || $this->isNumeric($value) && stripos($value, '.');

    }

    /**
     * Check if value is integer
     * @param $value
     * @return bool
     */
    public function isInteger($value)
    {
        return is_int($value) || $this->isNumeric($value) && !stripos($value, '.');
    }

    /**
     * Alias to isFloar
     * @param $value
     * @return bool
     */
    public function isDecimal($value)
    {
        return $this->isFloat($value);
    }

    /**
     * Check if a value is an array
     * @param $value
     * @return bool
     */
    public function isArray($value)
    {
        return is_array($value);
    }

    /**
     * Check if a value is a Collection
     *
     * @param $value
     * @return bool
     */
    public function isCollection($value)
    {
        return $value instanceof BaseCollection;
    }

    /**
     * Check if a value is String
     * @param $value
     * @return bool
     */
    public function isString($value)
    {
        return is_string($value) && !$this->isNumeric($value)
               && !$this->isDate($value) && !$this->isDateTime($value)
               && !$this->isTimestamp($value);
    }

    /**
     * Check if Value is a valid Date
     * @param $value
     * @return false|int
     */
    public function isDate($value)
    {
        $pattern = '/([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))/';
        return  $value instanceof Date || preg_match($pattern, $value)? true : false;
    }

    /**
     * Check if value is a valid DateTime
     * @param $value
     * @return false|int
     */
    public function isDateTime($value)
    {
        $pattern = '/^\d\d\d\d-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01]) (00|[0-9]|1[0-9]|2[0-3]):([0-9]|[0-5][0-9]):([0-9]|[0-5][0-9])$/';

        return  $value instanceof Date || preg_match($pattern, $value)? true : false;
    }

    /**
     * Check if value is a valid timestamp
     *
     * @param $value
     * @return bool
     */
    public function isTimestamp($value)
    {
        return ((string) (int) $value === $value)
                && ($value <= PHP_INT_MAX)
                && ($value >= ~PHP_INT_MAX);
    }

    /**
     * Convert value to integer
     * @param $value
     * @return int
     */
    public function toInteger($value)
    {
        return (int) $value;
    }

    /**
     * Convert value to integer
     *
     * @param $value
     * @return int
     */
    public function toInt($value)
    {
        return $this->toInteger($value);
    }

    /**
     * Convert value to float
     * @param $value
     * @return float
     */
    public function toFloat($value)
    {
        return floatval(str_replace(',', '.', str_replace('.', '', $value))) ;
    }

    /**
     * Convert value to array
     *
     * @param $value
     * @return array
     */
    public function toArray($value)
    {
        return $this->isCollection($value) ? $value->toArray() : (array) $value;
    }

    /**
     * Convert value to string
     *
     * @param $value
     * @return string
     */
    public function toString($value)
    {
        return (string) $value;
    }

    /**
     * Convert valid datetime string to DateTime instance
     *
     * @param $value
     * @return bool|DateTime
     */
    public function toDate($value)
    {
        return new Date($value);
    }

    /**
     * Convert String to datetime
     *
     * @param $value
     * @return bool|DateTime
     */
    public function toDateTime($value)
    {
        return $value instanceof Date ? $value : new Date($value);
    }

    /**
     * Convert value to Collection
     *
     * @param $value
     * @return BaseCollection
     */
    public function toCollection($value)
    {
        return $this->isCollection() ? $value : new BaseCollection($value);
    }

    /**
     * Convert value to timestamp
     * @param $value
     * @return int
     */
    public function toTimestamp($value)
    {
        return $this->toInteger($value);
    }

    public static function toObject($value)
    {
        return (object) $value;
    }

    public function isModel($value)
    {
        return $value instanceof Model;
    }

}