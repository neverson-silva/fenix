<?php

namespace Fenix\Traits\Support\Collection;

use Fenix\Contracts\Support\Arrayable;
use JsonSerializable;
use Traversable;
use ArrayAccess;

trait DotNotation
{
    /**
     * Adiciona um item a coleção
     * Caso deseje adicionar arrays multidimensionais você poderá utilizar a notação em '.' (ponto)
     *
     * Ex:
     * $collection->add('pessoa.nome', 'Neverson');
     * $collection->add('pessoa.sobrenome', 'Silva');
     *
     * Que representa o array da seguinte maneira
     *
     * $collection['pessoa']['nome'] = 'Silva;
     * $collection['pessoa']['sobrenome'] = 'Silva;
     *
     * Cada item após o ponto será considerado uma chave.
     * @param string|int $name O nome da chave em que o item será adicionado
     * @param string|int|array|mixed $item O item a ser adicionado a coleção
     * @return void
     */
    public function put($name, $item)
    {
        if (is_null($name)) {
            $this->items[] = $item;
            return;
        }
        if (strpos($name, '.')) {
            $this->dotNotation($name, $item);
        } else {
            $this->items[$name] = $item;
        }
    }
    /**
     * Recupera um item dentro da coleção
     *
     * Para arrays multidimensionais você poderá utilizar a notação em .
     *
     * Ex:
     *
     * return $collection->get('pessoa.sobrenome')
     *
     * Que representa o array da seguinte maneira
     *
     * return $collection['pessoa']['sobrenome']
     *
     * Cada item após o ponto será considerado uma chave.
     *
     * @param string|int $name O nome da chave a ser obtido
     * @throws \OutOfBoundsException Caso a chave não seja encontrada
     * @return mixed O item dentro da coleção
     */
    public function get($name)
    {
        if ($this->hasKey($name)) {
            return $this->dotNotation($name) ?? $this->items[$name];
        }
        throw new \OutOfBoundsException("Chave $name não encontrada nesta coleção.");
    }
    /**
     * Opção de notação em ponto
     *
     * @param string|int $name
     * @param array $item
     * @param boolean $get
     * @param $items
     * @return mixed|boolean
     */
    private function dotNotation($name, $item = [], $get = false, &$items = [])
    {
        if (empty($items)) {
            $items = &$this->items;
        }
        $keys = explode('.', $name);
        $lastItem = array_pop($keys);
        foreach ($keys as $key) {
            $items = &$items[$key];
        }
        if ($get == true || empty($item)) {
            return isset($items[$lastItem]) ? $items[$lastItem] : null;
        }
        $items[$lastItem] = $item;
    }
    /**
     * Remove um item dentro da coleção
     *
     * Para arrays multidimensionais você poderá utilizar a notação em .
     *
     * Ex: $collection->remove('pessoa.nome')
     *
     * Equivalente a utilizar unset no objeto
     *
     * Ex: unset($collection['pessoa']['nome'])
     *
     * @param string|int $name O nome da chave do item
     * @return bool
     */
    public function remove($name) : bool
    {
        $items = &$this->items;
        if (strpos($name, '.')) {
            $keys = explode('.', $name);
            $name = array_pop($keys);
            foreach ($keys as $key) {
                $items = &$items[$key];
            }
        }
        unset($items[$name]);
        return isset($items[$name]) ? false : true;
    }

    /**
     *
     * Verifica se uma posição existe
     *
     *
     * Este método é executado ao utilizar-se a função isset() ou empty()
     * em objetos que implementem ArrayAccess.
     *
     * Nota: Ao utilizar a função empty(), o método ArrayAccess::offsetGet() será chamado e
     * checado por vazio somente se o método
     *
     * ArrayAccess::offsetExists() retornar TRUE
     *
     * @return boolean
     *
     */
    public function  offsetExists( $offset )
    {
        return $this->hasKey($offset);
    }

       /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function &offsetGet( $offset )
    {
        $value = $this->get($offset);
        return $value;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet( $offset , $value )
    {
        return $this->put($offset, $value);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset( $offset )
    {
        return $this->remove($offset);
    }
}
