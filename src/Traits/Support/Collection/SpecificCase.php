<?php

namespace Fenix\Traits\Support\Collection;


trait SpecificCase
{
    /**
     * Seek a parten in collection and return if has it else return null
     *
     * @param string $toFind
     * @param string $pattern
     * @return static
     */
    public function preg_match($toFind, $pattern, $property = '')
    {
        return $this->filter(function ($value, $key) use ($toFind, $pattern, $property) {
            $expression = is_object($value) ? sprintf($pattern, $value->$property) : sprintf($pattern, $key);
            if (is_array($value)) {
                $expression = sprintf($pattern, $value[$property]);
            } elseif (is_object($value)) {
                sprintf($pattern, $value->$property);
            } else {
                sprintf($pattern, $key);
            }

            if (empty($key)) {
                return [];
            }
            return preg_match($expression, $toFind) ? $value : [];
        });
    }

    /**
     * Specific case
     *
     * @param array $newKeys
     * @param $keys
     * @return object
     */
    public function separateKeysValues(array $newKeys, $keys = null)
    {
        $keys = $this->keys();

        foreach ($keys as $key => $value) {
            $keys[$key] = str_replace('\\\\', '', $value);
        }
        return (object) [
            $newKeys[0] => $keys ?? $this->keys() ,
            $newKeys[1] => implode(DIRECTORY_SEPARATOR, $this->values())
        ];
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
     * Check conditional value
     * @param mixed $objeto
     * @param mixed $conds
     * @return mixed
     */
    private function conditional($objeto, $conds)
    {
        $objeto = is_object($objeto) ? $objeto : (object) $objeto;

        switch ($conds[1]) {
            case ">":
                return $objeto->{$conds[0]} > $conds[2];
                break;
            case "<":
                return $objeto->{$conds[0]} < $conds[2];
                break;
            case ">=";
                return $objeto->{$conds[0]} >= $conds[2];
                break;
            case "<=";
                return $objeto->{$conds[0]} <= $conds[2];
                break;
            case "=":
                return $objeto->{$conds[0]} == $conds[2];
                break;
            case "!=" || "<>":
                return $objeto->{$conds[0]}!= $conds[2];
                break;
        }
    }

    /**
     * Check if the array is multidimensional
     * @param array $array
     * @return bool
     */
    public static function is_multidimensional(array $array)
    {
        foreach ($array as $ar){
            if (is_array($ar) || is_object($ar)) return true;
        }

        return false;
    }

    /**
     * Erase some data
     */
    function erasing(&$data, $toErase)
    {

        if (is_object($data)) {
            unset($data->$toErase);
        } else {
            unset($data[$toErase]);
        }
        return $data;
    }
}