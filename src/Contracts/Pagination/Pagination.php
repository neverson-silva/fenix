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
interface Pagination
{
    /**
     * Set max per page
     *
     * @param integer $page
     * @return void
     */
    public function setPerPage(int $page);

    /**
     * Recupera a quantidade de resultados por página
     * Retrieves the number of results per page
     *
     * @return int
     */
    public function getPerPage() : int;

    /**
     * Número total de páginas geradas
     *  Total number of pages generated
     *
     * @return void
     */
    public function totalPages() : int;

    /**
     * Retorna os resultados de uma página
     * Returns the results of a page
     * @param int $page
     * @return void
     */
    public function getPage(int $page);

    /**
     * Retorna os resultados da página atual
     * Returns the results of the current page
     * @return void
     */
    public function currentPageResults();

    /**
     * Retorna os resultados da primeira página
     * Returns the results of the first page
     * @return void
     */
    public function firstPage();

    /**
     * Retorna os resultados da última página
     * Returns the results of the last page
     * @return void
     */
    public function lastPage();

    /**
     * Avança para a próxima página
     * Goes to next page
     * @return void
     */
    public function nextPage();

    /**
     * Volta uma página
     * Goes to previous page
     * @return void
     */
    public function previousPage();
}
