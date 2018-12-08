<?php

namespace Fenix\Contracts\Database;

use Fenix\Database\Collection\Collection;
/**
 * Active Record Model
 * @author Neverson Bento da Silva <neverson_bs13@gmail.com>
 * @package Meltdown\Contracts\Database
 * @version 1.0.1
 * @copyright MIT © 2018
 *
 */
interface Model
{
    /**
     * Mass Create of An Record
     * @throws Exception
     * @param array $create
     * @return boolean
     */
    public function create(array $create) : bool;
    
    /**
     * Retrieve all records from database
     *
     * @return Collection 
     */
    public static function all();
    
    /**
     * Find a record in database
     *
     * @param int|string $id
     * @return void
     */
    public function find($id);
    
    /**
     * Create/Save a new record in database
     *
     * @param array $data
     * @return boolean
     */
    public function save(array $data) : bool;
    
    /**
     * Update record in database
     *
     * @param array$columns
     * @param array $conditions
     * @return boolean
     */
    public function update(array $update);
    
    /**
     * Delete a record in database
     *
     * @param int $id
     * @param mixed $columnCondition
     * @return boolean
     */
    public function delete(string $column = '', $id = '', $operator = null) : bool;
}
