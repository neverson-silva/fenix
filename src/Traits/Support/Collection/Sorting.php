<?php

namespace Fenix\Traits\Support\Collection;

/**
 * Trait Sorting Methods
 * @package Meltdown\UsefullCollections\Traits
 */

/*
    O PHP tem muitas funções para lidar com ordenação de arrays, e esse documento existe para ajudar a você lidar com elas.

    As principais diferenças são:

    Algumas ordenam com base nas chaves do array, enquanto outras pelos valores: $array['chave'] = 'valor';
    A correlação entre as chaves e os valores do array não são mantidas depois da ordenação, o que pode fazer com que as
     chaves sejam resetadas numericamente (0, 1, 2, ...)
    A ordem da ordenação: alfabética, menor para maior (ascendente), maior para menor (descendente), numérica, natural,
    aleatório, ou definida pelo usuário
    Nota: Todas essas funções agem diretamente na própria variável do array, ao invés de retornar um novo array ordenado
    Se qualquer uma dessas funções avaliar dois membros como iguais então a ordem será indefinida (a ordenação não é estável).

*/
trait Sorting
{
    /**
     * Sorts an array by key in reverse order, maintaining key to data correlations.
     * This is useful mainly for associative arrays.
     *
     * @param $flags
     * @see sort() for sorting flags type
     *
     * @return static
     */
    public function ksort($flags = SORT_REGULAR)
    {
        $copy = $this->copy();

        ksort($copy->items, $flags);

        return $copy;
    }

    /**
     * This function sorts an array. Elements will be arranged from lowest to highest
     * when this function has completed.
     *
     * NOTE: If two members compare as equal, their relative order in the sorted array is undefined.
     *
     * SORTING TYPE FLAGS:
     *
     *  SORT_REGULAR - compare items normally (don't change types)
     *  SORT_NUMERIC - compare items numerically
     *  SORT_STRING - compare items as strings
     *  SORT_LOCALE_STRING - compare items as strings, based on the current locale.
     *                      It uses the locale, which can be changed using setlocale().
     *  SORT_NATURAL - compare items as strings using "natural ordering" like natsort().
     *  SORT_FLAG_CASE - can be combined (bitwise OR) with SORT_STRING or SORT_NATURAL to sort strings case-insensitively
     *
     * @param int $flags
     * @return Collectable
     */
    public function sort($flags = SORT_REGULAR)
    {
        $copy = $this->copy();

        sort($copy->items, $flags);

        return $copy;
    }

    /**
     * 
     * 
     * TO BE IMPLEMENTED
     * 
     */
    public function asort(){}

    public function arsort() {}

    public function krsort() {}

    public function natcasesort() {}

    public function natesort(){}

    public function rsort(){}

    public function shuffle(){}

    public function uasort(){}

    public function usort(){}

    public function uksort(){}

    public function walk() {}

    public function walkRecursive() {}

    public function random() {}





}