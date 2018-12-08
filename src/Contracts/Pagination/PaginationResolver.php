<?php
namespace Fenix\Contracts\Pagination;

/**
 *
 * @author Neverson Bento da Silva <neverson_bs13@gmail.com>
 * @package Fenix\Contracts\Pagination
 * @version 1.0
 * @copyright MIT © 2018
 *
 */
interface PaginationResolver
{
    /**
     * Print the links of pagination
     * @return string
     */
    public function links($class) : string;
}
