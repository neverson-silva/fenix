<?php

namespace Fenix\View;

use Fenix\Support\Strin;
use Fenix\Support\Arra;

trait Tool
{
    protected function removeEmpty(array $valores)
    {
        return $this->toStrin(array_values(array_filter($valores, function($valor){
            if (is_array($valor)) {
                return array_filter($valor, function($val){
                    return !empty($val) && $val !== ' ' && $val !== '';
                });
            }
            return !empty($valor) && $valor !== ' ' && $valor !== '';
        })));
    }

    /**
     * To Strin
     *
     * @param array $valores
     * @return Arra<Strin>
     */
    protected function toStrin(array $valores)
    {
        return new Arra(array_map(function($valor){
            if (is_array($valor) && count($valor) == 1) {
                return new Strin(array_values($valor)[0]);
            } elseif (is_array($valor)) {
                return $this->toStrin($valor);
            }
            return new Strin($valor);
        }, $valores));
    }
}