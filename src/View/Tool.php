<?php

namespace Fenix\View;

trait Tool
{
    protected function removeEmpty(array $valores)
    {
        return array_values(array_filter($valores, function($valor){
            if (is_array($valor)) {
                return array_filter($valor, function($val){
                    return !empty($val);
                });
            }
            return !empty($valor);
        }));
    }
}